<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\{AssignRoute, Notification, StaffRoute, AssignWeek, ClientRoute, ClientSchedule};
use App\Http\Requests\StaffRouteRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class StaffRoutesController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:staffroutes-list|staffroutes-create|staffroutes-edit|staffroutes-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:staffroutes-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:staffroutes-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:staffroutes-delete', ['only' => ['destroy']]);
        $this->middleware('permission:staffroutes-list', ['only' => ['show']]);
    }

    public function index()
    {
        // 1. First check the user
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        $currentYear = now()->year;
        $currentMonth = now()->format('F');
        $nextMonth = now()->addMonth()->format('F');

        $selectedYear = $currentYear;

        $firstMondayOfYear = Carbon::parse("first Monday of January $selectedYear");

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
        $todayDate = Carbon::now();

        $baseMonthName = null;
        foreach ($customStartDates as $range => $startDate) {
            if ($todayDate->gte($startDate) && $todayDate->lt($startDate->copy()->addWeeks(4))) {
                $baseMonthName = $range;
                break;
            }
        }

        if (!array_key_exists($baseMonthName, $customStartDates)) {
            $baseMonthName = "January - February";
            $selectedYear = $currentYear;
        }

        $monthStartDate = $customStartDates[$baseMonthName];

        $weeks = collect();
        $firstDayOfMonth = $monthStartDate->copy();

        for ($i = 0; $i < 4; $i++) {
            $weekStart = $firstDayOfMonth->copy();
            $weekEnd = $firstDayOfMonth->copy()->addDays(6);

            $weeks->push([
                'week_number' => $i + 1,
                'start_date' => $weekStart,
                'end_date' => $weekEnd,
            ]);

            $firstDayOfMonth->addDays(7);
        }

        $today = \Carbon\Carbon::now();
        $currentWeek = $weeks->first(fn($w) => $today->between($w['start_date'], $w['end_date'])) ?: $weeks->first();
        $currentWeekStart = $currentWeek['start_date'];
        $currentWeekEnd = $currentWeek['end_date'];
        // 3. MAIN QUERY WITH STRICT AUTH CHECK
        $query = StaffRoute::with([
            'clientRoute' => function ($q) {
                $q->whereHas('clients', fn($cq) => $cq->where('status', 1));
            },
            'clientRoute.clientSchedule.clientName'
        ]);

        // Role check: If NOT Admin, apply filter
        if (!$user->hasRole('admin')) {
            // Get route IDs from AssignRoute model for this staff member
            // Ensure 'staff_id' column matches the user's ID
            $assignedRouteIds = \App\Models\AssignRoute::where('staff_id', $user->id)
                ->pluck('route_id')
                ->toArray();

            // If staff has no assigned routes, show empty result
            if (empty($assignedRouteIds)) {
                $staffroutes = collect();
            } else {
                $query->whereIn('id', $assignedRouteIds)->where('status', 1);
                $staffroutes = $query->orderBy('created_at', 'DESC')->get();
            }
        } else {
            // If Admin, show all
            $staffroutes = $query->orderBy('created_at', 'DESC')->get();
        }

        // 4. Data Mapping
        $staffroutes = $staffroutes->map(function ($route) use ($currentWeekStart, $currentWeekEnd) {
            $weekSchedules = $route->clientRoute->flatMap(function ($cr) use ($currentWeekStart, $currentWeekEnd) {
                return $cr->clientSchedule->filter(function ($cs) use ($currentWeekStart, $currentWeekEnd) {
                    $start = \Carbon\Carbon::parse($cs->start_date);
                    $end = \Carbon\Carbon::parse($cs->end_date);

                    // Simplified overlap logic
                    return ($start <= $currentWeekEnd && $end >= $currentWeekStart);
                });
            });

            $grouped = $weekSchedules->groupBy(function ($clientSchedule) {
                return $clientSchedule->client_id . '_' . $clientSchedule->start_date;
            });

            $route->jobs_pending = $grouped->filter(function ($schedule) {
                return empty($schedule->status) || $schedule->status === 'pending';
            })->count();

            $route->jobs_total = $grouped->count();

            $route->jobs_completed = $grouped->filter(function ($schedule) {
                return !empty($schedule->status) && $schedule->status === 'completed';
            })->count();

            return $route;
        });

        return view('staffroutes.index', compact('staffroutes'));
    }

    public function create()
    {
        return view('staffroutes.create');
    }

    public function store(StaffRouteRequest $request)
    {
        $staffroute = new StaffRoute;
        $staffroute->name = $request['name'];
        $staffroute->save();
        Notification::create([
            'user_id' => auth()->user()->id,
            'action_id' => $staffroute->id,
            'title' => 'New Staff Route Created',
            'type' => 'staff_route_created',
            'message' => 'Staff Route "' . $staffroute->name . '" has been created.',
        ]);
        return redirect()->route('staffroutes.index')->with(['title' => 'Done', 'message' => 'Route Created Successfully', 'type' => 'success']);
    }

    public function show($id, Request $request)
    {
        $staffRoute = StaffRoute::with([
            'clientRoute' => function ($query) {
                $query->whereHas('clients', function ($clientQuery) {
                    $clientQuery->where('status', 1);
                });
            },
            // 'clientRoute.clientSchedule.clientSchedulePrice.clientPaymentPrice'
            'clientRoute.clientSchedule.clientSchedulePrice' => function ($query) {
                $query->join('client_price_lists', 'client_schedule_prices.price_id', '=', 'client_price_lists.id')
                    ->orderBy('client_price_lists.position', 'asc')
                    ->select('client_schedule_prices.*');
            },
            'clientRoute.clientSchedule.clientSchedulePrice.clientPaymentPrice'
        ])->findOrFail($id);

        $currentYear = now()->year;
        $currentDate = now();

        $firstMondayOfCurrentYear = \Carbon\Carbon::parse("first Monday of January $currentYear");
        $defaultCycleStartDates = [
            "January - February"  => $firstMondayOfCurrentYear->copy()->addDays(0),
            "February - March"    => $firstMondayOfCurrentYear->copy()->addWeeks(4),
            "March"               => $firstMondayOfCurrentYear->copy()->addWeeks(8),
            "March - April"       => $firstMondayOfCurrentYear->copy()->addWeeks(12),
            "April - May"         => $firstMondayOfCurrentYear->copy()->addWeeks(16),
            "May - June"          => $firstMondayOfCurrentYear->copy()->addWeeks(20),
            "June - July"         => $firstMondayOfCurrentYear->copy()->addWeeks(24),
            "July - August"       => $firstMondayOfCurrentYear->copy()->addWeeks(28),
            "August - September"  => $firstMondayOfCurrentYear->copy()->addWeeks(32),
            "September - October" => $firstMondayOfCurrentYear->copy()->addWeeks(36),
            "October - November"  => $firstMondayOfCurrentYear->copy()->addWeeks(40),
            "November - December" => $firstMondayOfCurrentYear->copy()->addWeeks(44),
            "December - January"  => $firstMondayOfCurrentYear->copy()->addWeeks(48),
        ];

        $defaultCycleName = "January - February";
        foreach ($defaultCycleStartDates as $range => $startDate) {
            if ($currentDate->gte($startDate) && $currentDate->lt($startDate->copy()->addWeeks(4))) {
                $defaultCycleName = $range;
                break;
            }
        }

        $selectedMonth = $request->input('month', "$defaultCycleName $currentYear");

        preg_match('/\d{4}/', $selectedMonth, $yearMatch);
        $selectedYear = $yearMatch[0] ?? $currentYear;

        $allMonths = collect([
            '1'  => 'January - February',
            '2'  => 'February - March',
            '3'  => 'March',
            '4'  => 'March - April',
            '5'  => 'April - May',
            '6'  => 'May - June',
            '7'  => 'June - July',
            '8'  => 'July - August',
            '9'  => 'August - September',
            '10' => 'September - October',
            '11' => 'October - November',
            '12' => 'November - December',
            '13' => 'December - January'
        ]);

        $months = $allMonths->map(fn($month) => "$month $selectedYear");

        $firstMondayOfYear = \Carbon\Carbon::parse("first Monday of January $selectedYear");

        $customStartDates = [
            "January - February"  => $firstMondayOfYear->copy()->addDays(0),
            "February - March"    => $firstMondayOfYear->copy()->addWeeks(4),
            "March"               => $firstMondayOfYear->copy()->addWeeks(8),
            "March - April"       => $firstMondayOfYear->copy()->addWeeks(12),
            "April - May"         => $firstMondayOfYear->copy()->addWeeks(16),
            "May - June"          => $firstMondayOfYear->copy()->addWeeks(20),
            "June - July"         => $firstMondayOfYear->copy()->addWeeks(24),
            "July - August"       => $firstMondayOfYear->copy()->addWeeks(28),
            "August - September"  => $firstMondayOfYear->copy()->addWeeks(32),
            "September - October" => $firstMondayOfYear->copy()->addWeeks(36),
            "October - November"  => $firstMondayOfYear->copy()->addWeeks(40),
            "November - December" => $firstMondayOfYear->copy()->addWeeks(44),
            "December - January"  => $firstMondayOfYear->copy()->addWeeks(48),
        ];

        $baseMonthName = trim(str_replace($selectedYear, '', $selectedMonth));

        if (!array_key_exists($baseMonthName, $customStartDates)) {
            $baseMonthName = "January - February";
            $selectedYear  = $currentYear;
        }

        $firstDayOfMonth = $customStartDates[$baseMonthName];
        $weeks = collect();

        for ($i = 0; $i < 4; $i++) {
            $endOfWeek = $firstDayOfMonth->copy()->addDays(6);
            $weeks->push([
                'week_number' => $i + 1,
                'start_date'  => $firstDayOfMonth->format('d F Y'),
                'end_date'    => $endOfWeek->format('d F Y'),
                'routes'      => [],
            ]);
            $firstDayOfMonth->addDays(7);
        }

        $mergedSchedules = $weeks->map(function ($week) use ($staffRoute) {
            $weekStartDate = \Carbon\Carbon::parse($week['start_date']);
            $weekEndDate   = \Carbon\Carbon::parse($week['end_date']);

            $filteredRoutes = $staffRoute->clientRoute->flatMap(function ($clientRoute) use ($weekStartDate, $weekEndDate) {

                $weekSchedules = $clientRoute->clientSchedule->filter(function ($clientSchedule) use ($weekStartDate, $weekEndDate) {
                    $scheduleStartDate = \Carbon\Carbon::parse($clientSchedule->start_date);
                    $serviceFrequency  = optional($clientSchedule->clientName)->service_frequency;

                    if ($serviceFrequency == 'monthly' || $serviceFrequency == 'biMonthly') {
                        for ($d = $weekStartDate->copy(); $d->lte($weekEndDate); $d->addDay()) {
                            if (
                                $d->day   == $scheduleStartDate->day &&
                                $d->month == $scheduleStartDate->month &&
                                $d->year  == $scheduleStartDate->year
                            ) {
                                return true;
                            }
                        }
                        return false;
                    }

                    // Baaki sab: start_date is week mein hai?
                    return $scheduleStartDate->gte($weekStartDate) && $scheduleStartDate->lte($weekEndDate);
                });

                // Step 2: client_id + start_date se group karo
                // Same client ke same start_date wale notes merge honge
                $grouped = $weekSchedules->groupBy(function ($clientSchedule) {
                    return $clientSchedule->client_id . '_' . $clientSchedule->start_date;
                });

                // Step 3: Har group ko merge karke ek row banao
                return $grouped->map(function ($schedules) {
                    $firstSchedule = $schedules->first();

                    // Notes merge karo
                    $mergedNotes = $schedules
                        ->pluck('note')
                        ->filter(fn($n) => !empty($n))
                        ->unique()
                        ->values()
                        ->toArray();
                    $displayNote = !empty($mergedNotes) ? implode(', ', $mergedNotes) : null;

                    // Prices merge karo
                    $mergedMultiPrice    = [];
                    $mergedInvoiceAmount = 0;

                    foreach ($schedules as $sch) {
                        // clientSchedulePrice (note_week_no = 0 wale ki base prices)
                        if ($sch->clientSchedulePrice && $sch->clientSchedulePrice->count() > 0) {
                            foreach ($sch->clientSchedulePrice as $sp) {
                                $val = (float)(optional($sp->clientPaymentPrice)->value ?? 0);
                                $mergedMultiPrice[] = [
                                    'name'  => optional($sp->clientPaymentPrice)->name,
                                    'value' => $val,
                                ];
                                $mergedInvoiceAmount += $val;
                            }
                        }

                        // extra_work prices
                        if ($sch->extra_work && $sch->extra_work_price) {
                            $names  = json_decode($sch->extra_work, true);
                            $values = json_decode($sch->extra_work_price, true);

                            if (is_array($names) && is_array($values)) {
                                foreach ($names as $idx => $name) {
                                    $val = (float)($values[$idx] ?? 0);
                                    $mergedMultiPrice[] = [
                                        'name'  => $name,
                                        'value' => $val,
                                    ];
                                    $mergedInvoiceAmount += $val;
                                }
                            }
                        }
                    }

                    return [
                        'client_hours'            => optional($firstSchedule->clientHour)
                            ->map(fn($scheduleHour) => [
                                'start_hour' => optional($scheduleHour)->start_hour,
                                'end_hour'   => optional($scheduleHour)->end_hour,
                            ]),
                        'client_start_week'       => optional($firstSchedule)->start_date,
                        'client_end_week'         => optional($firstSchedule)->end_date,
                        'client_id'               => optional($firstSchedule->clientName)->id,
                        'client_price_list'       => optional($firstSchedule->clientName)->clientPrice ?? [],
                        'schedule_id'             => optional($firstSchedule)->id,
                        'clientSchedule'          => optional($firstSchedule)->status,
                        'created_at'              => optional($firstSchedule)->created_at,
                        'client_name'             => optional($firstSchedule->clientName)->name
                            ?? optional(optional($firstSchedule->clientName)->user)->name
                            ?? null,
                        'client_unavailable_days' => optional($firstSchedule->clientName)->clientDay ?? [],
                        'client_job'              => optional($firstSchedule->clientName)->description ?? null,
                        'payment_type'            => $firstSchedule->clientName->payment_type ?? null,
                        'service_frequency'       => optional($firstSchedule->clientName)->service_frequency ?? null,
                        'invoice_amount'          => $mergedInvoiceAmount,
                        'multiPrice'              => $mergedMultiPrice,
                        'extra_work_price'        => $firstSchedule->extra_work_price ?? null,
                        'extra_work'              => $firstSchedule->extra_work ?? null,
                        'address'                 => $firstSchedule->clientName->address ?? null,
                        'house_no'                => $firstSchedule->clientName->house_no ?? null,
                        'city'                    => $firstSchedule->clientName->city ?? null,
                        'state'                   => $firstSchedule->clientName->state ?? null,
                        'zip_code'                => $firstSchedule->clientName->postal ?? null,
                        'note'                    => $displayNote,
                        'position'                => optional($firstSchedule->clientName)->position,
                        'staff_position'          => optional($firstSchedule->clientName)->staff_position,
                        'is_completed'            => $firstSchedule->status,
                        'is_increase'             => $firstSchedule->is_increase,
                    ];
                })->values();
            });

            $isAdmin = auth()->user()->hasRole('admin');
            $filteredRoutes = $filteredRoutes->sortBy(
                fn($route) =>
                $isAdmin
                    ? ($route['position'] ?? PHP_INT_MAX)
                    : ($route['staff_position'] ?? PHP_INT_MAX)
            );

            $week['routes'] = $filteredRoutes;
            return $week;
        });

        $routeMonths       = $months->values();
        $currentMonthIndex = $routeMonths->search($selectedMonth);
        $previousMonth     = $routeMonths->get(($currentMonthIndex - 1 + $routeMonths->count()) % $routeMonths->count());
        $nextMonth         = $routeMonths->get(($currentMonthIndex + 1) % $routeMonths->count());

        if ($currentMonthIndex == 12) {
            $nextYear  = (int) $selectedYear + 1;
            $nextMonth = "January - February $nextYear";
        }
        if ($currentMonthIndex == 0) {
            $previousYear  = (int) $selectedYear - 1;
            $previousMonth = "December - January $previousYear";
        }

        $nextYearValue      = (int) $selectedYear + 1;
        $nextYearFirstMonth = "January - February $nextYearValue";

        $weeks->push([
            'week_number' => $i + 1,
            'week'        => 'week' . $i,
            'month'       => strtolower($firstDayOfMonth->format('F')),
            'year'        => $firstDayOfMonth->format('Y'),
            'start_date'  => $firstDayOfMonth->format('d F Y'),
            'end_date'    => $firstDayOfMonth->addDays(6)->format('d F Y'),
            'routes'      => [],
        ]);

        return view('staffroutes.show', compact(
            'staffRoute',
            'months',
            'selectedMonth',
            'mergedSchedules',
            'previousMonth',
            'nextMonth',
            'nextYearFirstMonth'
        ));
    }

    private function calculateInvoiceAmount($clientSchedule)
    {
        $baseTotal = 0;
        if ($clientSchedule->clientSchedulePrice && $clientSchedule->clientSchedulePrice->count() > 0) {
            $baseTotal = $clientSchedule->clientSchedulePrice
                ->map(fn($schedulePrice) => optional($schedulePrice->clientPaymentPrice)->value)
                ->sum();
        }
        return $baseTotal + $this->getExtraWorkPriceSum($clientSchedule->extra_work_price);
    }

    private function getMultiPriceWithExtra($clientSchedule)
    {
        $basePrices = [];
        if ($clientSchedule->clientSchedulePrice && $clientSchedule->clientSchedulePrice->count() > 0) {
            $basePrices = $clientSchedule->clientSchedulePrice
                ->map(fn($schedulePrice) => [
                    'name'  => optional($schedulePrice->clientPaymentPrice)->name,
                    'value' => optional($schedulePrice->clientPaymentPrice)->value
                ])
                ->toArray();
        }

        $extraPrices = [];
        if ($clientSchedule->extra_work && $clientSchedule->extra_work_price) {
            $names  = json_decode($clientSchedule->extra_work, true);
            $values = json_decode($clientSchedule->extra_work_price, true);
            if (is_array($names) && is_array($values)) {
                for ($i = 0; $i < count($names); $i++) {
                    $extraPrices[] = [
                        'name'  => $names[$i] ?? null,
                        'value' => $values[$i] ?? null
                    ];
                }
            }
        }
        return array_merge($basePrices, $extraPrices);
    }

    private function getExtraWorkPriceSum($extraWorkPrice)
    {
        if (!$extraWorkPrice) return 0;
        if (is_string($extraWorkPrice) && (str_starts_with($extraWorkPrice, '[') || str_starts_with($extraWorkPrice, '{'))) {
            try {
                $prices = json_decode($extraWorkPrice, true);
                if (is_array($prices)) {
                    return array_sum(array_map('floatval', $prices));
                }
            } catch (\Exception $e) {
            }
        }
        return floatval($extraWorkPrice);
    }

    public function exportSchedule($id, Request $request)
    {
        $staffRoute = StaffRoute::with([
            'clientRoute' => function ($query) {
                $query->whereHas('clients', function ($q) {
                    $q->where('status', 1);
                })->with([
                    'clientSchedule.clientSchedulePrice.clientPaymentPrice',
                    'clientSchedule.clientName.clientHour',
                    'clientSchedule.clientName.clientDay'
                ]);
            }
        ])->findOrFail($id);

        $currentYear = now()->year;
        $currentMonth = now()->format('F');
        $nextMonth = now()->addMonth()->format('F');

        $selectedMonth = $request->input('selectedMonth', "$currentMonth - $nextMonth $currentYear");
        preg_match('/\d{4}/', $selectedMonth, $yearMatch);
        $selectedYear = $yearMatch[0] ?? $currentYear;

        // Define all months and map them with the selected year
        $allMonths = collect([
            '1' => 'January - February',
            '2' => 'February - March',
            '3' => 'March',
            '4' => 'March - April',
            '5' => 'April - May',
            '6' => 'May - June',
            '7' => 'June - July',
            '8' => 'July - August',
            '9' => 'August - September',
            '10' => 'September - October',
            '11' => 'October - November',
            '12' => 'November - December',
            '13' => 'December - January'
        ]);

        $months = $allMonths->map(fn($month) => "$month $selectedYear");

        $firstMondayOfYear = Carbon::parse("first Monday of January $selectedYear");

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

        $exportData = [];

        $monthData = $this->getMonthData($selectedMonth, $staffRoute, $customStartDates);
        $exportData['data'][] = $monthData['data'];

        return response()->json($exportData);
    }

    public function getMonthData($month, $staffRoute, $customStartDates)
    {
        preg_match('/\d{4}/', $month, $yearMatch);
        $selectedYear  = $yearMatch[0] ?? date('Y');
        $baseMonthName = trim(str_replace($selectedYear, '', $month));

        if (!array_key_exists($baseMonthName, $customStartDates)) {
            $baseMonthName = "January - February";
        }

        $firstDayOfMonth = $customStartDates[$baseMonthName]->copy();
        $data = [];

        $intervalMap = [
            '4_weeks'  => 4,
            '8_weeks'  => 8,
            '12_weeks' => 12,
            '24_weeks' => 24,
            '52_weeks' => 52,
        ];

        $checkOccurrence = function ($noteDate, $intervalWeeks) use (&$weekStartDate, &$weekEndDate) {
            $currentDate = \Carbon\Carbon::parse($noteDate);
            while ($currentDate->lte($weekEndDate->copy()->addYear())) {
                if ($currentDate->gte($weekStartDate) && $currentDate->lte($weekEndDate)) {
                    return true;
                }
                if ($currentDate->gt($weekEndDate)) break;
                $currentDate->addWeeks($intervalWeeks);
            }
            return false;
        };

        for ($i = 0; $i < 4; $i++) {
            $weekStartDate = $firstDayOfMonth->copy();
            $weekEndDate   = $firstDayOfMonth->copy()->addDays(6);

            // ── SHOW METHOD WALI FILTERING ──
            $filteredRoutes = $staffRoute->clientRoute->flatMap(function ($clientRoute) use ($weekStartDate, $weekEndDate, $intervalMap, $checkOccurrence) {

                $weekSchedules = $clientRoute->clientSchedule->filter(function ($clientSchedule) use ($weekStartDate, $weekEndDate) {
                    $scheduleStartDate = \Carbon\Carbon::parse($clientSchedule->start_date);
                    $serviceFrequency  = optional($clientSchedule->clientName)->service_frequency;

                    // Monthly / biMonthly — exact date match
                    if ($serviceFrequency == 'monthly' || $serviceFrequency == 'biMonthly') {
                        for ($d = $weekStartDate->copy(); $d->lte($weekEndDate); $d->addDay()) {
                            if (
                                $d->day   == $scheduleStartDate->day &&
                                $d->month == $scheduleStartDate->month &&
                                $d->year  == $scheduleStartDate->year
                            ) {
                                return true;
                            }
                        }
                        return false;
                    }

                    // Baaki sab: start_date is week mein hai?
                    return $scheduleStartDate->gte($weekStartDate) && $scheduleStartDate->lte($weekEndDate);
                });

                // client_id + start_date se group karo
                $grouped = $weekSchedules->groupBy(function ($clientSchedule) {
                    return $clientSchedule->client_id . '_' . $clientSchedule->start_date;
                });

                // Har group ko merge karke export row banao
                return $grouped->map(function ($schedules) use ($clientRoute, $intervalMap, $checkOccurrence) {
                    $firstSchedule = $schedules->first();

                    // calculateMergedData use karo — show method ki tarah
                    $mergedData    = $this->calculateMergedData($firstSchedule, $clientRoute, $intervalMap, $checkOccurrence);

                    $amount        = $mergedData['amount'];
                    $servicesArray = $mergedData['prices'];
                    $displayNote   = $mergedData['note'];
                    $client = $firstSchedule->clientName;

                    $servicesString = '';
                    if (!empty($servicesArray)) {
                        $servicesString = collect($servicesArray)
                            ->map(fn($s) => ($s['name'] ?? '') . ($s['value'] ? ' ($' . $s['value'] . ')' : ''))
                            ->filter()
//                            ->implode(', ');
                            ->implode("\n");
                    }
                    // Best Time
                    $bestTime = $client?->clientHour
                        ?->map(function ($time) {
                            return trim(($time->start_hour ?? '') . ' - ' . ($time->end_hour ?? ''));
                        })
                        ->filter()
                        ->implode(', ');

                    // Close Days
                    $closeDays = $client?->clientDay
                        ?->pluck('day')
                        ->filter()
                        ->implode(', ');

                    return [
                        'Client Name' => optional($firstSchedule->clientName)->name ?? 'N/A',
                        'Cash'        => ucfirst(strtolower(optional($firstSchedule->clientName)->payment_type ?? 'N/A')),
                        'Amount'      => $amount,
                        'Service'       => $servicesString,
                        'Address'     => trim(
                            (optional($firstSchedule->clientName)->house_no ?? '') . ' ' .
                            (optional($firstSchedule->clientName)->address ?? '')
                        ),
                        'City'        => optional($firstSchedule->clientName)->city ?? 'N/A',
                        'Note'        => $displayNote,
                        'Billed'      => $amount,
                        'position'    => optional($firstSchedule->clientName)->position,
                        'Time'        => $bestTime ?: '',
                        'Close Days'  => $closeDays ?: '',
                    ];
                })->values();
            });

            // Position se sort karo
            $sortedRoutes = $filteredRoutes->sortBy('position')->values();

            // Deposit logic
            $weekKey   = 'week' . $i;
            $monthKey  = strtolower($weekStartDate->format('F'));

            $totalCashReceived = \App\Models\Deposit::where('route_id', $staffRoute->id)
                ->where('week', $weekKey)
                ->where('month', $monthKey)
                ->where('year', $selectedYear)
                ->sum('deposit_amount');

            $data[] = [
                'week_number'           => $i + 1,
                'start_date'            => $weekStartDate->format('M j'),
                'end_date'              => $weekEndDate->format('M j, Y'),
                'cash_received_to_date' => $totalCashReceived,
                'routes'                => $sortedRoutes,
            ];

            $firstDayOfMonth->addDays(7);
        }

        return ['data' => $data];
    }

    private function calculateMergedData($clientSchedule, $clientRoute, $intervalMap, $checkOccurrence)
    {
        // --- Show method style merging ---
        // Merge notes from all schedules in the group
        $mergedNotes = [];
        $mergedMultiPrice = [];
        $mergedInvoiceAmount = 0;

        // Grouping logic: all schedules for this client_id + start_date
        $groupSchedules = $clientRoute->clientSchedule
            ->where('client_id', $clientSchedule->client_id)
            ->where('start_date', $clientSchedule->start_date);

        foreach ($groupSchedules as $sch) {
            // Notes
            if (!empty($sch->note)) {
                $mergedNotes[] = $sch->note;
            }

            // Prices
            if ($sch->clientSchedulePrice && $sch->clientSchedulePrice->count() > 0) {
                foreach ($sch->clientSchedulePrice as $sp) {
                    $val = (float)(optional($sp->clientPaymentPrice)->value ?? 0);
                    $mergedMultiPrice[] = [
                        'name'  => $this->normalizePriceName(optional($sp->clientPaymentPrice)->name),
                        'value' => $val,
                    ];
                    $mergedInvoiceAmount += $val;
                }
            }

            // Extra work prices
            if ($sch->extra_work && $sch->extra_work_price) {
                $names  = json_decode($sch->extra_work, true);
                $values = json_decode($sch->extra_work_price, true);
                if (is_array($names) && is_array($values)) {
                    foreach ($names as $idx => $name) {
                        $val = (float)($values[$idx] ?? 0);
                        $mergedMultiPrice[] = [
                            'name'  => $this->normalizePriceName($name),
                            'value' => $val,
                        ];
                        $mergedInvoiceAmount += $val;
                    }
                }
            }
        }

        $mergedNotes = array_unique($mergedNotes);
        $displayNote = !empty($mergedNotes) ? implode(', ', $mergedNotes) : 'a';

        return [
            'note'   => $displayNote,
            'prices' => $mergedMultiPrice,
            'amount' => $mergedInvoiceAmount,
        ];
    }

    private function normalizePriceName($name)
    {
        if (empty($name)) {
            return $name;
        }

        $patterns = [
            '/interior\s*(&|and)\s*exterior/i' => 'ie',
            '/\binterior\b/i' => 'i',
            '/\bexterior\b/i' => 'e',
        ];

        $result = $name;
        foreach ($patterns as $pattern => $replacement) {
            $result = preg_replace($pattern, $replacement, $result);
        }

        return $result;
    }

    public function toggleStatus($id)
    {
        try {
            $staffRoute = StaffRoute::findOrFail($id);

            // Toggle status (1 to 0, 0 to 1)
            $staffRoute->status = $staffRoute->status == 1 ? 0 : 1;
            $staffRoute->save();

            return response()->json([
                'success' => true,
                'status' => $staffRoute->status,
                'message' => 'Route status updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        $staffroute = StaffRoute::findOrFail($id);
        return view('staffroutes.edit', ['staffroute' => $staffroute]);
    }

    public function update(StaffRouteRequest $request, $id)
    {
        $staffroute = StaffRoute::findOrFail($id);
        $staffroute->name = $request->input('name');
        $staffroute->status = $request->input('status');
        $staffroute->save();

        Notification::create([
            'user_id' => auth()->user()->id,
            'action_id' => $staffroute->id,
            'title' => 'Staff Route Updated',
            'type' => 'staff_route_updated',
            'message' => 'Staff Route "' . $staffroute->name . '" has been updated.',
        ]);

        return to_route('staffroutes.index');
    }

    public function destroy($id)
    {
        $staffroute = StaffRoute::findOrFail($id);
        try {
            DB::beginTransaction();
            AssignRoute::where('route_id', $id)->delete();
            $staffroute->delete();
            Notification::create([
                'user_id' => auth()->user()->id,
                'action_id' => $id,
                'title' => 'Staff Route Deleted',
                'type' => 'staff_route_deleted',
                'message' => 'Staff Route "' . $staffroute->name . '" and all its staff assignments have been deleted.',
            ]);
            DB::commit();
            return redirect()->route('staffroutes.index')->with([
                'title' => 'Done',
                'message' => 'Route and all associated staff assignments deleted successfully',
                'type' => 'success'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with([
                'title' => 'Error',
                'message' => 'Failed to delete route: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }
}
