<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\{Deposit, StaffRoute, ClientSchedule, ClientPayment, Client, ClientRoute, AssignRoute, Notification};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DepositsController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $isAdmin = $user && $user->hasRole('admin');

            if ($isAdmin) {
                $routes = StaffRoute::where('status', 1)->get();
                $staffs = \App\Models\User::role('staff')->orderBy('name')->get();
                // Admin can optionally narrow to one staff member via the filter; staff role
                // always sees only their own records regardless of any staff_id passed in.
                $onlyStaffId = $request->filled('staff_id') ? $request->staff_id : null;
            } else {
                $assignedRouteIds = AssignRoute::where('staff_id', $user->id)
                    ->pluck('route_id')
                    ->toArray();
                $routes = StaffRoute::where('status', 1)
                    ->whereIn('id', $assignedRouteIds)
                    ->get();
                $staffs = collect();
                $onlyStaffId = $user->id;
            }

            $sections = $this->buildStaffSections($onlyStaffId, $request);
            $grandTotal = $sections->sum('total');

            return view('dashboard.deposits', compact('sections', 'routes', 'staffs', 'isAdmin', 'grandTotal'));
        } catch (\Exception $e) {
            Log::error('Error loading deposits index: ' . $e->getMessage());
            return redirect()->back()->with([
                'title' => 'Error',
                'message' => 'Failed to load deposits: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    /**
     * Build the "Total Undeposited Cash" data, grouped by staff member.
     *
     * Combines two sources into one unified row list:
     *   1) Existing Deposit records that haven't been marked deposited yet (is_deposit = false).
     *   2) Raw paid-cash ClientPayments that haven't been converted into a Deposit record yet
     *      (e.g. a cash account that was unpaid at time of service and has since been paid).
     *
     * Rows from (2) are grouped by [staff, route, week, month, year] so a normal weekly batch of
     * cash payments collapses into one aggregate row (Client column blank), while a payment that
     * sits alone in its group (e.g. paid late, outside the normal batch) is shown individually
     * with its own client name and exact service date.
     */
    private function buildStaffSections($onlyStaffId, Request $request)
    {
        $rows = collect();
        $filterDateFrom = $request->filled('date_from') ? Carbon::parse($request->date_from)->startOfDay() : null;
        $filterDateTo = $request->filled('date_to') ? Carbon::parse($request->date_to)->endOfDay() : null;

        // 1) Existing deposit records not yet marked as deposited
        $depositQuery = Deposit::with(['route', 'staff', 'clientSchedule.clientName'])
            ->where('is_deposit', false);

        if ($onlyStaffId) {
            $depositQuery->where('staff_id', $onlyStaffId);
        }
        if ($request->filled('route_id')) {
            $depositQuery->where('route_id', $request->route_id);
        }
        if ($request->filled('week')) {
            $depositQuery->where('week', $request->week);
        }

        foreach ($depositQuery->get() as $deposit) {
            $referenceDate = $deposit->schedule_id
                ? ($deposit->clientSchedule?->service_date ?? $deposit->clientSchedule?->start_date)
                : null;

            $range = $referenceDate
                ? $this->calendarWeekRangeForDate(Carbon::parse($referenceDate))
                : $this->computeWeekDateRange($deposit->week, $deposit->month, $deposit->year);

            if (($filterDateFrom || $filterDateTo) && (!$range || !$this->rangeOverlaps($range, $filterDateFrom, $filterDateTo))) {
                continue;
            }

            $rows->push([
                'staff_id' => $deposit->staff_id,
                'staff_name' => $deposit->staff->name ?? 'Unassigned',
                'route_name' => $deposit->route->name ?? 'N/A',
                'week_number' => $this->weekNumberFromString($deposit->week),
                'amount' => (float) $deposit->deposit_amount,
                'date_label' => $this->formatDateLabel($range),
                'sort_date' => $range['start'] ?? $deposit->created_at,
                'deposit_date' => null,
                'deposit_id' => $deposit->id,
                'payment_ids' => null,
            ]);
        }

        // 2) Raw cash payments not yet converted into a deposit record
        $rawPayments = $this->getUndepositedCashPaymentsForStaff($onlyStaffId);
        $staffMap = \App\Models\User::whereIn('id', $rawPayments->pluck('staff_id')->filter()->unique())
            ->get()->keyBy('id');

        $groups = $rawPayments->map(function ($payment) {
            $payment->setAttribute('_group_context', $this->resolvePaymentGroupContext($payment));
            return $payment;
        })->groupBy(function ($payment) {
            $context = $payment->_group_context;
            return implode('|', [$payment->staff_id, $context['route_id'], $context['week'], $context['month'], $context['year']]);
        });

        foreach ($groups as $groupPayments) {
            $first = $groupPayments->first();
            $context = $first->_group_context;

            if ($request->filled('route_id') && (string) $context['route_id'] !== (string) $request->route_id) {
                continue;
            }
            if ($request->filled('week') && $context['week'] !== $request->week) {
                continue;
            }

            $routeName = $first->client?->clientRouteStaff?->first(
                fn($cr) => (string) $cr->route_id === (string) $context['route_id']
            )?->route?->name ?? 'N/A';

            $range = $this->calendarWeekRangeForDate($context['reference_date']);

            if (($filterDateFrom || $filterDateTo) && !$this->rangeOverlaps($range, $filterDateFrom, $filterDateTo)) {
                continue;
            }

            $rows->push([
                'staff_id' => $first->staff_id,
                'staff_name' => $staffMap[$first->staff_id]->name ?? 'Unassigned',
                'route_name' => $routeName,
                'week_number' => $this->weekNumberFromString($context['week']),
                'amount' => (float) $groupPayments->sum(fn($p) => (float) ($p->final_price ?? 0)),
                'date_label' => $this->formatDateLabel($range),
                'sort_date' => $range['start'] ?? $context['reference_date'],
                'deposit_date' => null,
                'deposit_id' => null,
                'payment_ids' => $groupPayments->pluck('id')->values()->all(),
            ]);
        }

        return $rows->groupBy('staff_name')
            ->map(function ($staffRows, $staffName) {
                return [
                    'staff_id' => $staffRows->first()['staff_id'],
                    'staff_name' => $staffName,
                    'rows' => $staffRows->sortByDesc('sort_date')->values(),
                    'total' => $staffRows->sum('amount'),
                ];
            })
            ->sortBy('staff_name')
            ->values();
    }

    /**
     * Resolve the same route/week/month/year grouping key that createDepositFromPayment()
     * would use if this payment were converted into a Deposit record, so a pending group's
     * display stays consistent with the Deposit row it eventually becomes.
     */
    private function resolvePaymentGroupContext(ClientPayment $payment): array
    {
        $schedule = $payment->clientSchedule;
        $routeId = $payment->client?->clientRouteStaff?->first()?->route_id;

        $referenceDate = $schedule?->service_date
            ?? $schedule?->start_date
            ?? $payment->payment_date
            ?? $payment->created_at
            ?? now();
        $referenceDate = Carbon::parse($referenceDate);

        $week = $schedule?->week;
        if ($week === null || $week === '') {
            $week = 'week0';
        } elseif (is_numeric($week)) {
            $week = 'week' . $week;
        } elseif (!str_starts_with((string) $week, 'week')) {
            $week = 'week' . preg_replace('/[^0-9]/', '', (string) $week);
        }

        $month = $schedule?->month ?: $referenceDate->format('F');

        return [
            'route_id' => $routeId,
            'week' => $week,
            'month' => $month,
            'year' => (int) $referenceDate->format('Y'),
            'reference_date' => $referenceDate,
        ];
    }

    /**
     * Same "undeposited cash" query as getUndepositedCashPayments(), but not restricted to a
     * single staff member's assigned routes — used by the admin, all-staff grouped view.
     */
    private function getUndepositedCashPaymentsForStaff($staffId = null)
    {
        $depositedScheduleIds = Deposit::whereNotNull('schedule_id')->pluck('schedule_id');
        $depositedPaymentIds = Deposit::whereNotNull('client_payment_id')->pluck('client_payment_id');

        return ClientPayment::with([
                'clientSchedule.clientName.clientRouteStaff.route',
                'client.clientRouteStaff.route',
            ])
            ->where('option', '!=', 'omit')
            ->where('payment_type', 'cash')
            ->where('status', 'paid')
            ->where(function ($q) {
                $q->whereNull('payment_status')->orWhere('payment_status', 'payment')->orWhere('payment_status', 'pending');
            })
            ->when($staffId, fn($q) => $q->where('staff_id', $staffId))
            ->when($depositedScheduleIds->isNotEmpty(), function ($q) use ($depositedScheduleIds) {
                $q->where(function ($sub) use ($depositedScheduleIds) {
                    $sub->whereNull('schedule_id')->orWhereNotIn('schedule_id', $depositedScheduleIds);
                });
            })
            ->when($depositedPaymentIds->isNotEmpty(), function ($q) use ($depositedPaymentIds) {
                $q->whereNotIn('id', $depositedPaymentIds);
            })
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Calendar date range for a stored week/month/year triple (e.g. week='week2',
     * month='August - September', year=2026), using the same 4-week cycle-offset map already
     * used for route reports. Returns null when the month string doesn't match a known cycle
     * name (e.g. legacy bare month names like "August" saved by createDepositFromPayment()).
     */
    private function computeWeekDateRange(?string $week, ?string $month, ?int $year): ?array
    {
        if (!$week || !$year) {
            return null;
        }

        $cycleOffsets = [
            'januaryfebruary' => 0,
            'februarymarch' => 4,
            'march' => 8,
            'marchapril' => 12,
            'aprilmay' => 16,
            'mayjune' => 20,
            'junejuly' => 24,
            'julyaugust' => 28,
            'augustseptember' => 32,
            'septemberoctober' => 36,
            'octobernovember' => 40,
            'novemberdecember' => 44,
            'decemberjanuary' => 48,
        ];

        $normalized = strtolower(preg_replace('/[^a-zA-Z]/', '', (string) $month));
        if (!array_key_exists($normalized, $cycleOffsets)) {
            return null;
        }

        $monthStart = Carbon::parse("first Monday of January {$year}")->addWeeks($cycleOffsets[$normalized]);
        $weekStart = $monthStart->copy()->addDays(($this->weekNumberFromString($week) - 1) * 7);
        $weekEnd = $weekStart->copy()->addDays(6);

        return ['start' => $weekStart, 'end' => $weekEnd];
    }

    /**
     * Whether a row's week date range overlaps the requested date_from/date_to filter range.
     * Either bound may be null (open-ended).
     */
    private function rangeOverlaps(array $range, ?Carbon $filterDateFrom, ?Carbon $filterDateTo): bool
    {
        if ($filterDateFrom && $range['end']->lt($filterDateFrom)) {
            return false;
        }
        if ($filterDateTo && $range['start']->gt($filterDateTo)) {
            return false;
        }

        return true;
    }

    private function weekNumberFromString(?string $week): int
    {
        return ((int) preg_replace('/[^0-9]/', '', (string) $week)) + 1;
    }

    /**
     * Find the portal's 7-day calendar week that a given date falls into, purely from the date
     * itself — the same "first Monday of January + 7-day blocks" tiling the rest of the app uses
     * to lay out weeks, without depending on a (possibly inconsistent) stored week/month string.
     */
    private function calendarWeekRangeForDate(Carbon $date): array
    {
        $firstMonday = Carbon::parse('first Monday of January ' . $date->year);
        if ($date->lt($firstMonday)) {
            $firstMonday = Carbon::parse('first Monday of January ' . ($date->year - 1));
        }

        $weekIndex = intdiv($firstMonday->diffInDays($date), 7);
        $weekStart = $firstMonday->copy()->addWeeks($weekIndex);
        $weekEnd = $weekStart->copy()->addDays(6);

        return ['start' => $weekStart, 'end' => $weekEnd];
    }

    /**
     * Format a week date range as e.g. "Aug 03 - 09, 2026" (same month) or
     * "Aug 31 - Sep 06, 2026" (crossing a month boundary).
     */
    private function formatDateLabel(?array $range): string
    {
        if (!$range) {
            return 'N/A';
        }

        $start = $range['start'];
        $end = $range['end'];

        if ($start->format('M') === $end->format('M')) {
            return $start->format('M d') . ' - ' . $end->format('d') . ', ' . $end->format('Y');
        }

        return $start->format('M d') . ' - ' . $end->format('M d') . ', ' . $end->format('Y');
    }

    public function create(Request $request)
    {
        try {
            $user = Auth::user();
            // Get routes - for staff, only show their assigned routes; for admin, show all
            if ($user && !$user->hasRole('admin')) {
                $assignedRouteIds = AssignRoute::where('staff_id', $user->id)
                    ->pluck('route_id')
                    ->toArray();
                $routes = StaffRoute::where('status', 1)
                    ->whereIn('id', $assignedRouteIds)
                    ->get();
            } else {
                $routes = StaffRoute::where('status', 1)->get();
            }

            // Pre-fill data if coming from route show page
            $routeId = $request->route_id;
            $week = $request->week;
            $month = $request->month;
            $year = $request->year;
            $totalAmount = $request->total_amount;

            return view('deposits.create', compact('routes', 'routeId', 'week', 'month', 'year', 'totalAmount'));
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'title' => 'Error',
                'message' => 'Failed to load create form: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    private function calculateExpectedCash($routeId, $week, $month, $year)
    {
        try {
            $staffRoute = StaffRoute::with([
                'clientRoute.clientSchedule.clientSchedulePrice.clientPaymentPrice',
                'clientRoute.clientSchedule.clientName'
            ])->findOrFail($routeId);

            $weekNumber = (int) preg_replace('/[^0-9]/', '', $week);
            $selectedMonth = trim($month);

            // Check if year is already present in the month string
            if (!preg_match('/\d{4}/', $selectedMonth)) {
                // Year not present, add it
                $selectedMonth = trim($selectedMonth) . " " . $year;
            }
            // Now $selectedMonth = "March - April 2026" or "March 2026"

            // Get month start date - EXACT copy from StaffRoutesController show method
            $firstMondayOfYear = Carbon::parse("first Monday of January $year");

            $customStartDates = [
                "January - February" => $firstMondayOfYear->copy()->addDays(0),
                "February - March" => $firstMondayOfYear->copy()->addWeeks(4),
                "March" => $firstMondayOfYear->copy()->addWeeks(8),
                "March - April" => $firstMondayOfYear->copy()->addWeeks(12),
                "April - May" => $firstMondayOfYear->copy()->addWeeks(16),
                "May - June" => $firstMondayOfYear->copy()->addWeeks(20),
                "June - July" => $firstMondayOfYear->copy()->addWeeks(24),
                "July - August" => $firstMondayOfYear->copy()->addWeeks(28),
                "August - September" => $firstMondayOfYear->copy()->addWeeks(32),
                "September - October" => $firstMondayOfYear->copy()->addWeeks(36),
                "October - November" => $firstMondayOfYear->copy()->addWeeks(40),
                "November - December" => $firstMondayOfYear->copy()->addWeeks(44),
                "December - January" => $firstMondayOfYear->copy()->addWeeks(48),
            ];

            // Extract base month name exactly like StaffRoutesController
            $baseMonthName = trim(str_replace($year, '', $selectedMonth));

            // If the month name is not in customStartDates, default to January - February
            if (!array_key_exists($baseMonthName, $customStartDates)) {
                \Log::warning('Month not found in customStartDates', [
                    'baseMonthName' => $baseMonthName,
                    'selectedMonth' => $selectedMonth
                ]);
                $baseMonthName = "January - February";
            }

            $firstDayOfMonth = $customStartDates[$baseMonthName];

            // Build weeks EXACTLY like StaffRoutesController show method
            $weeks = collect();
            $tempFirstDay = $firstDayOfMonth->copy();

            for ($i = 0; $i < 4; $i++) {
                $endOfWeek = $tempFirstDay->copy()->addDays(6);

                $weeks->push([
                    'week_number' => $i + 1, // week_number starts from 1 in show method
                    'start_date' => $tempFirstDay->format('d F Y'),
                    'end_date' => $endOfWeek->format('d F Y'),
                    'routes' => [],
                ]);

                $tempFirstDay->addDays(7);
            }

            // Get the specific week (week0 = index 0 = week_number 1)
            $targetWeekIndex = $weekNumber; // week0=0, week1=1, week2=2, week3=3
            $targetWeek = $weeks->get($targetWeekIndex);

            if (!$targetWeek) {
                return 0;
            }

            // Build mergedSchedules EXACTLY like StaffRoutesController show method
            $weekStartDate = Carbon::parse($targetWeek['start_date']);
            $weekEndDate = Carbon::parse($targetWeek['end_date']);

            $filteredRoutes = $staffRoute->clientRoute->flatMap(function ($clientRoute) use ($weekStartDate, $weekEndDate) {
                return $clientRoute->clientSchedule->filter(function ($clientSchedule) use ($weekStartDate, $weekEndDate) {
                    $scheduleStartDate = Carbon::parse($clientSchedule->start_date);
                    $scheduleEndDate = Carbon::parse($clientSchedule->end_date);
                    $serviceFrequency = optional($clientSchedule->clientName)->service_frequency;

                    // Monthly/BiMonthly: check if schedule's exact date falls in this week
                    if ($serviceFrequency == 'monthly' || $serviceFrequency == 'biMonthly') {
                        $scheduleDay = $scheduleStartDate->day;
                        $scheduleMonth = $scheduleStartDate->month;
                        $scheduleYear = $scheduleStartDate->year;

                        for ($d = $weekStartDate->copy(); $d->lte($weekEndDate); $d->addDay()) {
                            if ($d->day == $scheduleDay && $d->month == $scheduleMonth && $d->year == $scheduleYear) {
                                return true;
                            }
                        }
                        return false;
                    }

                    // Weekly/Regular: check if schedule overlaps with week
                    return ($scheduleStartDate->gte($weekStartDate) && $scheduleStartDate->lte($weekEndDate)) ||
                        ($scheduleEndDate->gte($weekStartDate) && $scheduleEndDate->lte($weekEndDate)) ||
                        ($scheduleStartDate->lte($weekStartDate) && $scheduleEndDate->gte($weekEndDate));
                })->map(function ($clientSchedule) {
                    // EXACT same mapping as StaffRoutesController show method
                    $priceSum = optional($clientSchedule->clientSchedulePrice)
                        ->map(fn($schedulePrice) => (float) (optional($schedulePrice->clientPaymentPrice)->value ?? 0))
                        ->sum();

                    // Handle extra_work_price - it's stored as JSON array ["29", "50"] etc
                    $extraWorkPrice = 0;
                    if (!empty($clientSchedule->extra_work_price)) {
                        // Decode JSON if it's a string
                        $extraPrices = is_string($clientSchedule->extra_work_price)
                            ? json_decode($clientSchedule->extra_work_price, true)
                            : $clientSchedule->extra_work_price;

                        // Sum all extra work prices
                        if (is_array($extraPrices)) {
                            $extraWorkPrice = array_sum(array_map('floatval', $extraPrices));
                        } else {
                            $extraWorkPrice = (float) $extraPrices;
                        }
                    }

                    return [
                        'clientSchedule' => optional($clientSchedule)->status ?? '',
                        'payment_type' => optional($clientSchedule->clientName)->payment_type ?? null,
                        'invoice_amount' => $priceSum + $extraWorkPrice,
                    ];
                });
            });

            $pendingRoutes = collect($filteredRoutes)->filter(function ($route) {
                return $route['clientSchedule'] === 'completed' || empty($route['clientSchedule']);
            });

            $expectedCash = 0;
            foreach ($pendingRoutes as $route) {
                if ($route['payment_type'] === 'cash') {
                    $expectedCash += $route['invoice_amount'];
                }
            }

            // Ensure we return a numeric value
            return (float) $expectedCash;
        } catch (\Exception $e) {
            Log::error('Error calculating expected cash: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return 0;
        }
    }

    /**
     * Get expected cash via API
     */
    public function getExpectedCash(Request $request)
    {
        // Force JSON response - set headers before any processing
        $request->headers->set('Accept', 'application/json');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        try {
            $routeId = $request->input('route_id');
            $week = $request->input('week');
            $month = $request->input('month');
            $year = $request->input('year');

            // For staff users, verify they have access to this route
            $user = Auth::user();
            if ($user && !$user->hasRole('admin')) {
                $assignedRouteIds = AssignRoute::where('staff_id', $user->id)
                    ->pluck('route_id')
                    ->toArray();

                if (!in_array($routeId, $assignedRouteIds)) {
                    return response()->json([
                        'success' => false,
                        'expected_cash' => 0,
                        'already_deposited' => 0,
                        'remaining' => 0,
                        'message' => 'You do not have access to this route'
                    ], 403)->header('Content-Type', 'application/json');
                }
            }

            if (!$routeId || !$week || !$month || !$year) {
                $response = response()->json([
                    'success' => false,
                    'expected_cash' => 0,
                    'already_deposited' => 0,
                    'remaining' => 0,
                    'message' => 'Missing required parameters: route_id, week, month, and year are required'
                ], 400);
                $response->header('Content-Type', 'application/json');
                return $response;
            }

            $expectedCash = $this->calculateExpectedCash($routeId, $week, $month, $year);

            // Ensure it's a number
            if (!is_numeric($expectedCash)) {
                \Log::error('Expected cash is not numeric', [
                    'value' => $expectedCash,
                    'type' => gettype($expectedCash)
                ]);
                $expectedCash = 0;
            }

            // Get already deposited amount for this week/month/year/route
            $alreadyDeposited = Deposit::where('route_id', $routeId)
                ->where('week', $week)
                ->where('month', $month)
                ->where('year', $year)
                ->sum('deposit_amount');

            // Ensure it's a number
            if (!is_numeric($alreadyDeposited)) {
                \Log::error('Already deposited is not numeric', [
                    'value' => $alreadyDeposited,
                    'type' => gettype($alreadyDeposited)
                ]);
                $alreadyDeposited = 0;
            }

            // Convert to float for safe calculation
            $expectedCash = (float) $expectedCash;
            $alreadyDeposited = (float) $alreadyDeposited;
            $remaining = max(0, $expectedCash - $alreadyDeposited);

            \Log::info('getExpectedCash result', [
                'route_id' => $routeId,
                'week' => $week,
                'month' => $month,
                'year' => $year,
                'expected_cash' => $expectedCash,
                'already_deposited' => $alreadyDeposited,
                'remaining' => $remaining
            ]);

            $response = response()->json([
                'success' => true,
                'expected_cash' => round($expectedCash, 2),
                'already_deposited' => round($alreadyDeposited, 2),
                'remaining' => round($remaining, 2)
            ]);
            $response->header('Content-Type', 'application/json');
            $response->header('Cache-Control', 'no-cache, no-store, must-revalidate');
            return $response;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Model not found in getExpectedCash: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'expected_cash' => 0,
                'already_deposited' => 0,
                'remaining' => 0,
                'message' => 'Route not found'
            ], 404)->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            \Log::error('Error in getExpectedCash: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json([
                'success' => false,
                'expected_cash' => 0,
                'already_deposited' => 0,
                'remaining' => 0,
                'message' => 'Error calculating expected cash: ' . $e->getMessage()
            ], 500)->header('Content-Type', 'application/json');
        }
    }

    public function store(Request $request)
    {
        $isAjax = $request->ajax() || $request->wantsJson() || $request->expectsJson();

        try {
            $user = Auth::user();

            // For staff users, verify they have access to the selected route
            if ($user && !$user->hasRole('admin')) {
                $assignedRouteIds = AssignRoute::where('staff_id', $user->id)
                    ->pluck('route_id')
                    ->toArray();

                $requestedRouteId = $request->input('route_id');
                if (!in_array($requestedRouteId, $assignedRouteIds)) {
                    if ($isAjax) {
                        return response()->json([
                            'message' => 'You do not have access to this route',
                            'errors' => [
                                'route_id' => ['You do not have access to the selected route']
                            ]
                        ], 403);
                    }
                    return redirect()->back()->withErrors([
                        'route_id' => 'You do not have access to this route'
                    ])->withInput()->with([
                        'title' => 'Error',
                        'message' => 'You do not have access to the selected route',
                        'type' => 'error'
                    ]);
                }
            }

            $validated = $request->validate([
                'route_id' => 'required|exists:staff_routes,id',
                'week' => 'required|string',
                'month' => 'required|string',
                'year' => 'required|integer|min:2020|max:2100',
                'total_amount' => 'required|numeric|min:0',
                'deposit_amount' => 'required|numeric|min:0',
                'is_deposit' => 'nullable|boolean',
                'deposit_date' => 'nullable|date',
                'notes' => 'nullable|string|max:1000',
            ]);

            // Calculate expected cash for validation
            $expectedCash = $this->calculateExpectedCash(
                $validated['route_id'],
                $validated['week'],
                $validated['month'],
                $validated['year']
            );

            // Get already deposited amount for this week/month/year/route (excluding current deposit if editing)
            $alreadyDeposited = Deposit::where('route_id', $validated['route_id'])
                ->where('week', $validated['week'])
                ->where('month', $validated['month'])
                ->where('year', $validated['year'])
                ->sum('deposit_amount');

            $remaining = max(0, $expectedCash - $alreadyDeposited);

            // Auto-set total_amount to expected cash (for display only)
            $validated['total_amount'] = $expectedCash;

            // Auto-set staff_id for staff users (admin can set manually if needed)
            if ($user && !$user->hasRole('admin')) {
                $validated['staff_id'] = $user->id;
            } else {
                // For admin, if staff_id is provided, use it; otherwise set to null
                $validated['staff_id'] = $request->input('staff_id', null);
            }

            // Validate that deposit_amount doesn't exceed remaining expected cash
            if ($validated['deposit_amount'] > $remaining) {
                if ($isAjax) {
                    return response()->json([
                        'message' => 'Deposit amount exceeds remaining expected cash for this week',
                        'errors' => [
                            'deposit_amount' => ["Deposit amount cannot exceed remaining expected cash of $" . number_format($remaining, 2) . " for this week. (Total Expected: $" . number_format($expectedCash, 2) . ", Already Deposited: $" . number_format($alreadyDeposited, 2) . ")"]
                        ]
                    ], 422);
                }

                return redirect()->back()->withErrors([
                    'deposit_amount' => "Deposit amount cannot exceed expected cash of $" . number_format($expectedCash, 2) . " for this week."
                ])->withInput()->with([
                    'title' => 'Validation Error',
                    'message' => 'Deposit amount exceeds expected cash for this week',
                    'type' => 'error'
                ]);
            }

            // Set is_deposit to false if not checked
            $validated['is_deposit'] = $request->has('is_deposit') ? true : false;

            Deposit::create($validated);

            Notification::create([
                'user_id' => 2,
                'action_id' => 2,
                'title' => Auth::user()->name . ' New Deposit Created',
                'message' => 'A new deposit has been created.',
                'type' => 'new_deposit',
            ]);

            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => 'Deposit created successfully'
                ]);
            }

            return redirect()->route('deposits.index')->with([
                'title' => 'Success',
                'message' => 'Deposit created successfully',
                'type' => 'success'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($isAjax) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()->back()->withErrors($e->errors())->withInput()->with([
                'title' => 'Validation Error',
                'message' => 'Please check the form and try again',
                'type' => 'error'
            ]);
        } catch (\Exception $e) {
            if ($isAjax) {
                return response()->json([
                    'message' => 'Failed to create deposit: ' . $e->getMessage(),
                    'errors' => []
                ], 500);
            }

            return redirect()->back()->withInput()->with([
                'title' => 'Error',
                'message' => 'Failed to create deposit: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    /**
     * Get deposit data for editing (AJAX)
     */
    public function getDepositData($id)
    {
        try {
            $deposit = Deposit::with(['route'])->findOrFail($id);
            $user = Auth::user();

            // For staff users, check if this deposit belongs to them
            if ($user && !$user->hasRole('admin')) {
                if ($deposit->staff_id != $user->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have permission to edit this deposit.'
                    ], 403)->header('Content-Type', 'application/json');
                }
            }

            return response()->json([
                'success' => true,
                'deposit' => [
                    'id' => $deposit->id,
                    'route_id' => $deposit->route_id,
                    'week' => $deposit->week,
                    'month' => $deposit->month,
                    'year' => $deposit->year,
                    'deposit_amount' => $deposit->deposit_amount,
                    'deposit_date' => $deposit->deposit_date ? $deposit->deposit_date->format('Y-m-d') : '',
                    'notes' => $deposit->notes,
                ]
            ])->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load deposit data: ' . $e->getMessage()
            ], 500)->header('Content-Type', 'application/json');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $deposit = Deposit::findOrFail($id);
            $user = Auth::user();

            // For staff users, check if this deposit belongs to them
            if ($user && !$user->hasRole('admin')) {
                if ($deposit->staff_id != $user->id) {
                    return redirect()->route('deposits.index')->with([
                        'title' => 'Error',
                        'message' => 'You do not have permission to update this deposit.',
                        'type' => 'error'
                    ]);
                }
            }

            $validated = $request->validate([
                'route_id' => 'required|exists:staff_routes,id',
                'week' => 'required|string',
                'month' => 'required|string',
                'year' => 'required|integer|min:2020|max:2100',
                'total_amount' => 'required|numeric|min:0',
                'deposit_amount' => 'required|numeric|min:0',
                'is_deposit' => 'nullable|boolean',
                'deposit_date' => 'nullable|date',
                'notes' => 'nullable|string|max:1000',
            ]);

            // Calculate expected cash for validation
            $expectedCash = $this->calculateExpectedCash(
                $validated['route_id'],
                $validated['week'],
                $validated['month'],
                $validated['year']
            );

            // Get already deposited amount for this week/month/year/route (excluding current deposit)
            $alreadyDeposited = Deposit::where('route_id', $validated['route_id'])
                ->where('week', $validated['week'])
                ->where('month', $validated['month'])
                ->where('year', $validated['year'])
                ->where('id', '!=', $id)
                ->sum('deposit_amount');

            // Calculate remaining expected cash
            $remaining = max(0, $expectedCash - $alreadyDeposited);

            // Auto-set total_amount to expected cash (for display only)
            $validated['total_amount'] = $expectedCash;

            // Validate that deposit_amount doesn't exceed remaining expected cash
            if ($validated['deposit_amount'] > $remaining) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'message' => 'Deposit amount exceeds remaining expected cash for this week',
                        'errors' => [
                            'deposit_amount' => ["Deposit amount cannot exceed remaining expected cash of $" . number_format($remaining, 2) . " for this week. (Total Expected: $" . number_format($expectedCash, 2) . ", Already Deposited: $" . number_format($alreadyDeposited, 2) . ")"]
                        ]
                    ], 422);
                }

                return redirect()->back()->withErrors([
                    'deposit_amount' => "Deposit amount cannot exceed remaining expected cash of $" . number_format($remaining, 2) . " for this week. (Total Expected: $" . number_format($expectedCash, 2) . ", Already Deposited: $" . number_format($alreadyDeposited, 2) . ")"
                ])->withInput()->with([
                    'title' => 'Validation Error',
                    'message' => 'Deposit amount exceeds remaining expected cash for this week',
                    'type' => 'error'
                ]);
            }

            // Set is_deposit to false if not checked
            $validated['is_deposit'] = $request->has('is_deposit') ? true : false;

            $deposit->update($validated);

            return redirect()->route('deposits.index')->with([
                'title' => 'Success',
                'message' => 'Deposit updated successfully',
                'type' => 'success'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('deposits.index')->with([
                'title' => 'Error',
                'message' => 'Deposit not found',
                'type' => 'error'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput()->with([
                'title' => 'Validation Error',
                'message' => 'Please check the form and try again',
                'type' => 'error'
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with([
                'title' => 'Error',
                'message' => 'Failed to update deposit: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    /**
     * Remove the specified deposit.
     */
    public function destroy($id)
    {
        try {
            $deposit = Deposit::findOrFail($id);
            $user = Auth::user();

            // For staff users, check if this deposit belongs to them
            if ($user && !$user->hasRole('admin')) {
                if ($deposit->staff_id != $user->id) {
                    if (request()->ajax() || request()->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'You do not have permission to delete this deposit.'
                        ], 403)->header('Content-Type', 'application/json');
                    }
                    return redirect()->route('deposits.index')->with([
                        'title' => 'Error',
                        'message' => 'You do not have permission to delete this deposit.',
                        'type' => 'error'
                    ]);
                }
            }

            $deposit->delete();
            Notification::create([
                'user_id' => 2,
                'action_id' => 2,
                'title' => Auth::user()->name . ' has deleted a Deposit',
                'message' => 'A deposit has been deleted.',
                'type' => 'deposit_deleted',
            ]);

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Deposit deleted successfully'
                ])->header('Content-Type', 'application/json');
            }

            return redirect()->route('deposits.index')->with([
                'title' => 'Success',
                'message' => 'Deposit deleted successfully',
                'type' => 'success'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('deposits.index')->with([
                'title' => 'Error',
                'message' => 'Deposit not found',
                'type' => 'error'
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'title' => 'Error',
                'message' => 'Failed to delete deposit: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    public function routeDetail(Request $request, $routeId)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return redirect()->route('login');
            }

            if ($user->hasRole('staff')) {
                $assignedRouteIds = AssignRoute::where('staff_id', $user->id)
                    ->pluck('route_id')
                    ->toArray();

                if ($routeId !== 'unassigned' && !in_array($routeId, $assignedRouteIds)) {
                    return redirect()->back()->with([
                        'title' => 'Error',
                        'message' => 'You do not have access to this route',
                        'type' => 'error'
                    ]);
                }
            } else {
                $assignedRouteIds = StaffRoute::where('status', 1)->pluck('id')->toArray();
            }

            if ($routeId === 'unassigned' || $routeId === '0') {
                $route = (object)[
                    'id' => 'unassigned',
                    'name' => 'Unassigned Clients'
                ];
            } else {
                $route = StaffRoute::findOrFail($routeId);
            }

            // Fetch payments specifically for this route
            $request->merge(['route_id' => $routeId]);
            $cashPayments = $this->getUndepositedCashPayments($user->id, $request, $assignedRouteIds);

            $totalAmount = $cashPayments->sum(fn($payment) => (float) ($payment->final_price ?? 0));

            return view('dashboard.undeposited_route_detail', compact('route', 'cashPayments', 'totalAmount'));
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'title' => 'Error',
                'message' => 'Failed to load route details: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    public function markRouteDeposited(Request $request, $routeId)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        $assignedRouteIds = AssignRoute::where('staff_id', $user->id)
            ->pluck('route_id')
            ->toArray();

        if ($user->hasRole('staff') && $routeId !== 'unassigned' && !in_array($routeId, $assignedRouteIds)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this route.',
            ], 403);
        }

        // Set route_id in request so we can reuse helper
        $request->merge(['route_id' => $routeId]);
        $payments = $this->getUndepositedCashPayments($user->id, $request, $assignedRouteIds);

        if ($payments->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No undeposited cash payments found for this route.',
            ], 400);
        }

        foreach ($payments as $payment) {
            if ($payment->payment_type !== 'cash' || $payment->status !== 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only paid cash payments can be marked as deposited.',
                ], 422);
            }

            if ($payment->payment_status === 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'One or more payments are already marked as deposited.',
                ], 422);
            }

            if (Deposit::where('client_payment_id', $payment->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'A deposit record already exists for one or more payments.',
                ], 422);
            }
        }

        try {
            DB::transaction(function () use ($payments, $user) {
                foreach ($payments as $payment) {
                    $payment->update(['payment_status' => 'paid']);
                    $this->createDepositFromPayment($payment, $user);
                }
            });
        } catch (\Exception $e) {
            Log::error('Error marking route payments as deposited: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark payments as deposited: ' . $e->getMessage(),
            ], 500);
        }

        Notification::create([
            'user_id' => 2,
            'action_id' => 2,
            'title' => ($user->name ?? 'Staff') . ' submitted cash deposit(s)',
            'message' => $payments->count() . ' cash payment(s) marked for deposit review.',
            'type' => 'new_deposit',
        ]);

        return response()->json([
            'success' => true,
            'message' => $payments->count() . ' payments marked as deposited successfully.',
        ]);
    }

    private function getUndepositedCashPayments($staffId, Request $request, array $assignedRouteIds)
    {
        $depositedScheduleIds = Deposit::whereNotNull('schedule_id')->pluck('schedule_id');
        $depositedPaymentIds = Deposit::whereNotNull('client_payment_id')->pluck('client_payment_id');

        $query = ClientPayment::with([
            'clientSchedule.clientName.clientRouteStaff.route',
            'client.clientRouteStaff.route',
        ])
            ->where('option', '!=', 'omit')
            ->where('payment_type', 'cash')
            ->where('status', 'paid')
            ->where('staff_id', $staffId)
            ->where(function ($q) {
                $q->whereNull('payment_status')->orWhere('payment_status', 'payment')->orWhere('payment_status', 'pending');
            })
            ->when($depositedScheduleIds->isNotEmpty(), function ($q) use ($depositedScheduleIds) {
                $q->where(function ($sub) use ($depositedScheduleIds) {
                    $sub->whereNull('schedule_id')->orWhereNotIn('schedule_id', $depositedScheduleIds);
                });
            })
            ->when($depositedPaymentIds->isNotEmpty(), function ($q) use ($depositedPaymentIds) {
                $q->whereNotIn('id', $depositedPaymentIds);
            });

        $payments = $query->orderByDesc('created_at')->get()->map(function ($payment) use ($assignedRouteIds) {
            $routeAssignment = $payment->client?->clientRouteStaff
                ?->first(fn($cr) => in_array($cr->route_id, $assignedRouteIds));

            $payment->route_name = $routeAssignment?->route?->name ?? 'N/A';
            $payment->route_id_display = $routeAssignment?->route_id;

            return $payment;
        });

        if ($request->filled('route_id')) {
            $routeId = $request->route_id;
            $payments = $payments->filter(function ($payment) use ($routeId) {
                if ($routeId === 'unassigned' || $routeId === '0') {
                    return empty($payment->route_id_display);
                }
                return (string) $payment->route_id_display === (string) $routeId;
            });
        }

        return $payments;
    }

    /**
     * Mark undeposited cash client payments as deposited (payment_status = paid)
     * and create a deposit record linked to the schedule.
     */
    public function markDeposited(Request $request)
    {
        $request->validate([
            'payment_ids' => 'required|array|min:1',
            'payment_ids.*' => 'required|exists:client_payments,id',
            'deposit_date' => 'nullable|date',
        ]);

        $user = Auth::user();
        $payments = ClientPayment::with([
            'clientSchedule',
            'client.clientRouteStaff',
        ])->whereIn('id', $request->payment_ids)->get();

        if ($payments->count() !== count($request->payment_ids)) {
            return response()->json([
                'success' => false,
                'message' => 'One or more payments could not be found.',
            ], 404);
        }

        foreach ($payments as $payment) {
            if ($payment->payment_type !== 'cash' || $payment->status !== 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only paid cash payments can be marked as deposited.',
                ], 422);
            }

            if ($payment->payment_status === 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'One or more payments are already marked as deposited.',
                ], 422);
            }

            if ($user->hasRole('staff') && $payment->staff_id != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to update one or more payments.',
                ], 403);
            }

            if ($payment->schedule_id && Deposit::where('schedule_id', $payment->schedule_id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'A deposit record already exists for one or more selected schedules.',
                ], 422);
            }

            if (Deposit::where('client_payment_id', $payment->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'A deposit record already exists for one or more selected payments.',
                ], 422);
            }
        }

        // A caller can pass an explicit deposit_date (e.g. the "Date Deposited" inline picker on
        // the Total Undeposited Cash page) to record the deposit as already made on that date.
        // Without one (e.g. the plain "Mark Deposited" action elsewhere), it's staged for review
        // with is_deposit left false, same as before.
        $depositDate = $request->filled('deposit_date') ? Carbon::parse($request->deposit_date) : null;
        $isDeposit = (bool) $depositDate;

        try {
            DB::transaction(function () use ($payments, $user, $depositDate, $isDeposit) {
                foreach ($payments as $payment) {
                    $payment->update(['payment_status' => 'paid']);
                    $this->createDepositFromPayment($payment, $user, $depositDate, $isDeposit);
                }
            });
        } catch (\Exception $e) {
            Log::error('Error marking payments as deposited: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark payments as deposited: ' . $e->getMessage(),
            ], 500);
        }

        Notification::create([
            'user_id' => 2,
            'action_id' => 2,
            'title' => ($user->name ?? 'Staff') . ' submitted cash deposit(s)',
            'message' => count($request->payment_ids) . ' cash payment(s) marked for deposit review.',
            'type' => 'new_deposit',
        ]);

        return response()->json([
            'success' => true,
            'message' => count($request->payment_ids) === 1
                ? 'Payment marked as deposited successfully.'
                : count($request->payment_ids) . ' payments marked as deposited successfully.',
            'deposit_date' => $depositDate?->format('m-d-Y'),
        ]);
    }

    private function createDepositFromPayment(ClientPayment $payment, $user, $depositDate = null, bool $isDeposit = false): Deposit
    {
        $schedule = $payment->clientSchedule;
        $routeId = $payment->client?->clientRouteStaff?->first()?->route_id;

        if (!$routeId) {
            \Log::warning('Unable to determine route for payment #' . $payment->id);
        }

        if (!$payment->schedule_id) {
            \Log::warning('Payment #' . $payment->id . ' is not linked to a schedule.');
        }

        $referenceDate = $schedule?->service_date
            ?? $schedule?->start_date
            ?? $payment->payment_date
            ?? now();

        $referenceDate = Carbon::parse($referenceDate);

        $week = $schedule?->week;
        if ($week === null || $week === '') {
            $week = 'week0';
        } elseif (is_numeric($week)) {
            $week = 'week' . $week;
        } elseif (!str_starts_with((string) $week, 'week')) {
            $week = 'week' . preg_replace('/[^0-9]/', '', (string) $week);
        }

        $month = $schedule?->month ?: $referenceDate->format('F');
        $amount = (float) ($payment->final_price ?? 0);

        return Deposit::create([
            'route_id' => $routeId,
            'staff_id' => $payment->staff_id ?? $user->id,
            'schedule_id' => $payment->schedule_id,
            'client_payment_id' => $payment->id,
            'week' => $week,
            'month' => $month,
            'year' => (int) $referenceDate->format('Y'),
            'total_amount' => $amount,
            'deposit_amount' => $amount,
            'is_deposit' => $isDeposit,
            'deposit_date' => $depositDate ? Carbon::parse($depositDate) : now(),
        ]);
    }

    /**
     * Update deposit status (is_deposit checkbox)
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $deposit = Deposit::findOrFail($id);
            $user = Auth::user();

            // Admin can update any deposit; staff can only update their own.
            if (!$user || (!$user->hasRole('admin') && $deposit->staff_id != $user->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update deposit status'
                ], 403)->header('Content-Type', 'application/json');
            }

            $deposit->is_deposit = $request->input('is_deposit', 0);
            if ($request->filled('deposit_date')) {
                // An explicit date (e.g. from the "Date Deposited" inline picker) always wins.
                $deposit->deposit_date = Carbon::parse($request->input('deposit_date'));
            } elseif ($deposit->is_deposit && !$deposit->deposit_date) {
                $deposit->deposit_date = now();
            }
            $deposit->save();
            Notification::create([
                'user_id' => $deposit->staff_id,
                'action_id' => $deposit->staff_id,
                'title' => 'Deposit Status Updated',
                'message' => 'The status of a deposit has been updated.',
                'type' => 'deposit_status_updated',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Deposit status updated successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating deposit status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating deposit status: ' . $e->getMessage()
            ], 500);
        }
    }

}
