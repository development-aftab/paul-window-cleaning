<?php

namespace App\Http\Controllers;

use App\Models\{AssignRoute,
    StaffRoute,
    User,
    BlogAttachment,
    Client,
    ClientPayment,
    ClientPriceList,
    ClientSchedule,
    ClientSchedulePrice,
    CmsAbout,
    CmsBlog,
    CmsContact,
    CmsHome,
    CmsService,
    RouteReportReview,
    Contact,
    ContactCleaning,
    ContactImage,
    ContactSiding,
    Notification,
    StaffRequirement,
    Testimonial,
    Deposit,
    Profile,
    Timelog};
use Illuminate\Support\Facades\{Log, Mail, Storage, DB, Http, Auth};
use Carbon\Carbon;
use Dompdf\Dompdf;
use Illuminate\Http\Request;

class WebsiteController extends Controller
{

    public function index()
    {
        $testimonial = Testimonial::where('status', 'accepted')->get();
        $cmsHome = CmsHome::first();
        return view('website.index', ['testimonial' => $testimonial, 'cmsHome' => $cmsHome]);
    }

    public function aboutUs()
    {
        $cmsAbout = CmsAbout::first();
        return view('website.about_us', ['cmsAbout' => $cmsAbout]);
    }

    public function services()
    {
        $cmsService = CmsService::first();
        return view('website.services', compact('cmsService'));
    }

    public function blogs($id = null)
    {
        // return "abc";
        if ($id != null) {
            $blog = CmsBlog::where('id', $id)->first();
            return view('website.blog-detail', compact('blog'));
        } else {
            $cmsBlog = CmsBlog::orderBy('created_at', 'desc')->get();
            return view('website.blogs', compact('cmsBlog'));
        }
    }

    public function contactUs()
    {
        $cmsContact = CmsContact::first();
        return view('website.contact_us', compact('cmsContact'));
    }

    public function routes()
    {
        return view('dashboard.routes');
    }

    public function clientManagement()
    {
        return view('dashboard.client_management');
    }

    public function createClient()
    {
        return view('dashboard.create_client');
    }

    public function dashboardIndex()
    {
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

        $today = Carbon::now();
        $currentWeek = $weeks->first(function ($week) use ($today) {
            return $today->between($week['start_date'], $week['end_date']);
        });

        if (!$currentWeek) {
            $currentWeek = $weeks->first();
        }

        $currentWeekStart = $currentWeek['start_date'];
        $currentWeekEnd = $currentWeek['end_date'];
        $myAssignRoutes = AssignRoute::where('staff_id', Auth::id())->pluck('route_id')->toArray();

        $staffRoute = StaffRoute::where('status', 1)
            ->when(Auth::user()->hasRole('staff'), function ($query) use ($myAssignRoutes) {
                return $query->whereIn('id', $myAssignRoutes);
            })
            ->with(['clientRoute.clientSchedule' => function ($query) use ($currentWeekStart, $currentWeekEnd) {
                $query->where('start_date', '<=', $currentWeekEnd->format('Y-m-d'))
                    ->where('end_date', '>=', $currentWeekStart->format('Y-m-d'))
                    ->select('id', 'client_id', 'start_date', 'end_date', 'status');
            }])
            ->get()->map(function ($route) {
                $weekSchedules = $route->clientRoute->flatMap(function ($clientRoute) {
                    return $clientRoute->clientSchedule;
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

        $weekNumber = $currentWeek['week_number'];
        $startOfWeek = $currentWeekStart->format('d');
        $endOfWeek = $currentWeekEnd->format('d');

        if ($currentWeekStart->format('F') === $currentWeekEnd->format('F')) {
            $currentMonthName = $currentWeekStart->format('F'); // e.g. "July"
        } else {
            $currentMonthName = $currentWeekStart->format('F') . ' - ' . $currentWeekEnd->format('F'); // e.g. "June - July"
        }

        $notifications = Notification::where('user_id', Auth::id())->get();
        if (auth()->user()->hasRole('admin')) {
            $MyPotentialClients = Client::where('status', 0)->whereNotNull('staff_id')->get();
        } else {
            $MyPotentialClients = Client::where('status', 0)->where('staff_id', auth()->user()->id)->get();
        }

        $deposits = collect([]);
        if (auth()->user()->hasRole('staff')) {
            $deposits = Deposit::with(['route'])
                ->where('staff_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        }
        $last45DaysInvoices = ClientPayment::with('client')
            ->where('payment_type', 'invoice')
            ->where('created_at', '>=', now()->subDays(45))
            ->get();

        $startDate = Carbon::now();
        $endDate = Carbon::now()->addWeeks(2);
        $prioritySchedules = ClientSchedule::with(['clientName.clientRoute'])
            ->where('priority', 1)
            ->whereBetween('start_date', [$startDate, $endDate])
            ->orderBy('start_date', 'asc')
            ->get();

        $totalUnPaids = 0;
        if (auth()->user()->hasRole('staff')) {
            $totalUnPaids = ClientSchedule::with(['clientSchedulePayment', 'clientName.clientRouteStaff.route', 'StaffName'])
                ->where('status', 'completed')
                ->where('staff_id', Auth::id())
                ->where(function($q) {
                    $q->whereHas('clientSchedulePayment', function ($sub) {
                        $sub->where('status', '!=', 'paid');
                    })->orWhereDoesntHave('clientSchedulePayment');
                })
                ->count();
        }

        return view('dashboard.dashboard_index', [
            'staffRoute' => $staffRoute,
            'currentMonth' => $currentMonthName,
            'currentYear' => $currentYear,
            'weekNumber' => $weekNumber,
            'startOfWeek' => $startOfWeek,
            'endOfWeek' => $endOfWeek,
            'notifications' => $notifications,
            'MyPotentialClients' => $MyPotentialClients,
            'deposits' => $deposits,
            'last45DaysInvoices' => $last45DaysInvoices,
            'prioritySchedules' => $prioritySchedules,
        ]);
    }

    public function notification()
    {
        $userId = Auth::id();

        Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->update([
                'is_read' => 1,
                'read_at' => now(),
            ]);

        $notificationsss = Notification::with('user')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('dashboard.notification', compact('notificationsss'));
    }

    /**
     * Route IDs a non-admin (staff) user is allowed to see in the route report.
     * Returns null for admins, meaning "no restriction".
     */
    private function routeReportAllowedRouteIds()
    {
        $user = auth()->user();

        if (!$user || $user->hasRole('admin')) {
            return null;
        }

        return AssignRoute::where('staff_id', $user->id)->pluck('route_id')->toArray();
    }

    public function routeReport(Request $request)
    {
        $allowedRouteIds = $this->routeReportAllowedRouteIds();

        $routesQuery = StaffRoute::where('status', 1);
        if ($allowedRouteIds !== null) {
            $routesQuery->whereIn('id', $allowedRouteIds);
        }
        $routes = $routesQuery->get();

        $staffs = $allowedRouteIds === null ? User::role('staff')->whereDoesntHave('profile', function ($q) {
            $q->where('employment_type', 'subcontractor');
        })->get() : collect([auth()->user()]);

        // Get filter values
        $selectedRouteId = $request->input('route');
        $selectedStaffId = $request->input('staff');

        if ($allowedRouteIds !== null) {
            if ($selectedRouteId && !in_array($selectedRouteId, $allowedRouteIds)) {
                // Ignore attempts to view a route not assigned to this staff member
                $selectedRouteId = null;
            }
            if ($selectedStaffId && $selectedStaffId != auth()->id()) {
                // Staff can only filter down to themselves, not other staff
                $selectedStaffId = null;
            }
        }

        $currentYear = now()->year;
        $currentDate = now();

        $firstMondayOfCurrentYear = Carbon::parse("first Monday of January $currentYear");
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

        $customStartDates = [
            "January - February" => Carbon::parse("first Monday of January $selectedYear")->copy()->addDays(0),
            "February - March" => Carbon::parse("first Monday of January $selectedYear")->copy()->addWeeks(4),
            "March" => Carbon::parse("first Monday of January $selectedYear")->copy()->addWeeks(8),
            "March - April" => Carbon::parse("first Monday of January $selectedYear")->copy()->addWeeks(12),
            "April - May" => Carbon::parse("first Monday of January $selectedYear")->copy()->addWeeks(16),
            "May - June" => Carbon::parse("first Monday of January $selectedYear")->copy()->addWeeks(20),
            "June - July" => Carbon::parse("first Monday of January $selectedYear")->copy()->addWeeks(24),
            "July - August" => Carbon::parse("first Monday of January $selectedYear")->copy()->addWeeks(28),
            "August - September" => Carbon::parse("first Monday of January $selectedYear")->copy()->addWeeks(32),
            "September - October" => Carbon::parse("first Monday of January $selectedYear")->copy()->addWeeks(36),
            "October - November" => Carbon::parse("first Monday of January $selectedYear")->copy()->addWeeks(40),
            "November - December" => Carbon::parse("first Monday of January $selectedYear")->copy()->addWeeks(44),
            "December - January" => Carbon::parse("first Monday of January $selectedYear")->copy()->addWeeks(48),
        ];

        $baseMonthName = trim(str_replace($selectedYear, '', $selectedMonth));
        if (!array_key_exists($baseMonthName, $customStartDates)) {
            $baseMonthName = "January - February";
        }

        $monthStartDate = $customStartDates[$baseMonthName];
        $monthEndDate = $monthStartDate->copy()->addWeeks(4)->subDay();

        $weeks = collect();
        $cycleStart = $monthStartDate->copy();
        $weekIndex = 1;
        while ($weekIndex <= 4) {
            $weekStart = $cycleStart->copy();
            $weekEnd = $cycleStart->copy()->addDays(6);
            $weeks->push([
                'week_number' => $weekIndex,
                'start_date' => $weekStart->copy(),
                'end_date' => $weekEnd->copy(),
            ]);
            $cycleStart->addDays(7);
            $weekIndex++;
        }

        $query = ClientSchedule::where('status', 'completed')
            ->with([
                'clientSchedulePayment',
                'clientName.clientRouteStaff.route',
                'StaffName'
            ])
            ->whereBetween('start_date', [$monthStartDate->format('Y-m-d'), $monthEndDate->format('Y-m-d')]);

        if ($selectedRouteId) {
            $query->whereHas('clientName.clientRouteStaff', function ($q) use ($selectedRouteId) {
                $q->where('route_id', $selectedRouteId);
            });
        } elseif ($allowedRouteIds !== null) {
            $query->whereHas('clientName.clientRouteStaff', function ($q) use ($allowedRouteIds) {
                $q->whereIn('route_id', $allowedRouteIds);
            });
        }
        if ($selectedStaffId) {
            $query->where('staff_id', $selectedStaffId);
        }

        $query->whereDoesntHave('StaffName.profile', function ($q) {
            $q->where('employment_type', 'subcontractor');
        });
        $dbData = $query->get();

        $allDeposits = Deposit::all();
        $allClientPayments = ClientPayment::all();
        $allTimelogs = Timelog::all();

        // Pre-fetch StaffLogHour records for this month's date range
        $allStaffLogHours = \App\Models\StaffLogHour::with('staff')->whereBetween('week_start_date', [
            $monthStartDate->format('Y-m-d'),
            $monthEndDate->format('Y-m-d'),
        ])->get();

        $allRouteReportReviews = RouteReportReview::where('year', $selectedYear)->where('month', $baseMonthName)->get();

        $cycleData = collect();
        foreach ($weeks as $week) {
            $weekLabel = 'Week ' . $week['week_number'] . ' | ' . $week['start_date']->format('d F') . ' - ' . $week['end_date']->format('d F');
            $cycleData[$weekLabel] = collect();
        }
        foreach ($dbData as $item) {
            foreach ($weeks as $week) {
                if (Carbon::parse($item->start_date)->between($week['start_date'], $week['end_date'])) {
                    $weekLabel = 'Week ' . $week['week_number'] . ' | ' . $week['start_date']->format('d F') . ' - ' . $week['end_date']->format('d F');
                    $cycleData[$weekLabel]->push($item);
                    break;
                }
            }
        }
        $data = $cycleData->slice(0, 4)->map(function ($weekItems) {
            if ($weekItems->count() > 0) {
                return $weekItems->groupBy(function ($item) {
                    return $item->clientName?->clientRouteStaff->first()->route->id ?? 0;
                });
            } else {
                return collect();
            }
        });

        $monthNames = array_keys($customStartDates);
        $months = [];
        foreach ($monthNames as $monthName) {
            $months[] = $monthName . ' ' . $selectedYear;
        }

        $currentIndex = array_search($baseMonthName, $monthNames);
        if ($currentIndex === false) {
            $currentIndex = 0;
        }
        if ($currentIndex > 0) {
            $previousMonthName = $monthNames[$currentIndex - 1];
            $previousMonth = $previousMonthName . ' ' . $selectedYear;
        } else {
            $previousMonthName = $monthNames[count($monthNames) - 1];
            $previousMonth = $previousMonthName . ' ' . ($selectedYear - 1);
        }
        if ($currentIndex < count($monthNames) - 1) {
            $nextMonthName = $monthNames[$currentIndex + 1];
            $nextMonth = $nextMonthName . ' ' . $selectedYear;
        } else {
            $nextMonthName = $monthNames[0];
            $nextMonth = $nextMonthName . ' ' . ($selectedYear + 1);
        }
        return view('dashboard.route_report', compact('routes', 'staffs', 'data', 'allDeposits', 'allClientPayments', 'allTimelogs', 'allStaffLogHours', 'allRouteReportReviews', 'weeks', 'months', 'selectedMonth', 'previousMonth', 'nextMonth', 'selectedRouteId', 'selectedStaffId'));
    }

    public function routeReportAjax(Request $request)
    {
        $allowedRouteIds = $this->routeReportAllowedRouteIds();

        $selectedRouteId = $request->input('route');
        $selectedStaffId = $request->input('staff');
        $selectedMonth = $request->input('month');

        if ($allowedRouteIds !== null) {
            if ($selectedRouteId && !in_array($selectedRouteId, $allowedRouteIds)) {
                // Ignore attempts to view a route not assigned to this staff member
                $selectedRouteId = null;
            }
            if ($selectedStaffId && $selectedStaffId != auth()->id()) {
                // Staff can only filter down to themselves, not other staff
                $selectedStaffId = null;
            }
        }

        $currentYear = now()->year;
        preg_match('/\d{4}/', $selectedMonth, $yearMatch);
        $selectedYear = $yearMatch[0] ?? $currentYear;

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

        $baseMonthName = trim(str_replace($selectedYear, '', $selectedMonth));
        if (!array_key_exists($baseMonthName, $customStartDates)) {
            $baseMonthName = "January - February";
        }

        $monthStartDate = $customStartDates[$baseMonthName];
        $monthEndDate = $monthStartDate->copy()->addWeeks(4)->subDay();



        $weeks = collect();
        $cycleStart = $monthStartDate->copy();
        $weekIndex = 1;
        while ($weekIndex <= 4) {
            $weekStart = $cycleStart->copy();
            $weekEnd = $cycleStart->copy()->addDays(6);
            $weeks->push([
                'week_number' => $weekIndex,
                'start_date' => $weekStart->copy(),
                'end_date' => $weekEnd->copy(),
            ]);
            $cycleStart->addDays(7);
            $weekIndex++;
        }

        $query = ClientSchedule::where('status', 'completed')
            ->with([
                'clientSchedulePayment',
                'clientName.clientRouteStaff.route',
                'StaffName'
            ])
            ->whereBetween('start_date', [$monthStartDate->format('Y-m-d'), $monthEndDate->format('Y-m-d')]);

        if ($selectedRouteId) {
            $query->whereHas('clientName.clientRouteStaff', function ($q) use ($selectedRouteId) {
                $q->where('route_id', $selectedRouteId);
            });
        } elseif ($allowedRouteIds !== null) {
            $query->whereHas('clientName.clientRouteStaff', function ($q) use ($allowedRouteIds) {
                $q->whereIn('route_id', $allowedRouteIds);
            });
        }

        if ($selectedStaffId) {
            $query->where('staff_id', $selectedStaffId);
        }

        $query->whereDoesntHave('StaffName.profile', function ($q) {
            $q->where('employment_type', 'subcontractor');
        });

        $dbData = $query->get();

        $allDeposits = Deposit::all();
        $allClientPayments = ClientPayment::all();
        $allTimelogs = Timelog::all();

        // Pre-fetch StaffLogHour records for this month's date range
        $allStaffLogHours = \App\Models\StaffLogHour::with('staff')->whereBetween('week_start_date', [
            $monthStartDate->format('Y-m-d'),
            $monthEndDate->format('Y-m-d'),
        ])->get();

        $cycleData = collect();
        foreach ($weeks as $week) {
            $weekLabel = 'Week ' . $week['week_number'] . ' | ' . $week['start_date']->format('d F') . ' - ' . $week['end_date']->format('d F');
            $cycleData[$weekLabel] = collect();
        }
        foreach ($dbData as $item) {
            foreach ($weeks as $week) {
                if (Carbon::parse($item->start_date)->between($week['start_date'], $week['end_date'])) {
                    $weekLabel = 'Week ' . $week['week_number'] . ' | ' . $week['start_date']->format('d F') . ' - ' . $week['end_date']->format('d F');
                    $cycleData[$weekLabel]->push($item);
                    break;
                }
            }
        }
        $data = $cycleData->slice(0, 4)->map(function ($weekItems) {
            if ($weekItems->count() > 0) {
                return $weekItems->groupBy(function ($item) {
                    return $item->clientName?->clientRouteStaff->first()->route->id ?? 0;
                });
            } else {
                return collect();
            }
        });

        $monthNames = array_keys($customStartDates);
        $currentIndex = array_search($baseMonthName, $monthNames);

        if ($currentIndex === false) {
            $currentIndex = 0;
        }

        if ($currentIndex > 0) {
            $previousMonthName = $monthNames[$currentIndex - 1];
            $previousMonth = $previousMonthName . ' ' . $selectedYear;
        } else {
            $previousMonthName = $monthNames[count($monthNames) - 1];
            $previousMonth = $previousMonthName . ' ' . ($selectedYear - 1);
        }
        if ($currentIndex < count($monthNames) - 1) {
            $nextMonthName = $monthNames[$currentIndex + 1];
            $nextMonth = $nextMonthName . ' ' . $selectedYear;
        } else {
            $nextMonthName = $monthNames[0];
            $nextMonth = $nextMonthName . ' ' . ($selectedYear + 1);
        }

        $allRouteReportReviews = \App\Models\RouteReportReview::all();

        $cycleData = collect();
        foreach ($weeks as $week) {
            $weekLabel = 'Week ' . $week['week_number'] . ' | ' . $week['start_date']->format('d F') . ' - ' . $week['end_date']->format('d F');
            $cycleData[$weekLabel] = collect();
        }
        foreach ($dbData as $item) {
            foreach ($weeks as $week) {
                if (Carbon::parse($item->start_date)->between($week['start_date'], $week['end_date'])) {
                    $weekLabel = 'Week ' . $week['week_number'] . ' | ' . $week['start_date']->format('d F') . ' - ' . $week['end_date']->format('d F');
                    $cycleData[$weekLabel]->push($item);
                    break;
                }
            }
        }
        $data = $cycleData->slice(0, 4)->map(function ($weekItems) {
            if ($weekItems->count() > 0) {
                return $weekItems->groupBy(function ($item) {
                    return $item->clientName?->clientRouteStaff->first()->route->id ?? 0;
                });
            } else {
                return collect();
            }
        });

        $monthNames = array_keys($customStartDates);
        $currentIndex = array_search($baseMonthName, $monthNames);

        if ($currentIndex === false) {
            $currentIndex = 0;
        }

        if ($currentIndex > 0) {
            $previousMonthName = $monthNames[$currentIndex - 1];
            $previousMonth = $previousMonthName . ' ' . $selectedYear;
        } else {
            $previousMonthName = $monthNames[count($monthNames) - 1];
            $previousMonth = $previousMonthName . ' ' . ($selectedYear - 1);
        }
        if ($currentIndex < count($monthNames) - 1) {
            $nextMonthName = $monthNames[$currentIndex + 1];
            $nextMonth = $nextMonthName . ' ' . $selectedYear;
        } else {
            $nextMonthName = $monthNames[0];
            $nextMonth = $nextMonthName . ' ' . ($selectedYear + 1);
        }

        $months = [];
        foreach ($monthNames as $monthName) {
            $months[] = $monthName . ' ' . $selectedYear;
        }

        $html = view('dashboard.partials.route_report_table', compact('data', 'allDeposits', 'allClientPayments', 'allTimelogs', 'allStaffLogHours', 'allRouteReportReviews', 'weeks', 'selectedMonth'))->render();

        return response()->json([
            'html' => $html,
            'selectedMonth' => $selectedMonth,
            'previousMonth' => $previousMonth,
            'nextMonth' => $nextMonth,
            'months' => $months,
            'selectedYear' => $selectedYear
        ]);
    }
    public function toggleRouteReportReview(Request $request)
    {
        $request->validate([
            'week' => 'required|string',
            'month' => 'required|string',
            'year' => 'required|string',
            'is_reviewed' => 'required|boolean',
        ]);

        $review = RouteReportReview::updateOrCreate(
            [
                'week' => $request->week,
                'month' => $request->month,
                'year' => $request->year,
            ],
            [
                'is_reviewed' => $request->is_reviewed,
                'reviewed_by' => auth()->id(),
            ]
        );

        return response()->json(['success' => true, 'review' => $review]);
    }

    public function routeReportExport(Request $request)
    {
        $allowedRouteIds = $this->routeReportAllowedRouteIds();

        $selectedRouteId = $request->input('route');
        $selectedStaffId = $request->input('staff');
        $selectedMonth = $request->input('month');
        $weekNum = $request->input('week'); // null for all weeks, or specific week number
        $exportType = $request->input('type', 'single'); // 'single' or 'all'

        if ($allowedRouteIds !== null) {
            if ($selectedRouteId && !in_array($selectedRouteId, $allowedRouteIds)) {
                // Ignore attempts to export a route not assigned to this staff member
                $selectedRouteId = null;
            }
            if ($selectedStaffId && $selectedStaffId != auth()->id()) {
                // Staff can only filter down to themselves, not other staff
                $selectedStaffId = null;
            }
        }

        \Log::info('========== ROUTE REPORT EXPORT START ==========');
        \Log::info('Request Parameters:', $request->all());
        \Log::info('Week Number Received: ' . $weekNum);
        \Log::info('Export Type: ' . $exportType);

        $currentYear = now()->year;
        preg_match('/\d{4}/', $selectedMonth, $yearMatch);
        $selectedYear = $yearMatch[0] ?? $currentYear;

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

        $baseMonthName = trim(str_replace($selectedYear, '', $selectedMonth));
        if (!array_key_exists($baseMonthName, $customStartDates)) {
            $baseMonthName = "January - February";
        }

        $monthStartDate = $customStartDates[$baseMonthName];
        $monthEndDate = $monthStartDate->copy()->addWeeks(4)->subDay();

        // Build weeks array
        $weeks = collect();
        $cycleStart = $monthStartDate->copy();
        $weekIndex = 1;
        while ($weekIndex <= 4) {
            $weekStart = $cycleStart->copy();
            $weekEnd = $cycleStart->copy()->addDays(6);
            $weeks->push([
                'week_number' => $weekIndex,
                'start_date' => $weekStart->copy(),
                'end_date' => $weekEnd->copy(),
            ]);
            $cycleStart->addDays(7);
            $weekIndex++;
        }

        // Fetch data
        $query = ClientSchedule::where('status', 'completed')
            ->with([
                'clientSchedulePrice.clientPaymentPrice',
                'clientSchedulePayment',
                'clientName.clientRouteStaff.route',
                'StaffName'
            ])
            ->whereBetween('start_date', [$monthStartDate->format('Y-m-d'), $monthEndDate->format('Y-m-d')]);

        if ($selectedRouteId) {
            $query->whereHas('clientName.clientRouteStaff', function ($q) use ($selectedRouteId) {
                $q->where('route_id', $selectedRouteId);
            });
        } elseif ($allowedRouteIds !== null) {
            $query->whereHas('clientName.clientRouteStaff', function ($q) use ($allowedRouteIds) {
                $q->whereIn('route_id', $allowedRouteIds);
            });
        }

        if ($selectedStaffId) {
            $query->where('staff_id', $selectedStaffId);
        }

        $query->whereDoesntHave('StaffName.profile', function ($q) {
            $q->where('employment_type', 'subcontractor');
        });

        $dbData = $query->get();

        $allDeposits = Deposit::all();
        $allTimelogs = Timelog::all();

        // Pre-fetch StaffLogHour records for this month's date range
        $allStaffLogHours = \App\Models\StaffLogHour::with('staff')->whereBetween('week_start_date', [
            $monthStartDate->format('Y-m-d'),
            $monthEndDate->format('Y-m-d'),
        ])->get();

        // Group data by week
        $cycleData = collect();
        foreach ($weeks as $week) {
            $weekLabel = 'Week ' . $week['week_number'] . ' | ' . $week['start_date']->format('d F') . ' - ' . $week['end_date']->format('d F');
            $cycleData[$weekLabel] = collect();
        }
        foreach ($dbData as $item) {
            foreach ($weeks as $week) {
                if (Carbon::parse($item->start_date)->between($week['start_date'], $week['end_date'])) {
                    $weekLabel = 'Week ' . $week['week_number'] . ' | ' . $week['start_date']->format('d F') . ' - ' . $week['end_date']->format('d F');
                    $cycleData[$weekLabel]->push($item);
                    break;
                }
            }
        }

        // If exporting a single week, filter to only that week's data
        if ($exportType === 'single' && $weekNum) {
            // Find the specific week label
            $targetWeekLabel = null;

            \Log::info('Single Week Export - Week Number Received: ' . $weekNum);
            \Log::info('Available weeks: ' . implode(', ', $cycleData->keys()->toArray()));

            foreach ($cycleData->keys() as $label) {
                // Match "Week 1 |" or "Week 1 " or "Week 1-"
                if (preg_match('/^Week\s+' . $weekNum . '(\s|\||$)/', $label)) {
                    $targetWeekLabel = $label;
                    \Log::info('MATCHED Week Label: ' . $targetWeekLabel);
                    break;
                }
            }

            // Filter to only the target week
            if ($targetWeekLabel && isset($cycleData[$targetWeekLabel])) {
                $weekItems = $cycleData[$targetWeekLabel];
                if ($weekItems->count() > 0) {
                    $data = collect([
                        $targetWeekLabel => $weekItems->groupBy(function ($item) {
                            return $item->clientName?->clientRouteStaff->first()->route->id ?? 0;
                        })
                    ]);
                } else {
                    $data = collect([$targetWeekLabel => collect()]);
                }
            } else {
                // Week not found, create empty data
                $data = collect(["Week $weekNum" => collect()]);
            }
        } else {
            // Export all weeks
            $data = $cycleData->slice(0, 4)->map(function ($weekItems) {
                if ($weekItems->count() > 0) {
                    return $weekItems->groupBy(function ($item) {
                        return $item->clientName?->clientRouteStaff->first()->route->id ?? 0;
                    });
                } else {
                    return collect();
                }
            });
        }

        // Generate Excel
        return $this->generateExcelReport($data, $allDeposits, $allTimelogs, $allStaffLogHours, $weeks, $selectedMonth, $selectedYear, $weekNum, $exportType, $baseMonthName);
    }


    public function routeDetailsPdf(Request $request)
    {
        $staffRoute = StaffRoute::with('clientRoute.clientSchedule.clientSchedulePrice.clientPaymentPrice')->findOrFail($request->id);
        $routeName = $staffRoute->name;
        $currentYear = now()->year;
        //        $selectedMonth = $request->input('month', "January $currentYear");
        $selectedMonth = $request->selected_month;

        preg_match('/\d{4}/', $selectedMonth, $yearMatch);
        $selectedYear = $yearMatch[0] ?? $currentYear;

        $allMonths = collect([
            '1' => 'January',
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
            "January" => $firstMondayOfYear->copy()->addDays(0),
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

        $baseMonthName = trim(str_replace($selectedYear, '', $selectedMonth));

        if (!array_key_exists($baseMonthName, $customStartDates)) {
            $baseMonthName = "January";
            $selectedYear = $currentYear;
        }

        $firstDayOfMonth = $customStartDates[$baseMonthName];

        $weeks = collect();

        for ($i = 0; $i < 4; $i++) {
            $endOfWeek = $firstDayOfMonth->copy()->addDays(6);

            $weeks->push([
                'week_number' => $i + 1,
                'start_date' => $firstDayOfMonth->format('d F Y'),
                'end_date' => $endOfWeek->format('d F Y'),
                'routes' => [],
            ]);

            $firstDayOfMonth->addDays(7);
        }

        $mergedSchedules = $weeks->map(function ($week) use ($staffRoute) {
            $weekStartDate = Carbon::parse($week['start_date']);
            $weekEndDate = Carbon::parse($week['end_date']);

            $filteredRoutes = $staffRoute->clientRoute->flatMap(function ($clientRoute) use ($weekStartDate, $weekEndDate) {
                return $clientRoute->clientSchedule->filter(function ($clientSchedule) use ($weekStartDate, $weekEndDate) {
                    $scheduleStartDate = Carbon::parse($clientSchedule->start_date);
                    $scheduleEndDate = Carbon::parse($clientSchedule->end_date);

                    return ($scheduleStartDate->gte($weekStartDate) && $scheduleStartDate->lte($weekEndDate)) ||
                        ($scheduleEndDate->gte($weekStartDate) && $scheduleEndDate->lte($weekEndDate)) ||
                        ($scheduleStartDate->lte($weekStartDate) && $scheduleEndDate->gte($weekEndDate));
                })->map(function ($clientSchedule) {
                    return [
                        'client_hours' => optional($clientSchedule->clientHour)
                            ->map(fn($scheduleHour) => [
                                'start_hour' => optional($scheduleHour)->start_hour,
                                'end_hour' => optional($scheduleHour)->end_hour,
                            ]),
                        'client_start_week' => optional($clientSchedule)->start_date,
                        'client_end_week' => optional($clientSchedule)->end_date,
                        'client_id' => optional($clientSchedule->clientName)->id,
                        'client_name' => optional(optional(optional($clientSchedule)->clientName)->user)->name ?? null,
                        'client_job' => optional(optional($clientSchedule)->clientName)->description ?? null,
                        'payment_type' => $clientSchedule->payment_type,
                        'invoice_amount' => optional($clientSchedule->clientSchedulePrice)
                            ->map(fn($schedulePrice) => optional($schedulePrice->clientPaymentPrice)->value)
                            ->sum(),
                        'multiPrice' => optional($clientSchedule->clientSchedulePrice)
                            ->map(fn($schedulePrice) => [
                                'name' => optional($schedulePrice->clientPaymentPrice)->name,
                                'value' => optional($schedulePrice->clientPaymentPrice)->value
                            ]),
                        'address' => optional(optional(optional(optional($clientSchedule)->clientName)->user)->profile)->address ?? null,
                        'note' => $clientSchedule->note,
                        'is_completed' => $clientSchedule->status,
                    ];
                });
            });

            $week['routes'] = $filteredRoutes;
            return $week;
        });

        $dompdf = new Dompdf();
        $view = view('dashboard.route-details-pdf', compact('staffRoute', 'months', 'selectedMonth', 'mergedSchedules'));
        $dompdf->loadHtml($view->render());
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream($routeName . '_' . $selectedMonth . '.pdf');
    }

    public function profileSettings()
    {
        return view('dashboard.profile_settings');
    }

    public function staffManagement()
    {
        return view('dashboard.admin.staff_management');
    }

    public function staffRequest()
    {

        $staffRequest = StaffRequirement::with('staffRequirement.profile')
            ->get()
            ->groupBy(function ($item) {
                return $item->staff_id . '-' . $item->timestamp;
            });
        return view('dashboard.admin.staff-request', ['staffRequest' => $staffRequest]);
    }

    public function createStaffMember()
    {
        return view('dashboard.create_staff_member');
    }

    public function createStaffMemberTwo()
    {
        return view('dashboard.create_staff_member_two');
    }

    public function newRoute()
    {
        return view('dashboard.admin.new_route');
    }

    public function cms()
    {
        $cmsHome = CmsHome::first();
        $cmsAbout = CmsAbout::first();
        $cmsService = CmsService::first();
        $cmsContact = CmsContact::first();
        $cmsBlog = CmsBlog::orderBy('created_at', 'asc')->get();
        return view('dashboard.admin.cms', compact('cmsHome', 'cmsAbout', 'cmsService', 'cmsContact', 'cmsBlog'));
    }

    public function clientDetails()
    {
        return view('dashboard.client-details');
    }

    public function clientInvoice(Request $request, $id)
    {
        $client = Client::with(['clientSchedule.clientSchedulePrice.clientPaymentPrice' ,
            'clientPrice' => function ($query) {
                $query->orderBy('position', 'asc');
            }])->findOrFail($id);

        $start_week_date = $request->query('start_date');
        $end_week_date = $request->query('end_date');
        $clientSchedule = ClientSchedule::with('clientSchedulePrice.clientPaymentPrice')
            ->where('client_id', $client->id)
            ->where('start_date', $start_week_date)
            ->where('end_date', $end_week_date)
            ->first();

        if (!$clientSchedule) {
            return redirect()->back();
        }
        $clientPriceSum = $this->calculateTotalSum($clientSchedule);
        $multiPrices = $this->getMultiPriceWithExtra($clientSchedule);
        return view('dashboard.client_invoice', compact('client', 'clientPriceSum', 'clientSchedule', 'multiPrices'));
    }

    public function viewClientInvoice(Request $request, $id)
    {
        $client = Client::with('clientSchedule.clientSchedulePrice.clientPaymentPrice')->findOrFail($id);
        $start_week_date = $request->query('start_date');
        $end_week_date = $request->query('end_date');

        $clientSchedule = ClientSchedule::with(['clientSchedulePayment', 'clientSchedulePrice.clientPaymentPrice'])
            ->where('client_id', $client->id)
            ->where('start_date', $start_week_date)
            ->where('end_date', $end_week_date)
            ->first();

        if (!$clientSchedule || $clientSchedule->clientSchedulePayment === null) {
            return redirect()->back();
        }

        $clientPriceSum = $this->calculateTotalSum($clientSchedule);
        $multiPrices = $this->getMultiPriceWithExtra($clientSchedule);

        return view('dashboard.view_client_invoice', compact('client', 'clientPriceSum', 'clientSchedule', 'multiPrices'));
    }

    public function clientCash(Request $request, $id)
    {
        $client = Client::with('clientSchedule.clientSchedulePrice.clientPaymentPrice')->findOrFail($id);
        $start_week_date = $request->query('start_date');
        $end_week_date = $request->query('end_date');

        $clientSchedule = ClientSchedule::with('clientSchedulePrice.clientPaymentPrice')
            ->where('client_id', $client->id)
            ->where('start_date', $start_week_date)
            ->where('end_date', $end_week_date)
            ->first();

        if (!$clientSchedule) {
            return redirect()->back();
        }

        $clientPriceSum = $this->calculateTotalSum($clientSchedule);
        $multiPrices = $this->getMultiPriceWithExtra($clientSchedule);

        // return ['clientPriceSum' => $clientPriceSum, 'multiPrices' => $multiPrices];
        return view('dashboard.client_cash', compact('client', 'clientPriceSum', 'clientSchedule', 'multiPrices'));
    }

    public function viewClientCash(Request $request, $id)
    {
        $client = Client::with('clientSchedule.clientSchedulePrice.clientPaymentPrice')->findOrFail($id);
        $start_week_date = $request->query('start_date');
        $end_week_date = $request->query('end_date');

        $clientSchedule = ClientSchedule::with(['clientSchedulePayment', 'clientSchedulePrice.clientPaymentPrice'])
            ->where('client_id', $client->id)
            ->where('start_date', $start_week_date)
            ->where('end_date', $end_week_date)
            ->first();

        if (!$clientSchedule || $clientSchedule->clientSchedulePayment === null) {
            return redirect()->back();
        }

        $clientPriceSum = $this->calculateTotalSum($clientSchedule);
        $multiPrices = $this->getMultiPriceWithExtra($clientSchedule);
        return view('dashboard.view_client_cash', compact('client', 'clientPriceSum', 'clientSchedule', 'multiPrices'));
    }

    private function calculateTotalSum($clientSchedule)
    {
        $clientSchedulePriceIds = \App\Models\ClientSchedulePrice::where('schedule_id', $clientSchedule->id)
            ->pluck('price_id')
            ->toArray();

        $basePriceSum = 0;

        if (count($clientSchedulePriceIds) > 0) {
            $basePriceSum = (float) \App\Models\ClientPriceList::whereIn('id', $clientSchedulePriceIds)->sum('value');
        } else {
            $note1Schedule = \App\Models\ClientSchedule::where('client_id', $clientSchedule->client_id)
                ->where('week', $clientSchedule->week)
                ->where('week_month', $clientSchedule->week_month)
                ->whereHas('clientSchedulePrice') // Jis mein pivot table entries hon
                ->first();

            if ($note1Schedule) {
                $note1PriceIds = \App\Models\ClientSchedulePrice::where('schedule_id', $note1Schedule->id)
                    ->pluck('price_id')
                    ->toArray();
                $basePriceSum = (float) \App\Models\ClientPriceList::whereIn('id', $note1PriceIds)->sum('value');
            }
        }

        $extraWorkSum = 0;
        if (!empty($clientSchedule->extra_work_price)) {
            $decodedPrices = json_decode($clientSchedule->extra_work_price, true);
            if (is_array($decodedPrices)) {
                $extraWorkSum = array_sum(array_map('floatval', $decodedPrices));
            } else {
                $extraWorkSum = (float) $clientSchedule->extra_work_price;
            }
        }

        return $basePriceSum + $extraWorkSum;
    }

    private function getMultiPriceWithExtra($clientSchedule)
    {
        $basePrices = [];

        $currentPrices = $clientSchedule->clientSchedulePrice;

        if ($currentPrices && $currentPrices->count() > 0) {
            foreach ($currentPrices as $sp) {
                $basePrices[] = [
                    'name' => optional($sp->clientPaymentPrice)->name,
                    'value' => (float) optional($sp->clientPaymentPrice)->value
                ];
            }
        } else {
            $note1Schedule = \App\Models\ClientSchedule::where('client_id', $clientSchedule->client_id)
                ->where('week', $clientSchedule->week)
                ->where('week_month', $clientSchedule->week_month)
                ->whereHas('clientSchedulePrice')
                ->with('clientSchedulePrice.clientPaymentPrice')
                ->first();

            if ($note1Schedule) {
                foreach ($note1Schedule->clientSchedulePrice as $sp) {
                    $basePrices[] = [
                        'name' => optional($sp->clientPaymentPrice)->name,
                        'value' => (float) optional($sp->clientPaymentPrice)->value
                    ];
                }
            }
        }

        $extraPrices = [];
        if (!empty($clientSchedule->extra_work) && !empty($clientSchedule->extra_work_price)) {
            $names = json_decode($clientSchedule->extra_work, true);
            $values = json_decode($clientSchedule->extra_work_price, true);

            if (is_array($names) && is_array($values)) {
                foreach ($names as $index => $name) {
                    $extraPrices[] = [
                        'name' => $name,
                        'value' => (float) ($values[$index] ?? 0)
                    ];
                }
            }
        }

        return array_merge($basePrices, $extraPrices);
    }

    public function quote()
    {
        return view('dashboard.quote');
    }

    public function quoteDetails()
    {
        return view('dashboard.quote-details');
    }

    public function staffTestimonials()
    {
        return view('dashboard.admin.staff-testimonials');
    }

    public function routeDetails()
    {
        return view('dashboard.route-details');
    }

    public function invoiceTemplateOne()
    {
        return view('dashboard.invoice-template-one');
    }

    public function clientSchedule(Request $request, $id)
    {
        $client = Client::with([
            'clientSchedule.clientSchedulePrice',
            'clientPrice' => function ($query) {
                $query->orderBy('position', 'asc');
            }
        ])->findOrFail($id);
        $currentDate = $client->start_date ? Carbon::createFromFormat('d/m/Y', $client->start_date) : now();
        $secondCurrentDate = $client->second_start_date ? Carbon::createFromFormat('d/m/Y', $client->second_start_date) : now();

        $currentMonthReal = $currentDate->format('F');
        $currentYearReal = $currentDate->format('Y');

        $weekNumberReal = $currentDate->weekOfMonth;
        $weekSecondNumberReal = $secondCurrentDate->weekOfMonth;

        $startOfWeek = $currentDate->startOfWeek()->format('d');
        $endOfWeek = $currentDate->endOfWeek()->format('d');


        $currentYear = now()->year;
        $currentMonth = now()->format('F');
        $selectedMonth = "$currentMonth $currentYear";

        $startDateClient = $client->start_date ? Carbon::createFromFormat('d/m/Y', $client->start_date) : Carbon::now();


        if ($client->service_frequency == 'normalWeek' || $client->service_frequency == 'monthly' || $client->service_frequency == 'quarterly' || $client->service_frequency == 'eightWeek') {
            $startDateClient = Carbon::createFromFormat('d/m/Y', $client->start_date);
            $clientYear = $startDateClient->year;
            $firstMondayOfYear = Carbon::parse("first Monday of January $clientYear");

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

            // Initialize $months array to avoid undefined variable error
            $months = [];

            $matchedRange = null;
            foreach ($customStartDates as $range => $startDate) {
                if ($startDateClient->gte($startDate) && $startDateClient->lt($startDate->copy()->addWeeks(4))) {
                    $matchedRange = $range;
                    break;
                }
            }

            if ($matchedRange) {
                $rangeStart = $customStartDates[$matchedRange];
                for ($i = 0; $i < 4; $i++) {
                    $weekStart = $rangeStart->copy()->addWeeks($i);
                    $weekEnd = $weekStart->copy()->addDays(6);

                    $datesBetween = [];
                    $currentDate = $weekStart->copy();
                    while ($currentDate <= $weekEnd) {
                        $datesBetween[] = $currentDate->format('Y-m-d');
                        $currentDate->addDay();
                    }

                    $monthStart = $weekStart->format('F');
                    $monthEnd = $weekEnd->format('F');

                    if (!isset($months[$monthStart])) {
                        $months[$monthStart] = [
                            'weeks' => [],
                            'week_number' => 1,
                        ];
                    }

                    if ($monthStart === $monthEnd) {
                        $months[$monthStart]['weeks'][] = [
                            'month' => $monthStart,
                            'year' => $weekStart->year,
                            'week_number' => $months[$monthStart]['week_number'],
                            'start_date' => $weekStart->format('Y-m-d'),
                            'end_date' => $weekEnd->format('Y-m-d'),
                            'dates_between' => $datesBetween,
                        ];
                        $months[$monthStart]['week_number']++;
                    } else {
                        if (!isset($months[$monthEnd])) {
                            $months[$monthEnd] = [
                                'weeks' => [],
                                'week_number' => 1,
                            ];
                        }

                        $months[$monthEnd]['weeks'][] = [
                            'month' => $monthEnd,
                            'year' => $weekEnd->year,
                            'week_number' => $months[$monthEnd]['week_number'],
                            'start_date' => $weekStart->format('Y-m-d'),
                            'end_date' => $weekEnd->format('Y-m-d'),
                            'dates_between' => $datesBetween,
                        ];
                        $months[$monthEnd]['week_number']++;
                    }
                }
            }
        } elseif ($client->service_frequency == 'quarterly' || $client->service_frequency == 'eightWeek') {

            $endDateClient = $client->end_date ? Carbon::createFromFormat('d/m/Y', $client->end_date) : Carbon::now();
            $startDateClient = Carbon::createFromFormat('d/m/Y', $client->start_date);

            $weekStart = $startDateClient->copy()->startOfWeek(Carbon::MONDAY);

            $weeks = [];
            $currentWeekStart = $weekStart;
            $months = [];

            while ($currentWeekStart <= $endDateClient) {
                $currentWeekEnd = $currentWeekStart->copy()->addDays(6);

                if ($currentWeekEnd->gt($endDateClient)) {
                    $currentWeekEnd = $endDateClient;
                }

                $datesBetween = [];
                $currentDate = $currentWeekStart->copy();
                while ($currentDate <= $currentWeekEnd) {
                    $datesBetween[] = $currentDate->format('Y-m-d');
                    $currentDate->addDay();
                }

                $monthStart = $currentWeekStart->format('F');
                $monthEnd = $currentWeekEnd->format('F');

                if (!isset($months[$monthStart])) {
                    $months[$monthStart] = [
                        'weeks' => [],
                        'week_number' => 1,
                    ];
                }

                if (!isset($months[$monthEnd])) {
                    $months[$monthEnd] = [
                        'weeks' => [],
                        'week_number' => 1,
                    ];
                }

                if ($monthStart === $monthEnd) {
                    $months[$monthStart]['weeks'][] = [
                        'month' => $monthStart,
                        'year' => $currentWeekStart->year,
                        'week_number' => $months[$monthStart]['week_number'],
                        'start_date' => $currentWeekStart->format('Y-m-d'),
                        'end_date' => $currentWeekEnd->format('Y-m-d'),
                        'dates_between' => $datesBetween,
                    ];
                    $months[$monthStart]['week_number']++;
                } else {
                    $months[$monthEnd]['weeks'][] = [
                        'month' => $monthStart,
                        'year' => $currentWeekStart->year,
                        'week_number' => $months[$monthEnd]['week_number'],
                        'start_date' => $currentWeekStart->format('Y-m-d'),
                        'end_date' => $currentWeekEnd->format('Y-m-d'),
                        'dates_between' => $datesBetween,
                    ];
                    $months[$monthEnd]['week_number']++;
                }

                $currentWeekStart->addWeek();
            }
        } elseif ($client->service_frequency == 'biMonthly') {

            $secondStartDateClient = Carbon::createFromFormat('d/m/Y', $client->second_start_date);
            $firstMondayOfMonth = Carbon::parse("first Monday of " . $startDateClient->format('F') . " " . $startDateClient->year);
            $secondFirstMondayOfMonth = Carbon::parse("first Monday of " . $secondStartDateClient->format('F') . " " . $secondStartDateClient->year);

            if ($secondStartDateClient->lt($secondFirstMondayOfMonth)) {
                $secondStartDateClient = $secondFirstMondayOfMonth;
            }

            $weekStart = $startDateClient->copy()->startOfWeek(Carbon::MONDAY);
            $weekNumber = 1;
            while ($weekStart->month == $startDateClient->month) {
                $weekEnd = $weekStart->copy()->addDays(7);
                if ($startDateClient->between($weekStart, $weekEnd)) {
                    break;
                }
                $weekStart->addDays(7);
                $weekNumber++;
            }

            if ($weekNumber > 4) {
                $weekNumber = 4;
            }

            $weeks = collect([
                [
                    'week_number' => $weekNumber,
                    'start_date' => $weekStart->format('Y-m-d'),
                    'end_date' => $weekStart->copy()->addDays(6)->format('Y-m-d'),
                    'month' => $startDateClient->format('F'),
                    'year' => $startDateClient->year,
                ]
            ]);

            $months[$startDateClient->format('F')]['weeks'] = array_merge(
                $months[$startDateClient->format('F')]['weeks'] ?? [],
                $weeks->toArray()
            );

            $weekStart = $secondFirstMondayOfMonth->copy();
            $weekNumber = 1;
            if ($secondStartDateClient->dayOfWeek == Carbon::SUNDAY) {
                $weekStart = $secondStartDateClient->copy()->subDays(6);
            }

            while ($weekStart->month == $secondStartDateClient->month) {
                $weekEnd = $weekStart->copy()->addDays(6);
                if ($secondStartDateClient->between($weekStart, $weekEnd)) {
                    break;
                }
                $weekStart->addDays(7);
                $weekNumber++;
            }

            if ($weekNumber > 4) {
                $weekNumber = 4;
            }

            $secondWeeks = collect([
                [
                    'week_number' => $weekNumber,
                    'start_date' => $weekStart->format('Y-m-d'),
                    'end_date' => $weekStart->copy()->addDays(6)->format('Y-m-d'),
                    'month' => $secondStartDateClient->format('F'),
                    'year' => $secondStartDateClient->year,
                ]
            ]);

            $months[$secondStartDateClient->format('F')]['weeks'] = array_merge(
                $months[$secondStartDateClient->format('F')]['weeks'] ?? [],
                $secondWeeks->toArray()
            );
        } elseif ($client->service_frequency == 'biAnnually') {

            $firstMondayOfMonth = Carbon::parse("first Monday of " . $startDateClient->format('F') . " " . $startDateClient->year);

            $weekStart = $startDateClient->copy()->startOfWeek(Carbon::MONDAY);
            $weekNumber = 1;
            while ($weekStart->month == $startDateClient->month) {
                $weekEnd = $weekStart->copy()->addDays(7);
                if ($startDateClient->between($weekStart, $weekEnd)) {
                    break;
                }
                $weekStart->addDays(7);
                $weekNumber++;
            }

            if ($weekNumber > 4) {
                $weekNumber = 4;
            }

            $weeks = collect([
                [
                    'week_number' => $weekNumber,
                    'start_date' => $weekStart->format('Y-m-d'),
                    'end_date' => $weekStart->copy()->addDays(6)->format('Y-m-d'),
                    'month' => $startDateClient->format('F'),
                    'year' => $startDateClient->year,
                ]
            ]);

            $months[$startDateClient->format('F')]['weeks'] = array_merge(
                $months[$startDateClient->format('F')]['weeks'] ?? [],
                $weeks->toArray()
            );


            if (!empty($client->second_start_date)) {

                $secondStartDateClient = Carbon::createFromFormat('d/m/Y', $client->second_start_date);
                $secondFirstMondayOfMonth = Carbon::parse("first Monday of " . $secondStartDateClient->format('F') . " " . $secondStartDateClient->year);

                if ($secondStartDateClient->lt($secondFirstMondayOfMonth)) {
                    $secondStartDateClient = $secondFirstMondayOfMonth;
                }

                $weekStart = $secondFirstMondayOfMonth->copy();
                $weekNumber = 1;
                if ($secondStartDateClient->dayOfWeek == Carbon::SUNDAY) {
                    $weekStart = $secondStartDateClient->copy()->subDays(6);
                }

                while ($weekStart->month == $secondStartDateClient->month) {
                    $weekEnd = $weekStart->copy()->addDays(6);
                    if ($secondStartDateClient->between($weekStart, $weekEnd)) {
                        break;
                    }
                    $weekStart->addDays(7);
                    $weekNumber++;
                }

                if ($weekNumber > 4) {
                    $weekNumber = 4;
                }

                $secondWeeks = collect([
                    [
                        'week_number' => $weekNumber,
                        'start_date' => $weekStart->format('Y-m-d'),
                        'end_date' => $weekStart->copy()->addDays(6)->format('Y-m-d'),
                        'month' => $secondStartDateClient->format('F'),
                        'year' => $secondStartDateClient->year,
                    ]
                ]);

                $months[$secondStartDateClient->format('F')]['weeks'] = array_merge(
                    $months[$secondStartDateClient->format('F')]['weeks'] ?? [],
                    $secondWeeks->toArray()
                );
            }
        } elseif ($client->service_frequency == 'annually') {

            $weekStart = $startDateClient->copy()->startOfWeek(Carbon::MONDAY);
            $weekNumber = 1;
            while ($weekStart->month == $startDateClient->month) {
                $weekEnd = $weekStart->copy()->addDays(7);
                if ($startDateClient->between($weekStart, $weekEnd)) {
                    break;
                }
                $weekStart->addDays(7);
                $weekNumber++;
            }

            if ($weekNumber > 4) {
                $weekNumber = 4;
            }

            $weeks = collect([
                [
                    'week_number' => $weekNumber,
                    'start_date' => $weekStart->format('Y-m-d'),
                    'end_date' => $weekStart->copy()->addDays(6)->format('Y-m-d'),
                    'month' => $startDateClient->format('F'),
                    'year' => $startDateClient->year,
                ]
            ]);

            $months[$startDateClient->format('F')]['weeks'] = array_merge(
                $months[$startDateClient->format('F')]['weeks'] ?? [],
                $weeks->toArray()
            );
        }
        $flattenedMonths = [];
        $number = 0;

        foreach ($months as $monthName => $monthData) {
            if (!isset($monthData['weeks']) && empty($monthData['weeks'])) {
                continue;
            }
            foreach ($monthData['weeks'] as $index => $week) {
                $week['week_index'] = $number;
                $flattenedMonths[] = $week;
                $number++;
            }
        }

        $months = $flattenedMonths;

        return view('dashboard.client-schedule', compact('client', 'months', 'weekNumberReal', 'endOfWeek', 'startOfWeek', 'currentYearReal', 'currentMonthReal', 'weekSecondNumberReal'));
    }

    public function clientScheduleSave(Request $request, $id)
    {
        Log::info('Request Data for clientScheduleSave', [
            'id' => $id,
            'request_data' => $request->all()
        ]);

        // return $request->all();
        $client = Client::findOrFail($id);
        $status = auth()->user()->hasRole('admin') ? '1' : '0';
        $monthsToGenerate = 2;
        $startDateClient = $client->start_date ? Carbon::createFromFormat('d/m/Y', $client->start_date) : Carbon::now();
        $selectedYear = Carbon::now()->year;
        $firstMondayOfYear = Carbon::create($selectedYear, 1, 1)->modify('first monday');

        // if ($client->service_frequency == 'normalWeek') {
        //     $currentYear = $client->start_date ? Carbon::createFromFormat('d/m/Y', $client->start_date)->year : Carbon::now()->year;
        //     $nextYear = $currentYear + 1;
        //     $nextYear2 = $currentYear + 2;
        //     $nextYear3 = $currentYear + 3;

        //     $customStartDates = [
        //         "January - February-{$currentYear}" => Carbon::create($currentYear, 1, 1)->modify('first monday'),
        //         "January - February-{$nextYear}" => Carbon::create($nextYear, 1, 1)->modify('first monday'),
        //         "February - March-{$currentYear}" => Carbon::create($currentYear, 1, 1)->modify('first monday')->addWeeks(4),
        //         "February - March-{$nextYear}" => Carbon::create($nextYear, 1, 1)->modify('first monday')->addWeeks(4),
        //         "March-{$currentYear}" => Carbon::create($currentYear, 1, 1)->modify('first monday')->addWeeks(8),
        //         "March-{$nextYear}" => Carbon::create($nextYear, 1, 1)->modify('first monday')->addWeeks(8),
        //         "March - April-{$currentYear}" => Carbon::create($currentYear, 1, 1)->modify('first monday')->addWeeks(12),
        //         "March - April-{$nextYear}" => Carbon::create($nextYear, 1, 1)->modify('first monday')->addWeeks(12),
        //         "April - May-{$currentYear}" => Carbon::create($currentYear, 1, 1)->modify('first monday')->addWeeks(16),
        //         "April - May-{$nextYear}" => Carbon::create($nextYear, 1, 1)->modify('first monday')->addWeeks(16),
        //         "May - June-{$currentYear}" => Carbon::create($currentYear, 1, 1)->modify('first monday')->addWeeks(20),
        //         "May - June-{$nextYear}" => Carbon::create($nextYear, 1, 1)->modify('first monday')->addWeeks(20),
        //         "June - July-{$currentYear}" => Carbon::create($currentYear, 1, 1)->modify('first monday')->addWeeks(24),
        //         "June - July-{$nextYear}" => Carbon::create($nextYear, 1, 1)->modify('first monday')->addWeeks(24),
        //         "July - August-{$currentYear}" => Carbon::create($currentYear, 1, 1)->modify('first monday')->addWeeks(28),
        //         "July - August-{$nextYear}" => Carbon::create($nextYear, 1, 1)->modify('first monday')->addWeeks(28),
        //         "August - September-{$currentYear}" => Carbon::create($currentYear, 1, 1)->modify('first monday')->addWeeks(32),
        //         "August - September-{$nextYear}" => Carbon::create($nextYear, 1, 1)->modify('first monday')->addWeeks(32),
        //         "September - October-{$currentYear}" => Carbon::create($currentYear, 1, 1)->modify('first monday')->addWeeks(36),
        //         "September - October-{$nextYear}" => Carbon::create($nextYear, 1, 1)->modify('first monday')->addWeeks(36),
        //         "October - November-{$currentYear}" => Carbon::create($currentYear, 1, 1)->modify('first monday')->addWeeks(40),
        //         "October - November-{$nextYear}" => Carbon::create($nextYear, 1, 1)->modify('first monday')->addWeeks(40),
        //         "November - December-{$currentYear}" => Carbon::create($currentYear, 1, 1)->modify('first monday')->addWeeks(44),
        //         "November - December-{$nextYear}" => Carbon::create($nextYear, 1, 1)->modify('first monday')->addWeeks(44),
        //         "December - January-{$currentYear}" => Carbon::create($currentYear, 1, 1)->modify('first monday')->addWeeks(48),
        //         "December - January-{$nextYear}" => Carbon::create($nextYear, 1, 1)->modify('first monday')->addWeeks(48),
        //         // Next year 2
        //         "January - February-{$nextYear2}" => Carbon::create($nextYear2, 1, 1)->modify('first monday'),
        //         "February - March-{$nextYear2}" => Carbon::create($nextYear2, 1, 1)->modify('first monday')->addWeeks(4),
        //         "March-{$nextYear2}" => Carbon::create($nextYear2, 1, 1)->modify('first monday')->addWeeks(8),
        //         "March - April-{$nextYear2}" => Carbon::create($nextYear2, 1, 1)->modify('first monday')->addWeeks(12),
        //         "April - May-{$nextYear2}" => Carbon::create($nextYear2, 1, 1)->modify('first monday')->addWeeks(16),
        //         "May - June-{$nextYear2}" => Carbon::create($nextYear2, 1, 1)->modify('first monday')->addWeeks(20),
        //         "June - July-{$nextYear2}" => Carbon::create($nextYear2, 1, 1)->modify('first monday')->addWeeks(24),
        //         "July - August-{$nextYear2}" => Carbon::create($nextYear2, 1, 1)->modify('first monday')->addWeeks(28),
        //         "August - September-{$nextYear2}" => Carbon::create($nextYear2, 1, 1)->modify('first monday')->addWeeks(32),
        //         "September - October-{$nextYear2}" => Carbon::create($nextYear2, 1, 1)->modify('first monday')->addWeeks(36),
        //         "October - November-{$nextYear2}" => Carbon::create($nextYear2, 1, 1)->modify('first monday')->addWeeks(40),
        //         "November - December-{$nextYear2}" => Carbon::create($nextYear2, 1, 1)->modify('first monday')->addWeeks(44),
        //         "December - January-{$nextYear2}" => Carbon::create($nextYear2, 1, 1)->modify('first monday')->addWeeks(48),
        //         // Next year 3
        //         "January - February-{$nextYear3}" => Carbon::create($nextYear3, 1, 1)->modify('first monday'),
        //         "February - March-{$nextYear3}" => Carbon::create($nextYear3, 1, 1)->modify('first monday')->addWeeks(4),
        //         "March-{$nextYear3}" => Carbon::create($nextYear3, 1, 1)->modify('first monday')->addWeeks(8),
        //         "March - April-{$nextYear3}" => Carbon::create($nextYear3, 1, 1)->modify('first monday')->addWeeks(12),
        //         "April - May-{$nextYear3}" => Carbon::create($nextYear3, 1, 1)->modify('first monday')->addWeeks(16),
        //         "May - June-{$nextYear3}" => Carbon::create($nextYear3, 1, 1)->modify('first monday')->addWeeks(20),
        //         "June - July-{$nextYear3}" => Carbon::create($nextYear3, 1, 1)->modify('first monday')->addWeeks(24),
        //         "July - August-{$nextYear3}" => Carbon::create($nextYear3, 1, 1)->modify('first monday')->addWeeks(28),
        //         "August - September-{$nextYear3}" => Carbon::create($nextYear3, 1, 1)->modify('first monday')->addWeeks(32),
        //         "September - October-{$nextYear3}" => Carbon::create($nextYear3, 1, 1)->modify('first monday')->addWeeks(36),
        //         "October - November-{$nextYear3}" => Carbon::create($nextYear3, 1, 1)->modify('first monday')->addWeeks(40),
        //         "November - December-{$nextYear3}" => Carbon::create($nextYear3, 1, 1)->modify('first monday')->addWeeks(44),
        //         "December - January-{$nextYear3}" => Carbon::create($nextYear3, 1, 1)->modify('first monday')->addWeeks(48),
        //     ];

        //     function findMatchingMonth($month, $customStartDates)
        //     {
        //         foreach ($customStartDates as $key => $value) {
        //             if (str_contains($key, $month)) {
        //                 return $key;
        //             }
        //         }
        //         return null;
        //     }

        //     $inputSchedules = [];
        //     $isNonRecurring = $request->recurring_type === 'non_recurring';

        //     foreach ($request->input('month') as $monthName => $weeks) {
        //         $matchedMonthKey = findMatchingMonth($monthName, $customStartDates);
        //         if ($matchedMonthKey === null) {
        //             continue;
        //         }

        //         foreach ($weeks as $weekIndex => $week) {

        //             if (!isset($week['start_date'], $week['end_date'], $week['prices']) || empty($week['prices'])) {
        //                 continue;
        //             }

        //             if (!isset($week['note_start_date']) || empty($week['note_start_date'])) {
        //                 return back()->withErrors(['error' => "Please select a start date for {$monthName} - {$weekIndex}"])->withInput();
        //             }

        //             $startDateClient = Carbon::createFromFormat('Y-m-d', $week['note_start_date']);

        //             $matchedRange = null;
        //             foreach ($customStartDates as $range => $startDate) {
        //                 if ($startDateClient->gte($startDate) && $startDateClient->lt($startDate->copy()->addWeeks(4))) {
        //                     $matchedRange = $range;
        //                     break;
        //                 }
        //             }

        //             if ($matchedRange === null) {
        //                 return back()->withErrors(['error' => "Invalid date range for {$monthName} - {$weekIndex}"])->withInput();
        //             }

        //             preg_match('/\d+/', $weekIndex, $matches);
        //             $rangeStart = $customStartDates[$matchedRange];
        //             $originalStartDate = $rangeStart->copy()->addWeeks($matches[0]);
        //             $originalEndDate = $originalStartDate->copy()->addDays(6);

        //             if (!$isNonRecurring) {
        //                 $inputSchedules[] = [
        //                     'month' => $monthName,
        //                     'week' => $weekIndex,
        //                     'week_month' => $monthName,
        //                     'start_date' => $originalStartDate->toDateString(),
        //                     'end_date' => $originalEndDate->toDateString(),
        //                     'note' => $week['note'] ?? null,
        //                     'note_two' => $week['note_two'] ?? null,
        //                     'note_type' => $week['note_type'] ?? null,
        //                     'note_date' => $week['note_start_date'] ?? $week['start_date'] ?? null,
        //                     'prices' => $week['prices'],
        //                     'extra_work' =>  null,
        //                     'extra_work_price' => $week['note_extra_price'] ?? null,
        //                     'note_week_no' => 0,
        //                     'extra_work_price_id' => null,
        //                 ];
        //             }

        //             $additionalNotes = $week['additional_note'] ?? [];
        //             $additionalPricesNested = $week['extra_prices'] ?? [];
        //             $additionalNoteTypes = $week['extra_note_type'] ?? [];
        //             $additionalNoteDates = $week['extra_note_start_date'] ?? [];

        //             $allNoteDates = array_merge(
        //                 [$week['note_start_date'] ?? $week['start_date'] ?? null],
        //                 $additionalNoteDates
        //             );
        //             $allNoteTypes = array_merge(
        //                 [$week['note_type'] ?? null],
        //                 $additionalNoteTypes
        //             );
        //             $allNotes = array_merge(
        //                 [$week['note'] ?? null, $week['note_two'] ?? null],
        //                 $additionalNotes
        //             );

        //             if ($isNonRecurring) {
        //                 $allOccurrences = [];
        //                 $totalNotes = count($allNoteDates);

        //                 $noteOccurrences = [];

        //                 foreach ($allNoteDates as $dateIndex => $noteDate) {
        //                     if (!isset($noteDate) || empty($noteDate)) {
        //                         continue;
        //                     }

        //                     $noteType = $allNoteTypes[$dateIndex] ?? null;
        //                     $intervalWeeks = match ($noteType) {
        //                         '8_weeks'  => 2,
        //                         '12_weeks' => 3,
        //                         '24_weeks' => 6,
        //                         '52_weeks' => 13,
        //                         default    => 1,
        //                     };

        //                     for ($i = 0; $i < 39; $i += $intervalWeeks) {
        //                         $startDateClient = Carbon::createFromFormat('Y-m-d', $noteDate)->addWeeks($i * 4);

        //                         $matchedRange = null;
        //                         foreach ($customStartDates as $range => $startDate) {
        //                             if ($startDateClient->gte($startDate) && $startDateClient->lt($startDate->copy()->addWeeks(4))) {
        //                                 $matchedRange = $range;
        //                                 break;
        //                             }
        //                         }

        //                         if ($matchedRange === null) continue;

        //                         $rangeStart = $customStartDates[$matchedRange];
        //                         preg_match('/\d+/', $weekIndex, $matches);
        //                         $originalStartDate = $rangeStart->copy()->addWeeks($matches[0]);
        //                         $originalEndDate   = $originalStartDate->copy()->addDays(6);
        //                         $weekKey = $originalStartDate->toDateString();

        //                         $noteOccurrences[$dateIndex][] = $weekKey;
        //                     }
        //                 }

        //                 // Ab cycle banao: Note1, Note2, Note3, Note1, Note2 ...
        //                 // Sab unique dates collect karo sorted
        //                 $allDates = [];
        //                 foreach ($noteOccurrences as $dates) {
        //                     $allDates = array_merge($allDates, $dates);
        //                 }
        //                 $allDates = array_unique($allDates);
        //                 sort($allDates);

        //                 // Har date ko cycle ke hisaab se note assign karo
        //                 $noteIndexKeys = array_keys(array_filter($allNoteDates, fn($d) => !empty($d)));
        //                 $cycleLength   = count($noteIndexKeys);

        //                 foreach ($allDates as $cycleIndex => $weekKey) {
        //                     $assignedNoteIndex = $noteIndexKeys[$cycleIndex % $cycleLength]; // cycling!
        //                     $noteDate  = $allNoteDates[$assignedNoteIndex];
        //                     $noteType  = $allNoteTypes[$assignedNoteIndex] ?? null;

        //                     // Prices
        //                     $currentPrices     = [];
        //                     $extraWorkPriceIds = null;
        //                     $extraWorkNamesJson = null;
        //                     $extraWorkValuesJson = null;

        //                     if ($assignedNoteIndex === 0) {
        //                         $currentPrices = $week['prices'];
        //                     } else {
        //                         $notePrices  = $additionalPricesNested[$assignedNoteIndex - 1] ?? [];
        //                         $validPrices = array_filter($notePrices, fn($p) => $p && $p !== "0");

        //                         if (!empty($validPrices)) {
        //                             $priceDetails = \App\Models\ClientPriceList::whereIn('id', $validPrices)->get();
        //                             $extraWorkNames  = [];
        //                             $extraWorkValues = [];
        //                             foreach ($priceDetails as $pd) {
        //                                 $extraWorkNames[]  = $pd->name;
        //                                 $extraWorkValues[] = $pd->value;
        //                             }
        //                             $extraWorkPriceIds   = json_encode(array_values($validPrices));
        //                             $extraWorkNamesJson  = json_encode($extraWorkNames);
        //                             $extraWorkValuesJson = json_encode($extraWorkValues);
        //                         }
        //                     }

        //                     // Date objects reconstruct
        //                     $startDateObj = Carbon::createFromFormat('Y-m-d', $weekKey);
        //                     $endDateObj   = $startDateObj->copy()->addDays(6);

        //                     $inputSchedules[] = [
        //                         'month'               => $startDateObj->format('F'),
        //                         'week_month'          => $monthName,
        //                         'week'                => $weekIndex,
        //                         'start_date'          => $weekKey,
        //                         'end_date'            => $endDateObj->toDateString(),
        //                         'note'                => $allNotes[$assignedNoteIndex] ?? str_repeat('.', $assignedNoteIndex + 1),
        //                         'note_two'            => $allNotes[$assignedNoteIndex + 1] ?? null,
        //                         'note_type'           => $noteType,
        //                         'note_date'           => $noteDate,
        //                         'prices'              => $currentPrices,
        //                         'extra_work'          => $extraWorkNamesJson,
        //                         'extra_work_price'    => $extraWorkValuesJson,
        //                         'note_week_no'        => $assignedNoteIndex,
        //                         'extra_work_price_id' => $extraWorkPriceIds,
        //                     ];
        //                 }

        //                 continue; // foreach weeks loop continue
        //             }

        //             $firstNoteEndDate = null;

        //             foreach ($allNoteDates as $dateIndex => $noteDate) {
        //                 if (!isset($noteDate) || empty($noteDate)) {
        //                     continue;
        //                 }

        //                 $noteType = $allNoteTypes[$dateIndex] ?? null;
        //                 $lastNote = $allNotes[$dateIndex - 1] ?? null;

        //                 for ($i = 1; $i < 40; $i++) {
        //                     if ($i === 1) {
        //                         $startDateClient = Carbon::createFromFormat('Y-m-d', $noteDate);
        //                     } else {
        //                         $startDateClient = Carbon::createFromFormat('Y-m-d', $noteDate)->addWeeks(($i - 1) * 4);
        //                     }

        //                     $matchedRange = null;
        //                     foreach ($customStartDates as $range => $startDate) {
        //                         if ($startDateClient->gte($startDate) && $startDateClient->lt($startDate->copy()->addWeeks(4))) {
        //                             $matchedRange = $range;
        //                             break;
        //                         }
        //                     }

        //                     if ($matchedRange === null) {
        //                         \Log::warning("No matching range found for date: " . $startDateClient->toDateString());
        //                         continue;
        //                     }

        //                     $rangeStart = $customStartDates[$matchedRange];
        //                     preg_match('/\d+/', $weekIndex, $matches);
        //                     $originalStartDate = $rangeStart->copy()->addWeeks($matches[0]);
        //                     $originalEndDate = $originalStartDate->copy()->addDays(6);

        //                     if ($firstNoteEndDate != null && $originalStartDate->gte($firstNoteEndDate)) {
        //                         break;
        //                     }
        //                     if ($i === 39 && $firstNoteEndDate === null) {
        //                         $firstNoteEndDate = $originalStartDate;
        //                     }

        //                     $note = null;
        //                     $noteTwo = null;
        //                     $weekNoteType = null;
        //                     $noteWeekCounter = 0;
        //                     $currentPrices = $dateIndex === 0 ? $week['prices'] : [];
        //                     $extraWorkPriceIds = null;
        //                     $extraWorkNamesJson = null;
        //                     $extraWorkValuesJson = null;

        //                     if ($dateIndex > 0) {
        //                         $notePrices = $additionalPricesNested[$dateIndex - 1] ?? [];
        //                         if (!is_array($notePrices)) {
        //                             $notePrices = [];
        //                         }
        //                         $validPrices = array_filter($notePrices, function ($price) {
        //                             return $price && $price !== "0";
        //                         });

        //                         if (!empty($validPrices)) {
        //                             $priceDetails = \App\Models\ClientPriceList::whereIn('id', $validPrices)->get();
        //                             $extraWorkNames = [];
        //                             $extraWorkValues = [];
        //                             foreach ($priceDetails as $priceDetail) {
        //                                 $extraWorkNames[] = $priceDetail->name;
        //                                 $extraWorkValues[] = $priceDetail->value;
        //                             }
        //                             $extraWorkPriceIds = json_encode(array_values($validPrices));
        //                             $extraWorkNamesJson = json_encode($extraWorkNames);
        //                             $extraWorkValuesJson = json_encode($extraWorkValues);
        //                         }
        //                     }

        //                     if ($i === 1) {
        //                         $note = $allNotes[$dateIndex] ?? str_repeat('.', $dateIndex + 1);
        //                         $noteTwo = $allNotes[$dateIndex + 1] ?? null;
        //                         $noteWeekCounter = $dateIndex;
        //                         $weekNoteType = $noteType;
        //                     } elseif ($noteType === 'weekly') {
        //                         $note = $allNotes[$dateIndex] ?? str_repeat('.', $dateIndex + 1);
        //                         $noteTwo = $allNotes[$dateIndex + 1] ?? null;
        //                         $weekNoteType = $noteType;
        //                     } elseif ($noteType === '4_weeks' && (($i - 1) % 1) === 0) {
        //                         $note = $allNotes[$dateIndex] ?? str_repeat('.', $dateIndex + 1);
        //                         $noteTwo = $allNotes[$dateIndex + 1] ?? null;
        //                         $noteWeekCounter = $dateIndex;
        //                         $weekNoteType = $noteType;
        //                     } elseif ($noteType === '8_weeks' && (($i - 1) % 2) === 0) {
        //                         $note = $allNotes[$dateIndex] ?? str_repeat('.', $dateIndex + 1);
        //                         $noteTwo = $allNotes[$dateIndex + 1] ?? null;
        //                         $noteWeekCounter = $dateIndex;
        //                         $weekNoteType = $noteType;
        //                     } elseif ($noteType === '12_weeks' && (($i - 1) % 3) === 0) {
        //                         $note = $allNotes[$dateIndex] ?? str_repeat('.', $dateIndex + 1);
        //                         $noteTwo = $allNotes[$dateIndex + 1] ?? null;
        //                         $weekNoteType = '12_weeks';
        //                         $noteWeekCounter = $dateIndex;
        //                     } elseif ($noteType === '24_weeks' && (($i - 1) % 6) === 0) {
        //                         $note = $allNotes[$dateIndex] ?? str_repeat('.', $dateIndex + 1);
        //                         $noteTwo = $allNotes[$dateIndex + 1] ?? null;
        //                         $weekNoteType = '24_weeks';
        //                         $noteWeekCounter = $dateIndex;
        //                     } elseif ($noteType === '52_weeks' && (($i - 1) % 12) === 0) {
        //                         $note = $allNotes[$dateIndex] ?? str_repeat('.', $dateIndex + 1);
        //                         $noteTwo = $allNotes[$dateIndex + 1] ?? null;
        //                         $weekNoteType = '52_weeks';
        //                         $noteWeekCounter = $dateIndex;
        //                     }

        //                     $existingScheduleIndex = null;
        //                     foreach ($inputSchedules as $index => $schedule) {
        //                         if ($schedule['start_date'] === $originalStartDate->toDateString() && $schedule['week'] === $weekIndex) {
        //                             $existingScheduleIndex = $index;
        //                             break;
        //                         }
        //                     }

        //                     if ($existingScheduleIndex !== null) {
        //                         if (!isset($note)) {
        //                             continue;
        //                         }
        //                         $inputSchedules[$existingScheduleIndex] = [
        //                             'month' => $originalStartDate->format('F'),
        //                             'week_month' => $monthName,
        //                             'week' => $weekIndex,
        //                             'start_date' => $originalStartDate->toDateString(),
        //                             'end_date' => $originalEndDate->toDateString(),
        //                             'note' => $note,
        //                             'note_two' => $noteTwo,
        //                             'note_type' => $weekNoteType ?? null,
        //                             'note_date' => $noteDate ?? null,
        //                             'prices' => $currentPrices,
        //                             'extra_work' => $extraWorkNamesJson,
        //                             'extra_work_price' => $extraWorkValuesJson,
        //                             'note_week_no' => $noteWeekCounter ?? null,
        //                             'extra_work_price_id' => $extraWorkPriceIds,
        //                         ];
        //                     } else {
        //                         $inputSchedules[] = [
        //                             'month' => $originalStartDate->format('F'),
        //                             'week_month' => $monthName,
        //                             'week' => $weekIndex,
        //                             'start_date' => $originalStartDate->toDateString(),
        //                             'end_date' => $originalEndDate->toDateString(),
        //                             'note' => isset($note) ? $note : $lastNote,
        //                             'note_two' => $noteTwo,
        //                             'note_type' => $weekNoteType ?? null,
        //                             'note_date' => $noteDate ?? null,
        //                             'prices' => $currentPrices,
        //                             'extra_work' => $extraWorkNamesJson,
        //                             'extra_work_price' => $extraWorkValuesJson,
        //                             'note_week_no' => $noteWeekCounter ?? null,
        //                             'extra_work_price_id' => $extraWorkPriceIds,
        //                         ];
        //                     }
        //                 }
        //             }
        //         }
        //     }

        //     // Database operations (same as before)
        //     $existingSchedules = ClientSchedule::where('client_id', $id)->get();

        //     $existingSchedulesToDelete = $existingSchedules->filter(function ($schedule) use ($inputSchedules) {
        //         foreach ($inputSchedules as $inputSchedule) {
        //             if (
        //                 $schedule->month === $inputSchedule['month'] &&
        //                 $schedule->week == $inputSchedule['week'] &&
        //                 $schedule->start_date === $inputSchedule['start_date'] &&
        //                 $schedule->end_date === $inputSchedule['end_date']
        //             ) {
        //                 return false;
        //             }
        //         }
        //         return true;
        //     });

        //     foreach ($existingSchedulesToDelete as $scheduleToDelete) {
        //         ClientSchedulePrice::where('schedule_id', $scheduleToDelete->id)->delete();
        //         $scheduleToDelete->delete();
        //     }

        //     foreach ($inputSchedules as $scheduleData) {
        //         $prices = $scheduleData['prices'];
        //         unset($scheduleData['prices']);
        //         $existingSchedule = ClientSchedule::where('client_id', $id)
        //             ->where('month', $scheduleData['month'])
        //             ->where('week', $scheduleData['week'])
        //             ->where('start_date', $scheduleData['start_date'])
        //             ->where('end_date', $scheduleData['end_date'])
        //             ->first();

        //         if ($existingSchedule) {
        //             $existingSchedule->update($scheduleData);
        //         } else {
        //             $existingSchedule = ClientSchedule::create(array_merge($scheduleData, ['client_id' => $id]));
        //         }

        //         if (!empty($prices)) {
        //             $existingPrices = ClientSchedulePrice::where('client_id', $id)
        //                 ->where('schedule_id', $existingSchedule->id)
        //                 ->pluck('price_id')
        //                 ->toArray();

        //             $pricesToDelete = array_diff($existingPrices, $prices);

        //             ClientSchedulePrice::where('client_id', $id)
        //                 ->where('schedule_id', $existingSchedule->id)
        //                 ->whereIn('price_id', $pricesToDelete)
        //                 ->delete();

        //             $pricesToAdd = array_diff($prices, $existingPrices);

        //             foreach ($pricesToAdd as $priceId) {
        //                 ClientSchedulePrice::create([
        //                     'client_id' => $id,
        //                     'schedule_id' => $existingSchedule->id,
        //                     'price_id' => $priceId,
        //                 ]);
        //             }
        //         }
        //     }
        //     $client->update(['recurring_type' => $request->recurring_type]);
        // }
        if ($client->service_frequency == 'normalWeek') {
            $currentYear = $client->start_date ? Carbon::createFromFormat('d/m/Y', $client->start_date)->year : Carbon::now()->year;
            $nextYear  = $currentYear + 1;
            $nextYear2 = $currentYear + 2;
            $nextYear3 = $currentYear + 3;

            $customStartDates = [
                "January - February-{$currentYear}"  => Carbon::create($currentYear, 1, 1)->modify('first monday'),
                "February - March-{$currentYear}"    => Carbon::create($currentYear, 1, 1)->modify('first monday')->addWeeks(4),
                "March-{$currentYear}"               => Carbon::create($currentYear, 1, 1)->modify('first monday')->addWeeks(8),
                "March - April-{$currentYear}"       => Carbon::create($currentYear, 1, 1)->modify('first monday')->addWeeks(12),
                "April - May-{$currentYear}"         => Carbon::create($currentYear, 1, 1)->modify('first monday')->addWeeks(16),
                "May - June-{$currentYear}"          => Carbon::create($currentYear, 1, 1)->modify('first monday')->addWeeks(20),
                "June - July-{$currentYear}"         => Carbon::create($currentYear, 1, 1)->modify('first monday')->addWeeks(24),
                "July - August-{$currentYear}"       => Carbon::create($currentYear, 1, 1)->modify('first monday')->addWeeks(28),
                "August - September-{$currentYear}"  => Carbon::create($currentYear, 1, 1)->modify('first monday')->addWeeks(32),
                "September - October-{$currentYear}" => Carbon::create($currentYear, 1, 1)->modify('first monday')->addWeeks(36),
                "October - November-{$currentYear}"  => Carbon::create($currentYear, 1, 1)->modify('first monday')->addWeeks(40),
                "November - December-{$currentYear}" => Carbon::create($currentYear, 1, 1)->modify('first monday')->addWeeks(44),
                "December - January-{$currentYear}"  => Carbon::create($currentYear, 1, 1)->modify('first monday')->addWeeks(48),

                "January - February-{$nextYear}"     => Carbon::create($nextYear, 1, 1)->modify('first monday'),
                "February - March-{$nextYear}"       => Carbon::create($nextYear, 1, 1)->modify('first monday')->addWeeks(4),
                "March-{$nextYear}"                  => Carbon::create($nextYear, 1, 1)->modify('first monday')->addWeeks(8),
                "March - April-{$nextYear}"          => Carbon::create($nextYear, 1, 1)->modify('first monday')->addWeeks(12),
                "April - May-{$nextYear}"            => Carbon::create($nextYear, 1, 1)->modify('first monday')->addWeeks(16),
                "May - June-{$nextYear}"             => Carbon::create($nextYear, 1, 1)->modify('first monday')->addWeeks(20),
                "June - July-{$nextYear}"            => Carbon::create($nextYear, 1, 1)->modify('first monday')->addWeeks(24),
                "July - August-{$nextYear}"          => Carbon::create($nextYear, 1, 1)->modify('first monday')->addWeeks(28),
                "August - September-{$nextYear}"     => Carbon::create($nextYear, 1, 1)->modify('first monday')->addWeeks(32),
                "September - October-{$nextYear}"    => Carbon::create($nextYear, 1, 1)->modify('first monday')->addWeeks(36),
                "October - November-{$nextYear}"     => Carbon::create($nextYear, 1, 1)->modify('first monday')->addWeeks(40),
                "November - December-{$nextYear}"    => Carbon::create($nextYear, 1, 1)->modify('first monday')->addWeeks(44),
                "December - January-{$nextYear}"     => Carbon::create($nextYear, 1, 1)->modify('first monday')->addWeeks(48),

                "January - February-{$nextYear2}"    => Carbon::create($nextYear2, 1, 1)->modify('first monday'),
                "February - March-{$nextYear2}"      => Carbon::create($nextYear2, 1, 1)->modify('first monday')->addWeeks(4),
                "March-{$nextYear2}"                 => Carbon::create($nextYear2, 1, 1)->modify('first monday')->addWeeks(8),
                "March - April-{$nextYear2}"         => Carbon::create($nextYear2, 1, 1)->modify('first monday')->addWeeks(12),
                "April - May-{$nextYear2}"           => Carbon::create($nextYear2, 1, 1)->modify('first monday')->addWeeks(16),
                "May - June-{$nextYear2}"            => Carbon::create($nextYear2, 1, 1)->modify('first monday')->addWeeks(20),
                "June - July-{$nextYear2}"           => Carbon::create($nextYear2, 1, 1)->modify('first monday')->addWeeks(24),
                "July - August-{$nextYear2}"         => Carbon::create($nextYear2, 1, 1)->modify('first monday')->addWeeks(28),
                "August - September-{$nextYear2}"    => Carbon::create($nextYear2, 1, 1)->modify('first monday')->addWeeks(32),
                "September - October-{$nextYear2}"   => Carbon::create($nextYear2, 1, 1)->modify('first monday')->addWeeks(36),
                "October - November-{$nextYear2}"    => Carbon::create($nextYear2, 1, 1)->modify('first monday')->addWeeks(40),
                "November - December-{$nextYear2}"   => Carbon::create($nextYear2, 1, 1)->modify('first monday')->addWeeks(44),
                "December - January-{$nextYear2}"    => Carbon::create($nextYear2, 1, 1)->modify('first monday')->addWeeks(48),

                "January - February-{$nextYear3}"    => Carbon::create($nextYear3, 1, 1)->modify('first monday'),
                "February - March-{$nextYear3}"      => Carbon::create($nextYear3, 1, 1)->modify('first monday')->addWeeks(4),
                "March-{$nextYear3}"                 => Carbon::create($nextYear3, 1, 1)->modify('first monday')->addWeeks(8),
                "March - April-{$nextYear3}"         => Carbon::create($nextYear3, 1, 1)->modify('first monday')->addWeeks(12),
                "April - May-{$nextYear3}"           => Carbon::create($nextYear3, 1, 1)->modify('first monday')->addWeeks(16),
                "May - June-{$nextYear3}"            => Carbon::create($nextYear3, 1, 1)->modify('first monday')->addWeeks(20),
                "June - July-{$nextYear3}"           => Carbon::create($nextYear3, 1, 1)->modify('first monday')->addWeeks(24),
                "July - August-{$nextYear3}"         => Carbon::create($nextYear3, 1, 1)->modify('first monday')->addWeeks(28),
                "August - September-{$nextYear3}"    => Carbon::create($nextYear3, 1, 1)->modify('first monday')->addWeeks(32),
                "September - October-{$nextYear3}"   => Carbon::create($nextYear3, 1, 1)->modify('first monday')->addWeeks(36),
                "October - November-{$nextYear3}"    => Carbon::create($nextYear3, 1, 1)->modify('first monday')->addWeeks(40),
                "November - December-{$nextYear3}"   => Carbon::create($nextYear3, 1, 1)->modify('first monday')->addWeeks(44),
                "December - January-{$nextYear3}"    => Carbon::create($nextYear3, 1, 1)->modify('first monday')->addWeeks(48),
            ];

            function findMatchingMonth($month, $customStartDates)
            {
                foreach ($customStartDates as $key => $value) {
                    if (str_contains($key, $month)) return $key;
                }
                return null;
            }

            $intervalMap = [
                '4_weeks'  => 4,
                '8_weeks'  => 8,
                '12_weeks' => 12,
                '24_weeks' => 24,
                '52_weeks' => 52,
            ];

            $inputSchedules = [];

            foreach ($request->input('month') as $monthName => $weeks) {
                $matchedMonthKey = findMatchingMonth($monthName, $customStartDates);
                if ($matchedMonthKey === null) continue;

                foreach ($weeks as $weekIndex => $week) {
                    if (!isset($week['start_date'], $week['end_date'], $week['prices']) || empty($week['prices'])) continue;

                    if (!isset($week['note_start_date']) || empty($week['note_start_date'])) {
                        return back()->withErrors(['error' => "Please select a start date for {$monthName} - {$weekIndex}"])->withInput();
                    }

                    $additionalNotes        = $week['additional_note'] ?? [];
                    $additionalPricesNested = $week['extra_prices'] ?? [];
                    $additionalNoteTypes    = $week['extra_note_type'] ?? [];
                    $additionalNoteDates    = $week['extra_note_start_date'] ?? [];
                    $notesPriorities    = $week['priority'] ?? [];

                    $allNoteDates = array_merge(
                        [$week['note_start_date'] ?? null],
                        array_values($additionalNoteDates)
                    );

                    $allNoteTypes = array_merge(
                        [$week['note_type'] ?? null],
                        array_values($additionalNoteTypes)
                    );

                    $allNotes = array_merge(
                        [$week['note'] ?? null],
                        [$week['note_two'] ?? null],
                        array_values($additionalNotes)
                    );

                    foreach ($allNoteDates as $dateIndex => $noteDate) {
                        if (empty($noteDate)) continue;

                        $noteType      = $allNoteTypes[$dateIndex] ?? '4_weeks';
                        $intervalWeeks = $intervalMap[$noteType] ?? 4;

                        $currentPrices       = [];
                        $extraWorkPriceIds   = null;
                        $extraWorkNamesJson  = null;
                        $extraWorkValuesJson = null;

                        if ($dateIndex === 0) {
                            // Note 1 ki prices
                            $currentPrices = $week['prices'];
                        } else {
                            // Note 2,3,4,5,6 ki prices — extra_prices[$dateIndex - 1]
                            $notePrices  = $additionalPricesNested[$dateIndex - 1] ?? [];
                            $validPrices = array_filter($notePrices, fn($p) => $p && $p !== "0");

                            if (!empty($validPrices)) {
                                $priceDetails    = \App\Models\ClientPriceList::whereIn('id', $validPrices)->get();
                                $extraWorkNames  = [];
                                $extraWorkValues = [];
                                foreach ($priceDetails as $pd) {
                                    $extraWorkNames[]  = $pd->name;
                                    $extraWorkValues[] = $pd->value;
                                }
                                $extraWorkPriceIds   = json_encode(array_values($validPrices));
                                $extraWorkNamesJson  = json_encode($extraWorkNames);
                                $extraWorkValuesJson = json_encode($extraWorkValues);
                            }
                        }

                        $currentDate = Carbon::createFromFormat('Y-m-d', $noteDate);
                        $endDate     = Carbon::createFromFormat('Y-m-d', $noteDate)->addYears(3);

                        while ($currentDate->lte($endDate)) {
                            $matchedRange = null;
                            foreach ($customStartDates as $range => $startDate) {
                                if ($currentDate->gte($startDate) && $currentDate->lt($startDate->copy()->addWeeks(4))) {
                                    $matchedRange = $range;
                                    break;
                                }
                            }

                            if ($matchedRange === null) {
                                $currentDate->addWeeks($intervalWeeks);
                                continue;
                            }

                            $rangeStart        = $customStartDates[$matchedRange];
                            preg_match('/\d+/', $weekIndex, $matches);
                            $originalStartDate = $rangeStart->copy()->addWeeks($matches[0] ?? 0);
                            $originalEndDate   = $originalStartDate->copy()->addDays(6);

                            $inputSchedules[] = [
                                'month'               => $originalStartDate->format('F'),
                                'week_month'          => $monthName,
                                'week'                => $weekIndex,
                                'start_date'          => $originalStartDate->toDateString(),
                                'end_date'            => $originalEndDate->toDateString(),
                                'note'                => $allNotes[$dateIndex] ?? chr(97 + $dateIndex),
                                'note_two'            => null,
                                'note_type'           => $noteType,
                                'note_date'           => $noteDate,
                                'prices'              => $currentPrices,
                                'extra_work'          => $extraWorkNamesJson,
                                'extra_work_price'    => $extraWorkValuesJson,
                                'note_week_no'        => $dateIndex,
                                'extra_work_price_id' => $extraWorkPriceIds,
                                'priority'           => $notesPriorities[$dateIndex] ?? null,
                            ];

                            $currentDate->addWeeks($intervalWeeks);
                        }
                    }
                }
            }

            // Database operations
            $existingSchedules = ClientSchedule::where('client_id', $id)->get();

            $existingSchedulesToDelete = $existingSchedules->filter(function ($schedule) use ($inputSchedules) {
                foreach ($inputSchedules as $inputSchedule) {
                    if (
                        $schedule->month        === $inputSchedule['month'] &&
                        $schedule->week         == $inputSchedule['week'] &&
                        $schedule->start_date   === $inputSchedule['start_date'] &&
                        $schedule->end_date     === $inputSchedule['end_date'] &&
                        $schedule->note_week_no == $inputSchedule['note_week_no']
                    ) {
                        return false;
                    }
                }
                return true;
            });

            foreach ($existingSchedulesToDelete as $scheduleToDelete) {
                ClientSchedulePrice::where('schedule_id', $scheduleToDelete->id)->delete();
                $scheduleToDelete->delete();
            }

            foreach ($inputSchedules as $scheduleData) {
                $prices = $scheduleData['prices'];
                unset($scheduleData['prices']);

                $existingSchedule = ClientSchedule::where('client_id', $id)
                    ->where('month', $scheduleData['month'])
                    ->where('week', $scheduleData['week'])
                    ->where('start_date', $scheduleData['start_date'])
                    ->where('end_date', $scheduleData['end_date'])
                    ->where('note_week_no', $scheduleData['note_week_no'])
                    ->first();

                if ($existingSchedule) {
                    $existingSchedule->update($scheduleData);
                } else {
                    $existingSchedule = ClientSchedule::create(array_merge($scheduleData, ['client_id' => $id]));
                }

                if (!empty($prices)) {
                    $existingPrices = ClientSchedulePrice::where('client_id', $id)
                        ->where('schedule_id', $existingSchedule->id)
                        ->pluck('price_id')
                        ->toArray();

                    $pricesToDelete = array_diff($existingPrices, $prices);
                    ClientSchedulePrice::where('client_id', $id)
                        ->where('schedule_id', $existingSchedule->id)
                        ->whereIn('price_id', $pricesToDelete)
                        ->delete();

                    $pricesToAdd = array_diff($prices, $existingPrices);
                    foreach ($pricesToAdd as $priceId) {
                        ClientSchedulePrice::create([
                            'client_id'   => $id,
                            'schedule_id' => $existingSchedule->id,
                            'price_id'    => $priceId,
                        ]);
                    }
                }
            }
        } elseif ($client->service_frequency == 'monthly') {

            $inputSchedules = [];

            foreach ($request->input('month') as $monthName => $weeks) {
                foreach ($weeks as $weekIndex => $week) {
                    if (!isset($week['start_date'], $week['end_date'], $week['prices']) || empty($week['prices'])) {
                        continue;
                    }

                    $originalStartDate = Carbon::parse($week['start_date']);

                    for ($i = 0; $i < 39; $i++) {
                        $newStartDate = $originalStartDate->copy()->addMonthsNoOverflow($i);
                        $newEndDate = $newStartDate->copy()->addDays(6);

                        $inputSchedules[] = [
                            'month' => $newStartDate->format('F'),
                            'week_month' => $monthName,
                            'week' => $weekIndex,
                            'start_date' => $newStartDate->toDateString(),
                            'end_date' => $newEndDate->toDateString(),
                            'note' => $week['note'] ?? 'a',
                            'note_two' => $week['note_two'] ?? null,
                            'prices' => $week['prices'],
                            'extra_work_price' => $week['note_extra_price'] ?? null,
                            'priority' => $week['priority'][0]  ?? 0,
                        ];
                    }
                }
            }

            $existingSchedules = ClientSchedule::where('client_id', $id)->get();

            $existingSchedulesToDelete = $existingSchedules->filter(function ($schedule) use ($inputSchedules) {
                foreach ($inputSchedules as $inputSchedule) {
                    if (
                        $schedule->month === $inputSchedule['month'] &&
                        $schedule->week == $inputSchedule['week'] &&
                        $schedule->start_date === $inputSchedule['start_date'] &&
                        $schedule->end_date === $inputSchedule['end_date']
                    ) {
                        return false;
                    }
                }
                return true;
            });

            foreach ($existingSchedulesToDelete as $scheduleToDelete) {
                ClientSchedulePrice::where('schedule_id', $scheduleToDelete->id)->delete();
                $scheduleToDelete->delete();
            }

            foreach ($inputSchedules as $scheduleData) {
                $prices = $scheduleData['prices'];
                unset($scheduleData['prices']);

                $existingSchedule = ClientSchedule::where('client_id', $id)
                    ->where('month', $scheduleData['month'])
                    ->where('week', $scheduleData['week'])
                    ->where('start_date', $scheduleData['start_date'])
                    ->where('end_date', $scheduleData['end_date'])
                    ->first();

                if ($existingSchedule) {
                    $existingSchedule->update($scheduleData);
                } else {
                    $existingSchedule = ClientSchedule::create(array_merge($scheduleData, ['client_id' => $id]));
                }

                $existingPrices = ClientSchedulePrice::where('client_id', $id)
                    ->where('schedule_id', $existingSchedule->id)
                    ->pluck('price_id')
                    ->toArray();

                $pricesToDelete = array_diff($existingPrices, $prices);
                ClientSchedulePrice::where('client_id', $id)
                    ->where('schedule_id', $existingSchedule->id)
                    ->whereIn('price_id', $pricesToDelete)
                    ->delete();

                $pricesToAdd = array_diff($prices, $existingPrices);
                foreach ($pricesToAdd as $priceId) {
                    ClientSchedulePrice::create([
                        'client_id' => $id,
                        'schedule_id' => $existingSchedule->id,
                        'price_id' => $priceId,
                    ]);
                }
            }
        } elseif ($client->service_frequency == 'biMonthly') {

            $customStartDates = [
                "January - February" => $firstMondayOfYear->copy(),
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

            function findMatchingMonth($month, $customStartDates)
            {
                foreach ($customStartDates as $key => $value) {
                    if (str_contains($key, $month)) {
                        return $key;
                    }
                }
                return null;
            }

            $inputSchedules = [];
            $isNoteTwoNext = true;
            foreach ($request->input('month') as $monthName => $weeks) {
                $matchedMonthKey = findMatchingMonth($monthName, $customStartDates);
                if ($matchedMonthKey === null) {
                    continue;
                }

                foreach ($weeks as $weekIndex => $week) {
                    if (!isset($week['start_date'], $week['end_date'], $week['prices']) || empty($week['prices'])) {
                        continue;
                    }

                    $originalStartDate = Carbon::parse($week['start_date']);
                    $originalEndDate = Carbon::parse($week['end_date']);
                    $weekOffset = $originalStartDate->diffInDays($customStartDates[$matchedMonthKey]);

                    $inputSchedules[] = [
                        'month' => $monthName,
                        'week_month' => $monthName,
                        'week' => $weekIndex,
                        'start_date' => $originalStartDate->toDateString(),
                        'end_date' => $originalEndDate->toDateString(),
                        'note' => $week['note'] ?? 'a',
                        'note_two' => $week['note_two'] ?? null,
                        'prices' => $week['prices'],
                        'extra_work_price' => $week['note_extra_price'] ?? null,
                        'priority' => $week['priority'][0] ?? 0,
                    ];
                    for ($i = 0; $i < 39; $i++) {
                        $newStartDate = $originalStartDate->copy()->addMonthsNoOverflow($i);
                        $newEndDate = $newStartDate->copy()->addDays(6);

                        $inputSchedules[] = [
                            'month' => $newStartDate->format('F'),
                            'week_month' => $monthName,
                            'week' => $weekIndex,
                            'start_date' => $newStartDate->toDateString(),
                            'end_date' => $newEndDate->toDateString(),
                            'note' => $week['note'] ?? '.',
                            'note_two' => $week['note_two'] ?? null,
                            'prices' => $week['prices'],
                            'extra_work_price' => $week['note_extra_price'] ?? null,
                            'priority' => $week['priority'][0] ?? 0,
                        ];
                    }
                }
            }

            $existingSchedules = ClientSchedule::where('client_id', $id)->get();

            $existingSchedulesToDelete = $existingSchedules->filter(function ($schedule) use ($inputSchedules) {
                foreach ($inputSchedules as $inputSchedule) {
                    if (
                        $schedule->month === $inputSchedule['month'] &&
                        $schedule->week == $inputSchedule['week'] &&
                        $schedule->start_date === $inputSchedule['start_date'] &&
                        $schedule->end_date === $inputSchedule['end_date']
                    ) {
                        return false;
                    }
                }
                return true;
            });

            foreach ($existingSchedulesToDelete as $scheduleToDelete) {
                ClientSchedulePrice::where('schedule_id', $scheduleToDelete->id)->delete();
                $scheduleToDelete->delete();
            }

            foreach ($inputSchedules as $scheduleData) {
                $prices = $scheduleData['prices'];
                unset($scheduleData['prices']);

                $existingSchedule = ClientSchedule::where('client_id', $id)
                    ->where('month', $scheduleData['month'])
                    ->where('week', $scheduleData['week'])
                    ->where('start_date', $scheduleData['start_date'])
                    ->where('end_date', $scheduleData['end_date'])
                    ->first();

                if ($existingSchedule) {
                    $existingSchedule->update($scheduleData);
                } else {
                    $existingSchedule = ClientSchedule::create(array_merge($scheduleData, ['client_id' => $id]));
                }

                $existingPrices = ClientSchedulePrice::where('client_id', $id)
                    ->where('schedule_id', $existingSchedule->id)
                    ->pluck('price_id')
                    ->toArray();

                $pricesToDelete = array_diff($existingPrices, $prices);
                ClientSchedulePrice::where('client_id', $id)
                    ->where('schedule_id', $existingSchedule->id)
                    ->whereIn('price_id', $pricesToDelete)
                    ->delete();

                $pricesToAdd = array_diff($prices, $existingPrices);
                foreach ($pricesToAdd as $priceId) {
                    ClientSchedulePrice::create([
                        'client_id' => $id,
                        'schedule_id' => $existingSchedule->id,
                        'price_id' => $priceId,
                    ]);
                }
            }
        } elseif ($client->service_frequency == 'eightWeek') {

            $dates = [];
            $allWeeksData = []; // ✅ Store all weeks data for later use

            foreach ($request->input('month') as $monthName => $weeks) {
                foreach ($weeks as $weekIndex => $week) {
                    if (!isset($week['start_date'], $week['end_date'], $week['prices']) || empty($week['prices'])) {
                        continue;
                    }

                    // ✅ Store week data for later use
                    $allWeeksData[$weekIndex] = $week;

                    $adjustedStartDate = Carbon::parse($week['start_date']);

                    if ($adjustedStartDate->dayOfWeek !== Carbon::MONDAY) {
                        $adjustedStartDate->previous(Carbon::MONDAY);
                    }

                    $currentDate = clone $adjustedStartDate;

                    $endOfPeriod = Carbon::createFromFormat('Y-m-d', $week['start_date'])->addMonths(13)->endOfMonth();

                    while ($currentDate->lessThanOrEqualTo($endOfPeriod)) {
                        $endDate = (clone $currentDate)->addDays(6);
                        $dates[$weekIndex]['week_month'] = $monthName;
                        $dates[$weekIndex]['dates'][] = [
                            'start_date' => $currentDate->format('Y-m-d'),
                            'end_date' => $endDate->format('Y-m-d')
                        ];

                        $currentDate = (clone $currentDate)->addWeeks(8);
                    }
                }
            }

            $inputSchedules = [];

            foreach ($dates as $index => $weekDate) {
                // ✅ Get week data from stored array
                $weekData = $allWeeksData[$index] ?? null;

                if (!$weekData) {
                    continue;
                }

                foreach ($weekDate['dates'] as $date) {
                    $inputSchedules[] = [
                        'month' => Carbon::parse($date['start_date'])->format('F'),
                        'week_month' => $weekDate['week_month'],
                        'week' => $index,
                        'start_date' => $date['start_date'],
                        'end_date' => $date['end_date'],
                        'note' => $weekData['note'] ?? 'a',
                        'note_two' => $weekData['note_two'] ?? null,
                        'prices' => $weekData['prices'],
                        'extra_work_price' => null,
                        'note_week_no' => 0,
                    ];
                }
            }

            // ✅ FIX: Delete schedules that are no longer in the form
            // Get all existing schedules for this client
            $existingSchedules = ClientSchedule::where('client_id', $id)->get();

            $existingSchedulesToDelete = $existingSchedules->filter(function ($schedule) use ($inputSchedules) {
                foreach ($inputSchedules as $inputSchedule) {
                    if (
                        $schedule->month === $inputSchedule['month'] &&
                        $schedule->week == $inputSchedule['week'] &&
                        $schedule->start_date === $inputSchedule['start_date'] &&
                        $schedule->end_date === $inputSchedule['end_date']
                    ) {
                        return false; // Keep this schedule
                    }
                }
                return true; // Delete this schedule (not in form data)
            });

            foreach ($existingSchedulesToDelete as $scheduleToDelete) {
                ClientSchedulePrice::where('schedule_id', $scheduleToDelete->id)->delete();
                $scheduleToDelete->delete();
            }

            foreach ($inputSchedules as $scheduleData) {
                $prices = $scheduleData['prices'];
                unset($scheduleData['prices']);

                $existingSchedule = ClientSchedule::where('client_id', $id)
                    ->where('month', $scheduleData['month'])
                    ->where('week', $scheduleData['week'])
                    ->where('start_date', $scheduleData['start_date'])
                    ->where('end_date', $scheduleData['end_date'])
                    ->first();

                if ($existingSchedule) {
                    $existingSchedule->update($scheduleData);
                } else {
                    $existingSchedule = ClientSchedule::create(array_merge($scheduleData, ['client_id' => $id]));
                }

                $existingPrices = ClientSchedulePrice::where('client_id', $id)
                    ->where('schedule_id', $existingSchedule->id)
                    ->pluck('price_id')
                    ->toArray();

                $pricesToDelete = array_diff($existingPrices, $prices);
                ClientSchedulePrice::where('client_id', $id)
                    ->where('schedule_id', $existingSchedule->id)
                    ->whereIn('price_id', $pricesToDelete)
                    ->delete();

                $pricesToAdd = array_diff($prices, $existingPrices);
                foreach ($pricesToAdd as $priceId) {
                    ClientSchedulePrice::create([
                        'client_id' => $id,
                        'schedule_id' => $existingSchedule->id,
                        'price_id' => $priceId,
                    ]);
                }
            }
        } elseif ($client->service_frequency == 'quarterly') {

            $dates = [];
            $allWeeksData = []; // ✅ Store all weeks data for later use

            foreach ($request->input('month') as $monthName => $weeks) {
                foreach ($weeks as $weekIndex => $week) {
                    if (!isset($week['start_date'], $week['end_date'], $week['prices']) || empty($week['prices'])) {
                        continue;
                    }

                    // ✅ Store week data for later use
                    $allWeeksData[$weekIndex] = $week;

                    $adjustedStartDate = Carbon::parse($week['start_date']);

                    if ($adjustedStartDate->dayOfWeek !== Carbon::MONDAY) {
                        $adjustedStartDate->previous(Carbon::MONDAY);
                    }

                    $currentDate = clone $adjustedStartDate;

                    $endOfPeriod = Carbon::createFromFormat('Y-m-d', $week['start_date'])->addMonths(13)->endOfMonth();

                    while ($currentDate->lessThanOrEqualTo($endOfPeriod)) {
                        $endDate = (clone $currentDate)->addDays(6);
                        $dates[$weekIndex]['week_month'] = $monthName;
                        $dates[$weekIndex]['dates'][] = [
                            'start_date' => $currentDate->format('Y-m-d'),
                            'end_date' => $endDate->format('Y-m-d')
                        ];

                        $currentDate = (clone $currentDate)->addWeeks(12); // ✅ 12 weeks for quarterly
                    }
                }
            }

            $inputSchedules = [];

            foreach ($dates as $index => $weekDate) {
                // ✅ Get week data from stored array
                $weekData = $allWeeksData[$index] ?? null;

                if (!$weekData) {
                    continue;
                }

                foreach ($weekDate['dates'] as $date) {
                    $inputSchedules[] = [
                        'month' => Carbon::parse($date['start_date'])->format('F'),
                        'week_month' => $weekDate['week_month'],
                        'week' => $index,
                        'start_date' => $date['start_date'],
                        'end_date' => $date['end_date'],
                        'note' => $weekData['note'] ?? 'a',
                        'note_two' => $weekData['note_two'] ?? null,
                        'prices' => $weekData['prices'],
                        'extra_work_price' => null,
                        'note_week_no' => 0,
                    ];
                }
            }

            // ✅ FIX: Delete schedules that are no longer in the form
            // Get all existing schedules for this client
            $existingSchedules = ClientSchedule::where('client_id', $id)->get();

            $existingSchedulesToDelete = $existingSchedules->filter(function ($schedule) use ($inputSchedules) {
                foreach ($inputSchedules as $inputSchedule) {
                    if (
                        $schedule->month === $inputSchedule['month'] &&
                        $schedule->week == $inputSchedule['week'] &&
                        $schedule->start_date === $inputSchedule['start_date'] &&
                        $schedule->end_date === $inputSchedule['end_date']
                    ) {
                        return false; // Keep this schedule
                    }
                }
                return true; // Delete this schedule (not in form data)
            });

            foreach ($existingSchedulesToDelete as $scheduleToDelete) {
                ClientSchedulePrice::where('schedule_id', $scheduleToDelete->id)->delete();
                $scheduleToDelete->delete();
            }

            foreach ($inputSchedules as $scheduleData) {
                $prices = $scheduleData['prices'];
                unset($scheduleData['prices']);

                $existingSchedule = ClientSchedule::where('client_id', $id)
                    ->where('month', $scheduleData['month'])
                    ->where('week', $scheduleData['week'])
                    ->where('start_date', $scheduleData['start_date'])
                    ->where('end_date', $scheduleData['end_date'])
                    ->first();

                if ($existingSchedule) {
                    $existingSchedule->update($scheduleData);
                } else {
                    $existingSchedule = ClientSchedule::create(array_merge($scheduleData, ['client_id' => $id]));
                }

                $existingPrices = ClientSchedulePrice::where('client_id', $id)
                    ->where('schedule_id', $existingSchedule->id)
                    ->pluck('price_id')
                    ->toArray();

                $pricesToDelete = array_diff($existingPrices, $prices);
                ClientSchedulePrice::where('client_id', $id)
                    ->where('schedule_id', $existingSchedule->id)
                    ->whereIn('price_id', $pricesToDelete)
                    ->delete();

                $pricesToAdd = array_diff($prices, $existingPrices);
                foreach ($pricesToAdd as $priceId) {
                    ClientSchedulePrice::create([
                        'client_id' => $id,
                        'schedule_id' => $existingSchedule->id,
                        'price_id' => $priceId,
                    ]);
                }
            }
        } elseif ($client->service_frequency == 'annually') {

            $customStartDates = [
                "January - February" => $firstMondayOfYear->copy(),
                "February - March"   => $firstMondayOfYear->copy()->addWeeks(4),
                "March"              => $firstMondayOfYear->copy()->addWeeks(8),
                "March - April"      => $firstMondayOfYear->copy()->addWeeks(12),
                "April - May"        => $firstMondayOfYear->copy()->addWeeks(16),
                "May - June"         => $firstMondayOfYear->copy()->addWeeks(20),
                "June - July"        => $firstMondayOfYear->copy()->addWeeks(24),
                "July - August"      => $firstMondayOfYear->copy()->addWeeks(28),
                "August - September" => $firstMondayOfYear->copy()->addWeeks(32),
                "September - October" => $firstMondayOfYear->copy()->addWeeks(36),
                "October - November" => $firstMondayOfYear->copy()->addWeeks(40),
                "November - December" => $firstMondayOfYear->copy()->addWeeks(44),
                "December - January" => $firstMondayOfYear->copy()->addWeeks(48),
            ];

            function findMatchingMonth($month, $customStartDates)
            {
                foreach ($customStartDates as $key => $value) {
                    if (str_contains($key, $month)) return $key;
                }
                return null;
            }

            $inputSchedules = [];

            foreach ($request->input('month') as $monthName => $weeks) {
                $matchedMonthKey = findMatchingMonth($monthName, $customStartDates);
                if ($matchedMonthKey === null) continue;

                foreach ($weeks as $weekIndex => $week) {
                    if (!isset($week['start_date'], $week['end_date'], $week['prices']) || empty($week['prices'])) {
                        continue;
                    }

                    $originalStartDate = Carbon::parse($week['start_date']);
                    $originalEndDate   = Carbon::parse($week['end_date']);

                    // 3 saal = 3 entries, har saal same date
                    for ($i = 0; $i < 3; $i++) {
                        $newStartDate = $originalStartDate->copy()->addYears($i);
                        $newEndDate   = $originalEndDate->copy()->addYears($i);

                        $inputSchedules[] = [
                            'month'            => $newStartDate->format('F'),
                            'week_month'       => $monthName,
                            'week'             => $weekIndex,
                            'start_date'       => $newStartDate->toDateString(),
                            'end_date'         => $newEndDate->toDateString(),
                            'note'             => $week['note'] ?? 'a',
                            'note_two'         => $week['note_two'] ?? null,
                            'prices'           => $week['prices'],
                            'extra_work_price' => $week['note_extra_price'] ?? null,
                            'priority' => $week['priority'][0] ?? 0,
                        ];
                    }
                }
            }

            $existingSchedules = ClientSchedule::where('client_id', $id)->get();

            $existingSchedulesToDelete = $existingSchedules->filter(function ($schedule) use ($inputSchedules) {
                foreach ($inputSchedules as $inputSchedule) {
                    if (
                        $schedule->month      === $inputSchedule['month'] &&
                        $schedule->week       == $inputSchedule['week'] &&
                        $schedule->start_date === $inputSchedule['start_date'] &&
                        $schedule->end_date   === $inputSchedule['end_date']
                    ) {
                        return false;
                    }
                }
                return true;
            });

            foreach ($existingSchedulesToDelete as $scheduleToDelete) {
                ClientSchedulePrice::where('schedule_id', $scheduleToDelete->id)->delete();
                $scheduleToDelete->delete();
            }

            foreach ($inputSchedules as $scheduleData) {
                $prices = $scheduleData['prices'];
                unset($scheduleData['prices']);

                $existingSchedule = ClientSchedule::where('client_id', $id)
                    ->where('month', $scheduleData['month'])
                    ->where('week', $scheduleData['week'])
                    ->where('start_date', $scheduleData['start_date'])
                    ->where('end_date', $scheduleData['end_date'])
                    ->first();

                if ($existingSchedule) {
                    $existingSchedule->update($scheduleData);
                } else {
                    $existingSchedule = ClientSchedule::create(array_merge($scheduleData, ['client_id' => $id]));
                }

                $existingPrices = ClientSchedulePrice::where('client_id', $id)
                    ->where('schedule_id', $existingSchedule->id)
                    ->pluck('price_id')
                    ->toArray();

                $pricesToDelete = array_diff($existingPrices, $prices);
                ClientSchedulePrice::where('client_id', $id)
                    ->where('schedule_id', $existingSchedule->id)
                    ->whereIn('price_id', $pricesToDelete)
                    ->delete();

                $pricesToAdd = array_diff($prices, $existingPrices);
                foreach ($pricesToAdd as $priceId) {
                    ClientSchedulePrice::create([
                        'client_id'   => $id,
                        'schedule_id' => $existingSchedule->id,
                        'price_id'    => $priceId,
                    ]);
                }
            }
        }

        if ($status == 1) {
            Notification::create([
                'user_id' => 2,
                'action_id' => 2,
                'title' => $client->name . ' Schedule Updated',
                'message' => 'Client schedule has been updated.',
                'type' => 'client_schedule_updated',
            ]);
        } else {
            if ($client->staff_id) {
                Notification::create([
                    'user_id' => $client->staff_id,
                    'action_id' => 2,
                    'title' => 'Client Schedule Updated',
                    'message' => 'Schedule for client ' . $client->name . ' has been updated.',
                    'type' => 'staff_client_schedule_updated',
                ]);
            }
        }
        $client = Client::find($client->id);
        $client->update(['schedule' => 'assigned', 'updated_at' => now()]);

        if ($request->has('stay_on_page') && $request->input('stay_on_page') == 1) {
            return redirect()->route('client-schedule', ['id' => $client->id])->with(['title' => 'Done', 'message' => 'Client Schedule Updated Successfully.', 'type' => 'success', 'key' => 'completed']);
        }

        return redirect()->route('clients.index')->with(['title' => 'Done', 'message' => 'Client Schedule Updated Successfully.', 'type' => 'success', 'key' => 'completed']);
    }

    public function checkEmail(Request $request)
    {
        $email = $request->input('email');
        $exists = User::where('email', $email)->exists();

        return response()->json(['exists' => $exists]);
    }

    // public function checkClientName(Request $request)
    // {
    //     $name = $request->input('name');
    //     $type = $request->input('type');

    //     if ($type == 'edit') {
    //         $clientId = $request->input('client_id');

    //         // If client_id is empty, return false
    //         if (empty($clientId)) {
    //             return response()->json([
    //                 'exists' => false,
    //                 'debug' => 'client_id empty'
    //             ]);
    //         }

    //         // Get current client's name
    //         $currentClient = Client::find($clientId);
    //         $currentClientName = $currentClient ? $currentClient->name : 'NOT FOUND';

    //         // Find all clients with this name (excluding current client)
    //         $otherClients = Client::where('name', $name)
    //             ->whereNull('deleted_at')
    //             ->where('id', '!=', $clientId) // ✅ CLIENT ID se compare karo
    //             ->get(['id', 'name']);

    //         // Check if name exists for a DIFFERENT client
    //         $exists = $otherClients->count() > 0;

    //         return response()->json([
    //             'exists' => $exists,
    //             'debug' => 'edit mode',
    //             'client_id' => $clientId,
    //             'current_client_name' => $currentClientName,
    //             'checking_name' => $name,
    //             'other_clients_with_same_name' => $otherClients
    //         ]);
    //     } else {
    //         // Create mode - simple check
    //         $exists = Client::where('name', $name)
    //             ->whereNull('deleted_at')
    //             ->exists();

    //         return response()->json([
    //             'exists' => $exists,
    //             'debug' => 'create mode'
    //         ]);
    //     }
    // }

    public function checkClientName(Request $request)
    {
        $name = $request->input('name');
        $email = $request->input('contact_email');
        $phone = $request->input('phone_number');
        $type = $request->input('type');

        $cleanPhone = !empty($phone) ? preg_replace('/[^0-9]/', '', $phone) : null;

        if ($type == 'edit') {
            $clientId = $request->input('client_id');

            if (empty($clientId)) {
                return response()->json([
                    'exists' => false,
                    'debug' => 'client_id empty'
                ]);
            }

            $currentClient = Client::find($clientId);
            $currentClientName = $currentClient ? $currentClient->name : 'NOT FOUND';

            $nameExists = Client::where('name', $name)
                ->whereNull('deleted_at')
                ->where('id', '!=', $clientId)
                ->exists();

            $emailExists = false;
            if (!empty($email)) {
                $emailExists = Client::where('contact_email', $email)
                    ->whereNull('deleted_at')
                    ->where('id', '!=', $clientId)
                    ->exists();

                if (!$emailExists) {
                    $profiles = Profile::whereNotNull('additional_emails')
                        ->where('client_id', '!=', $clientId)
                        ->get();

                    foreach ($profiles as $profile) {
                        $additionalEmails = $profile->additional_emails; // Uses accessor
                        if (is_array($additionalEmails) && in_array($email, $additionalEmails)) {
                            $emailExists = true;
                            break;
                        }
                    }
                }
            }

            $phoneExists = false;
            if (!empty($cleanPhone)) {
                $clients = Client::whereNull('deleted_at')
                    ->where('id', '!=', $clientId)
                    ->whereNotNull('contact_phone')
                    ->get();

                foreach ($clients as $client) {
                    $dbCleanPhone = preg_replace('/[^0-9]/', '', $client->contact_phone);
                    if ($dbCleanPhone === $cleanPhone) {
                        $phoneExists = true;
                        break;
                    }
                }

                if (!$phoneExists) {
                    $profiles = Profile::whereNotNull('additional_phones')
                        ->where('client_id', '!=', $clientId)
                        ->get();

                    foreach ($profiles as $profile) {
                        $additionalPhones = $profile->additional_phones; // Uses accessor
                        if (is_array($additionalPhones)) {
                            foreach ($additionalPhones as $additionalPhone) {
                                $dbCleanPhone = preg_replace('/[^0-9]/', '', $additionalPhone);
                                if ($dbCleanPhone === $cleanPhone) {
                                    $phoneExists = true;
                                    break 2;
                                }
                            }
                        }
                    }
                }
            }

            return response()->json([
                'name_exists' => $nameExists,
                'email_exists' => $emailExists,
                'phone_exists' => $phoneExists,
                'debug' => 'edit mode',
                'client_id' => $clientId,
                'current_client_name' => $currentClientName,
                'received_email' => $email,
                'received_phone' => $phone,
                'clean_phone' => $cleanPhone
            ]);
        } else {
            $nameExists = Client::where('name', $name)
                ->whereNull('deleted_at')
                ->exists();

            $emailExists = false;
            if (!empty($email)) {
                $emailExists = Client::where('contact_email', $email)
                    ->whereNull('deleted_at')
                    ->exists();

                if (!$emailExists) {
                    $profiles = Profile::whereNotNull('additional_emails')->get();

                    foreach ($profiles as $profile) {
                        $additionalEmails = $profile->additional_emails;
                        if (is_array($additionalEmails) && in_array($email, $additionalEmails)) {
                            $emailExists = true;
                            break;
                        }
                    }
                }
            }

            $phoneExists = false;
            if (!empty($cleanPhone)) {
                $clients = Client::whereNull('deleted_at')
                    ->whereNotNull('contact_phone')
                    ->get();

                foreach ($clients as $client) {
                    $dbCleanPhone = preg_replace('/[^0-9]/', '', $client->contact_phone);
                    if ($dbCleanPhone === $cleanPhone) {
                        $phoneExists = true;
                        break;
                    }
                }

                if (!$phoneExists) {
                    $profiles = Profile::whereNotNull('additional_phones')->get();

                    foreach ($profiles as $profile) {
                        $additionalPhones = $profile->additional_phones;
                        if (is_array($additionalPhones)) {
                            foreach ($additionalPhones as $additionalPhone) {
                                $dbCleanPhone = preg_replace('/[^0-9]/', '', $additionalPhone);
                                if ($dbCleanPhone === $cleanPhone) {
                                    $phoneExists = true;
                                    break 2;
                                }
                            }
                        }
                    }
                }
            }

            return response()->json([
                'name_exists' => $nameExists,
                'email_exists' => $emailExists,
                'phone_exists' => $phoneExists,
                'debug' => 'create mode',
                'received_email' => $email,
                'received_phone' => $phone,
                'clean_phone' => $cleanPhone
            ]);
        }
    }

    public function routeNameCheck(Request $request)
    {
        $route = $request->input('name');
        $exists = StaffRoute::where('name', $route)->exists();

        return response()->json(['exists' => $exists]);
    }

    public function requirementStatus(Request $request)
    {
        $requests = StaffRequirement::where('staff_id', $request->staff_id)->where('timestamp', $request->timestamp)->get();
        foreach ($requests as $request) {
            $request->update(['status' => 'completed']);
        }

        Notification::create([
            'user_id' => $request->staff_id,
            'action_id' => $request->staff_id,
            'title' => 'Your Request Completed',
            'message' => 'Your request has been completed.',
            'type' => 'staff_request_completed',
        ]);

        return redirect()->route('staff-request')->with(['title' => 'Done', 'message' => 'Staff Request Status Updated Successfully.', 'type' => 'success', 'key' => 'completed']);
    }

    public function saveContactUs(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'             => 'required|string|max:255',
                'email'            => 'required|email|max:255',
                'phone'            => 'nullable|string|max:20',
                'subject'          => 'nullable|string|max:255',
                'message'          => 'nullable|string',
                'property_status'  => 'nullable|string|max:255',
                'address'          => 'nullable|string|max:500',
                'street_number'    => 'nullable|string|max:50',
                'city'             => 'nullable|string|max:100',
                'zip_code'         => 'nullable|string|max:20',
                'cleaning_side'    => 'nullable|array',
                'type'             => 'nullable|array',
                'image'            => 'nullable|array',
            ]);

            Log::info('Validation passed');

            DB::beginTransaction();

            $quote = Contact::create([
                'name'            => $validated['name'],
                'email'           => $validated['email'],
                'phone'           => str_replace(['-', ' ', '(', ')'], '', $validated['phone']),
                'subject'         => $validated['subject'],
                'property_status' => $validated['property_status'] ?? null,
                'address'         => $validated['address'] ?? null,
                'street_number'   => $validated['street_number'] ?? null,
                'city'            => $validated['city'] ?? null,
                'zip_code'        => $validated['zip_code'] ?? null,
                'message'         => $validated['message'],
            ]);

            $cleaningSide = $request->input('cleaning_side', []);
            if (!empty($cleaningSide)) {
                if (!is_array($cleaningSide)) {
                    $cleaningSide = [$cleaningSide];
                }
                foreach ($cleaningSide as $cleaningOption) {
                    if (!empty($cleaningOption)) {
                        ContactCleaning::create([
                            'contact_id'   => $quote->id,
                            'cleaning_side' => $cleaningOption,
                        ]);
                    }
                }
            }

            $types = $request->input('type', []);
            if (!empty($types)) {
                if (!is_array($types)) {
                    $types = [$types];
                }
                foreach ($types as $typeOption) {
                    if (!empty($typeOption)) {
                        ContactSiding::create([
                            'contact_id' => $quote->id,
                            'type'       => $typeOption,
                        ]);
                    }
                }
            }

            $images = $request->input('image', []);
            if (!empty($images) && is_array($images)) {
                foreach ($images as $base64Image) {
                    if (!empty($base64Image)) {
                        try {
                            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));
                            if ($imageData === false) {
                                Log::warning('Failed to decode base64 image for contact', ['contact_id' => $quote->id]);
                                continue;
                            }
                            $filename = 'image_' . uniqid() . '_' . time() . '.png';
                            $filePath = 'quote_image/' . $filename;
                            Storage::disk('website')->put($filePath, $imageData);
                            ContactImage::create([
                                'contact_id' => $quote->id,
                                'image'      => $filePath,
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Failed to save contact image', [
                                'contact_id' => $quote->id,
                                'error'      => $e->getMessage(),
                            ]);
                        }
                    }
                }
            }

            Notification::create([
                'user_id'   => 2,
                'action_id' => 2,
                'title'     => 'New Quote Request',
                'message'   => 'A new quote request has been submitted by ' . $validated['name'] . '.',
                'type'      => 'new_quote_request',
            ]);

            DB::commit();

            try {
                $emailData = [
                    'name'     => $validated['name'],
                    'email'    => $validated['email'],
                    'phone'    => $validated['phone'],
                    'subject'  => $validated['subject'],
                    'url'      => env('APP_URL'),
                    'site_url' => env('APP_URL'),
                ];
                Mail::send('website.email_templates.contact_quote_template', ['data' => $emailData], function ($message) use ($emailData) {
                    $message->to('varnum4@gmail.com', 'Admin')
                        ->subject('New Quote Request: ' . $emailData['subject']);
                });
            } catch (\Exception $e) {
                Log::error('Failed to send contact quote email', [
                    'contact_id' => $quote->id,
                    'error'      => $e->getMessage(),
                ]);
            }

            Log::info('Contact quote submitted successfully', [
                'contact_id' => $quote->id,
                'name'       => $validated['name'],
                'email'      => $validated['email'],
            ]);

            return redirect()->route('contact_us')->with([
                'title'   => 'Done',
                'message' => 'Quote Submitted Successfully.',
                'type'    => 'success',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return back()->withInput()->with([
                'title'   => 'Error',
                'message' => $e->getMessage(),
                'type'    => 'error',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with([
                'title'   => 'Error',
                'message' => 'Failed to submit quote. Please try again later.',
                'type'    => 'error',
            ]);
        }
    }

    public function checkEmailQuote(Request $request)
    {
        $email = $request->input('email');
        $exists = Contact::where('email', $email)->exists();

        return response()->json(['exists' => $exists]);
    }

    public function saveTestimonial(Request $request)
    {
        Testimonial::create(['name' => $request->name, 'message' => $request->message]);

        Notification::create([
            'user_id' => 2,
            'action_id' => 2,
            'title' => 'New Testimonial',
            'message' => 'A new testimonial has been submitted.',
            'type' => 'new_testimonial',
        ]);

        return redirect()->back()->with(['title' => 'Done', 'message' => 'Testimonial Submitted Successfully.', 'type' => 'success',]);
    }

    public function testimonialStatus(Request $request)
    {
        $testimonial = Testimonial::find($request->user_id);
        if ($testimonial) {
            $testimonial->update(['status' => 'accepted']);
        }

        Notification::create([
            'user_id' => 2,
            'action_id' => 2,
            'title' => 'Testimonial Accepted',
            'message' => 'A testimonial has been accepted.',
            'type' => 'testimonial_accepted',
        ]);

        return redirect()->route('testimonials.index')->with(['title' => 'Done', 'message' => 'Testimonial Request Accepted Successfully.', 'type' => 'success', 'key' => 'accepted']);
    }

    public function cmsHome(Request $request)
    {
        $data = $request->all();

        if ($request->hasFile('section_two_image_one')) {
            $data['section_two_image_one'] = $this->storeImage('cms', $request->section_two_image_one);
        }

        if ($request->hasFile('section_two_image_two')) {
            $data['section_two_image_two'] = $this->storeImage('cms', $request->section_two_image_two);
        }

        if ($request->hasFile('two_sub_section_one_icon')) {
            $data['two_sub_section_one_icon'] = $this->storeImage('cms', $request->two_sub_section_one_icon);
        }

        if ($request->hasFile('two_sub_section_two_icon')) {
            $data['two_sub_section_two_icon'] = $this->storeImage('cms', $request->two_sub_section_two_icon);
        }

        if ($request->hasFile('three_sub_section_one_image')) {
            $data['three_sub_section_one_image'] = $this->storeImage('cms', $request->three_sub_section_one_image);
        }

        $data['two_sub_section_one_title'] = isset($data['two_sub_section_one_title'])
            ? json_encode($data['two_sub_section_one_title'])
            : null;

        $data['two_sub_section_two_title'] = isset($data['two_sub_section_two_title'])
            ? json_encode($data['two_sub_section_two_title'])
            : null;

        CmsHome::updateOrCreate(
            ['id' => 1],
            $data
        );

        Notification::create([
            'user_id' => 2,
            'action_id' => 2,
            'title' => 'CMS Home Updated',
            'message' => 'The home page has been updated successfully.',
            'type' => 'cms_home_updated',
        ]);

        return redirect()->route('cms')->with(['title' => 'Done', 'message' => 'Home Updated Successfully.', 'type' => 'success', 'key' => 'home']);
    }

    public function cmsAbout(Request $request)
    {
        $data = $request->all();

        if ($request->hasFile('section_one_image')) {
            $data['section_one_image'] = $this->storeImage('cms', $request->section_one_image);
        }

        if ($request->hasFile('two_sub_section_one_image')) {
            $data['two_sub_section_one_image'] = $this->storeImage('cms', $request->two_sub_section_one_image);
        }

        if ($request->hasFile('two_sub_section_two_image')) {
            $data['two_sub_section_two_image'] = $this->storeImage('cms', $request->two_sub_section_two_image);
        }

        if ($request->hasFile('two_sub_section_three_image')) {
            $data['two_sub_section_three_image'] = $this->storeImage('cms', $request->two_sub_section_three_image);
        }

        if ($request->hasFile('two_sub_section_four_image')) {
            $data['two_sub_section_four_image'] = $this->storeImage('cms', $request->two_sub_section_four_image);
        }

        if ($request->hasFile('two_sub_section_five_image')) {
            $data['two_sub_section_five_image'] = $this->storeImage('cms', $request->two_sub_section_five_image);
        }

        CmsAbout::updateOrCreate(
            ['id' => 1],
            $data
        );

        return redirect()->route('cms')->with(['title' => 'Done', 'message' => 'About Us Updated Successfully.', 'type' => 'success', 'key' => 'about']);
    }

    public function cmsService(Request $request)
    {
        $data = $request->all();

        if ($request->hasFile('section_one_image')) {
            $data['section_one_image'] = $this->storeImage('cms', $request->section_one_image);
        }
        if ($request->hasFile('section_two_image')) {
            $data['section_two_image'] = $this->storeImage('cms', $request->section_two_image);
        }

        CmsService::updateOrCreate(
            ['id' => 1],
            $data
        );

        Notification::create([
            'user_id' => 2,
            'action_id' => 2,
            'title' => 'CMS Service Updated',
            'message' => 'The services page has been updated successfully.',
            'type' => 'cms_service_updated',
        ]);

        return redirect()->route('cms')->with(['title' => 'Done', 'message' => 'Services Updated Successfully.', 'type' => 'success', 'key' => 'service']);
    }

    public function cmsBlog(Request $request)
    {
        $blogIdsInRequest = collect($request->input('blogs', []))->pluck('id')->filter();
        $existingBlogs = CmsBlog::whereNotIn('id', $blogIdsInRequest)->get();
        $blogsToDelete = $existingBlogs->pluck('id');

        BlogAttachment::whereIn('blog_id', $blogsToDelete)->delete();
        CmsBlog::whereIn('id', $blogsToDelete)->delete();

        $updatedBlogs = [];
        foreach ($request->input('blogs', []) as $key => $blog) {
            $blog_id = $blog['id'] ?? null;

            $cmsBlog = CmsBlog::updateOrCreate(
                ['id' => $blog_id],
                [
                    'heading' => $blog['heading'] ?? null,
                    'description' => $blog['description'] ?? null,
                ]
            );
            $updatedBlogs[$cmsBlog->id] = $cmsBlog;

            if (!empty($request->blogs[$key]['image']) && is_array($request->blogs[$key]['image'])) {
                foreach ($request->blogs[$key]['image'] as $image) {
                    if ($image) {
                        $imagePath = $this->storeImage('cms', $image);
                        BlogAttachment::create([
                            'blog_id' => $cmsBlog->id,
                            'image' => $imagePath,
                        ]);
                    }
                }
            }
        }
        Notification::create([
            'user_id' => 2,
            'action_id' => 2,
            'title' => 'CMS Blog Updated',
            'message' => 'The blog page has been updated successfully.',
            'type' => 'cms_blog_updated',
        ]);

        return redirect()->route('cms')->with(['title' => 'Done', 'message' => 'Blogs Updated Successfully.', 'type' => 'success', 'key' => 'blog',]);
    }

    public function cmsContact(Request $request)
    {
        $data = $request->all();

        if ($request->hasFile('section_one_icon')) {
            $data['section_one_icon'] = $this->storeImage('cms', $request->section_one_icon);
        }

        if ($request->hasFile('section_two_icon')) {
            $data['section_two_icon'] = $this->storeImage('cms', $request->section_two_icon);
        }

        CmsContact::updateOrCreate(
            ['id' => 1],
            $data
        );

        Notification::create([
            'user_id' => 2,
            'action_id' => 2,
            'title' => 'CMS Contact Updated',
            'message' => 'The contact us page has been updated successfully.',
            'type' => 'cms_contact_updated',
        ]);

        return redirect()->route('cms')->with(['title' => 'Done', 'message' => 'Contact Us Updated Successfully.', 'type' => 'success', 'key' => 'contact']);
    }

    public function staffAcceptStatus(Request $request, $client_id)
    {
        $client = Client::find($client_id);
        if ($client) {
            $client->status = 1;
            $client->commission_percentage = $request->commission_percentage ?? null;
            $client->save();

            // If accept_branches is 1, also accept all child clients (branches)
            if ($request->accept_branches == '1') {
                $childClients = Client::where('user_id', $client->user_id)
                    ->where('is_child', true)
                    ->get();

                foreach ($childClients as $childClient) {
                    $childClient->status = 1;
                    $childClient->commission_percentage = $request->commission_percentage ?? null;
                    $childClient->save();
                }

                $branchCount = $childClients->count();
                $message = "Staff Request Accepted with {$branchCount} branch(es).";
            } else {
                $message = 'Staff Request Accepted.';
            }

            // Send notification to admin
            Notification::create([
                'user_id' => 2,
                'action_id' => 2,
                'title' => 'Staff Request Accepted',
                'message' => 'A staff request has been accepted.',
                'type' => 'staff_request_accepted',
            ]);

            // Send notification to staff if staff_id exists
            if ($client->staff_id) {
                Notification::create([
                    'user_id' => $client->staff_id,
                    'action_id' => 2,
                    'title' => 'Admin has Accepted your Client Request Accepted',
                    'message' => 'Your client request has been accepted.',
                    'type' => 'staff_client_accepted',
                ]);
            }

            return redirect()->route('clients.index')->with(['title' => 'Done', 'message' => $message, 'type' => 'success']);
        } else {
            return redirect()->back()->with(['title' => 'Error', 'message' => 'Staff Request not found.', 'type' => 'error']);
        }
    }

    public function savePayment(Request $request)
    {
        // return $request->all();
        $client = Client::where('id', $request->client_id)->with('clientRouteStaff')->first();
        $value = ClientPayment::create([
            'client_id' => $request->client_id,
            'schedule_id' => $request->schedule_id,
            'option' => $request->option ?? null,
            'option_two' => $request->option_two ?? null,
            'option_three' => $request->option_three ?? null,
            'option_four' => $request->option_four ?? null,
            'partial_completed_scope' => $request->partial_completed_scope ?? null,
            'reason' => $request->reason ?? null,
            'scope' => $request->scope ?? null,
            'amount' => $request->amount ?? null,
            'price_charge_one' => $request->price_charged_one ?? null,
            'price_charge_two' => $request->price_charged_two ?? null,
            'final_price' => $request->final_price ?? null,
            'day_number' => $request->day_number ?? null,
            'payment_type' => $request->payment_type ?? null,
            'payment_date' => ($request->payment_type == "cash" && $request->option != 'no_payment') ? now()->format('Y-m-d')  : null,
            'start_time' => $request->start_time ?? null,
            'end_time' => $request->end_time ?? null,
            'staff_id' => auth()->user()->id,
            'status' => ($request->payment_type == "cash" && $request->option != 'no_payment') ? 'paid' : 'pending',
            'payment_status' => ($request->payment_type == "cash" && $request->option != 'no_payment') ? 'paid' : 'pending',
        ]);

        $reorded = ClientSchedule::where('id', $request->schedule_id)->update([
            'status' => 'completed',
            'service_date' => $request->service_date ?? now()->format('Y-m-d'),
            'staff_id' => auth()->user()->id,
        ]);

        Notification::create([
            'user_id' => 2,
            'action_id' => $request->client_id,
            'title' => $request->option . ' Client Payment Updated',
            'message' => $request->option . ' Client payment has been updated.',
            'type' => 'client_payment_updated',
        ]);
        if (isset($client->clientRouteStaff[0])) {
            return redirect()->route('staffroutes.show', [$client->clientRouteStaff[0]->route_id])->with(['title' => 'Payment Updated', 'message' => 'Client Cash Updated Successfully.', 'type' => 'success']);
        } else {
            return redirect()->back()->with(['title' => 'Payment Updated', 'message' => 'Client Cash Updated Successfully.', 'type' => 'success']);
        }
    }

    public function sortedSchedule(Request $request)
    {
        $isAdmin = auth()->user()->hasRole('admin');
        foreach ($request->sorted_items as $index => $scheduleId) {
            $schedule = ClientSchedule::find($scheduleId);
            if ($schedule && $schedule->client_id) {
                $client = Client::find($schedule->client_id);
                if ($client) {
                    if ($isAdmin) {
                        $client->position = $index;
                    } else {
                        $client->staff_position = $index;
                    }
                    $client->save();
                }
            }
        }

        if ($isAdmin) {
            Notification::create([
                'user_id' => 2,
                'action_id' => 2,
                'title' => 'Client Schedule Sorted',
                'message' => 'Client schedule has been updated.',
                'type' => 'client_schedule_updated',
            ]);
        } else {
            Notification::create([
                'user_id' => auth()->id(),
                'action_id' => auth()->id(),
                'title' => 'Client Schedule Sorted',
                'message' => 'Client schedule has been sorted and is pending approval.',
                'type' => 'client_schedule_sorted_pending',
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function updateClientSchedule(Request $request)
    {
        $client = Client::where('id', $request->id)->first();
        $currentDate = Carbon::now();
        //        $clientStartDate = Carbon::createFromFormat('m-d-Y', $request->start_date)->format('d/m/Y');
        //        $clientStartDate = $request->start_date ? Carbon::createFromFormat('m-d-Y', $request->start_date)->format('d/m/Y') : $currentDate->format('d/m/Y');
        if (!$client) {
            return response()->json([
                'status' => 'error',
                'message' => 'Client not found.',
            ], 404);
        }
        if ($request->service_frequency == 'normalWeek') {
            if (empty($request->start_date)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Start date is required when service frequency is 4-Week Cycle.',
                ], 400);
            }

            try {

                $startDate = Carbon::createFromFormat('m-d-Y', $request->start_date);
                $endDate = $startDate->copy()->addWeeks(3);
                $endDate = $endDate->startOfWeek(Carbon::MONDAY)->endOfWeek(Carbon::SUNDAY);
                $endDateFormatted = $endDate->format('d/m/Y');
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid date format.',
                ], 400);
            }
        } elseif ($request->service_frequency == 'biMonthly' || $request->service_frequency == 'biAnnually' || $request->service_frequency == 'monthly') {
            if (empty($request->start_date)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Start date is required when service frequency is 4-Week Cycle.',
                ], 400);
            }

            try {
                $startDate = Carbon::createFromFormat('m-d-Y', $request->start_date);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid date format.',
                ], 400);
            }
        } elseif ($request->service_frequency == 'quarterly' || $request->service_frequency == 'eightWeek') {
            // ✅ For eightWeek/quarterly, start_date is NOT required (schedules are created from current date)
            // But if provided, use it
            if (!empty($request->start_date)) {
                try {
                    $startDate = Carbon::createFromFormat('m-d-Y', $request->start_date);
                    $endDateFormatted = null; // No end_date for eightWeek/quarterly
                } catch (\Exception $e) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Invalid date format.',
                    ], 400);
                }
            } else {
                // If no start_date provided, use current date logic
                try {
                    if ($currentDate->month <= 3) {
                        $startDate = $currentDate->startOfMonth()->next(Carbon::MONDAY);
                    } else {
                        $startDate = $currentDate->startOfMonth()->previous(Carbon::MONDAY);
                    }
                    $endDateFormatted = null;
                } catch (\Exception $e) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Invalid date format.',
                    ], 400);
                }
            }
        } else {
            $endDateFormatted = null;
        }

        //        $client->update([
        //            'service_frequency' => $request->service_frequency,
        //            'start_date' => $request->start_date->format('d/m/Y') ?? $client->start_date,
        //            'second_start_date' => $request->start_date_second->format('d/m/Y') ??  null,
        //            'end_date' => $endDateFormatted ?? null,
        //        ]);

        // Parse second_start_date - it can come in different formats
        $secondStartDate = null;
        if ($request->start_date_second) {
            $secondStartDateValue = $request->start_date_second;
            try {
                // Try m-d-Y format first (from flatpickr)
                $secondStartDate = Carbon::createFromFormat('m-d-Y', $secondStartDateValue)->format('d/m/Y');
            } catch (\Exception $e) {
                try {
                    // Try d/m/Y format (from database)
                    $secondStartDate = Carbon::createFromFormat('d/m/Y', $secondStartDateValue)->format('d/m/Y');
                } catch (\Exception $e2) {
                    $secondStartDate = null;
                }
            }
        }

        $client->update([
            'service_frequency' => $request->service_frequency,
            'start_date' => $startDate->format('d/m/Y'),
            'second_start_date' => $secondStartDate,
            'end_date' => $endDateFormatted ?? null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Schedule updated successfully.',
            'data' => $client
        ]);
    }


    public function fetchNotifications()
    {
        // Fetch all notifications (no filter)
        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get(['title', 'message', 'created_at', 'is_read']);

        return response()->json([
            'count' => Notification::where('user_id', Auth::id())->where('is_read', false)->count(),
            'notifications' => $notifications
        ]);
    }

    public function markAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function deleteNotification($id)
    {
        try {
            $notification = Notification::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete notification.'
            ], 404);
        }
    }

    public function completeJobs()
    {
        // Fetch completed schedules with relationships
        $completeJobs = ClientSchedule::with([
            'clientName',  // Client relationship
            'clientSchedulePayment',  // Payment relationship
        ])->where('status', 'completed')->orderBy('created_at', 'desc')->get();
        $routes = StaffRoute::where('status', 1)->get();
        return view('complete-jobs.index', compact('completeJobs', 'routes'));
    }

    public function updatePricePositions(Request $request)
    {
        $positions = $request->input('positions', []);
        $clientIds = [];

        foreach ($positions as $item) {
            if (!empty($item['id'])) {
                $priceList = ClientPriceList::where('id', $item['id'])->first();
                if ($priceList) {
                    $priceList->update(['position' => $item['position']]);
                    $clientIds[] = $priceList->client_id;
                }
            }
        }

        $clientIds = array_unique($clientIds);
        foreach ($clientIds as $clientId) {
            Client::find($clientId)?->touch();
        }
        return response()->json(['status' => 'success']);
    }

    private function generateExcelReport($data, $allDeposits, $allTimelogs, $allStaffLogHours, $weeks, $selectedMonth, $selectedYear, $weekNum, $exportType, $baseMonthName)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getDefaultStyle()->getFont()->setSize(9);

        $rowIndex = 1;

        // Determine which weeks to export
        $weeksToExport = [];
        if ($exportType === 'all') {
            $weeksToExport = [1, 2, 3, 4];
        } else {
            $weeksToExport = [$weekNum];
        }

        foreach ($weeksToExport as $currentWeekNum) {
            // Find the week data
            $weekData = null;
            $weekLabel = '';

            foreach ($data as $label => $weekRoutes) {
                // Match "Week 1 |" or "Week 1 " or "Week 1-"
                if (preg_match('/^Week\s+' . $currentWeekNum . '(\s|\||$)/', $label)) {
                    $weekData = $weekRoutes;
                    $weekLabel = $label;
                    break;
                }
            }

            if (!$weekLabel) {
                $weekLabel = "Week $currentWeekNum";
            }

            $hasSchedule = $weekData && $weekData->count() > 0;

            // Skip weeks with no schedule entirely when exporting all weeks
            if ($exportType === 'all' && !$hasSchedule) {
                continue;
            }

            // Title Row - Use weekLabel with year for single week, or add month for all weeks
            if ($exportType === 'single') {
                $sheet->setCellValue("A$rowIndex", "Route Report - $weekLabel - $selectedYear");
            } else {
                $sheet->setCellValue("A$rowIndex", "Route Report - $weekLabel - $selectedMonth");
            }
            $sheet->mergeCells("A$rowIndex:I$rowIndex");
            $sheet->getStyle("A$rowIndex")->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle("A$rowIndex")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF32346A');
            $sheet->getStyle("A$rowIndex")->getFont()->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle("A$rowIndex")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getRowDimension($rowIndex)->setRowHeight(24);
            $rowIndex++;

            $rowIndex++; // Empty row

            // Header Row - Billed (green) and Unpaid Accounts (red) get their own
            // header colors so the two columns are distinguishable at a glance
            $headers = ['Route', 'Staff', 'Total Sales', 'Cash Receive', 'HRs', 'Billed', 'Unpaid Accounts', 'Omit', 'Partial'];
            $colIndex = 'A';
            foreach ($headers as $header) {
                $headerCell = $sheet->getStyle($colIndex . $rowIndex);
                $sheet->setCellValue($colIndex . $rowIndex, strtoupper($header));

                if ($header === 'Billed') {
                    $headerFillColor = 'FF28A745';
                    $headerFontColor = 'FFFFFFFF';
                } elseif ($header === 'Unpaid Accounts') {
                    $headerFillColor = 'FFDC3545';
                    $headerFontColor = 'FFFFFFFF';
                } else {
                    $headerFillColor = 'F6F8FB';
                    $headerFontColor = 'FF32346A';
                }

                $headerCell->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($headerFontColor));
                $headerCell->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB($headerFillColor);
                $headerCell->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $headerCell->getBorders()->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                    ->getColor()->setARGB('FFFFFFFF');
                $colIndex++;
            }
            $sheet->getRowDimension($rowIndex)->setRowHeight(20);
            $rowIndex++;

            // Data Rows
            if ($hasSchedule) {
                $routeRowCounter = 0;
                foreach ($weekData as $routeId => $schedules) {
                    $routeRowCounter++;
                    $routeName = $schedules->first()->clientName?->clientRouteStaff->first()->route->name ?? 'N/A';
                    $staffName = $schedules->first()?->StaffName?->name ?? 'N/A';
                        $staffName = $staffName === 'N/A' ? 'N/A' : preg_replace('/^(\S+)\s+(\S).*/', '$1 $2', $staffName);

                    // Calculate summary values
                    $totalSales = $schedules->sum(fn($s) => $s->clientSchedulePayment->final_price ?? 0);
                    $cashSchedules = $schedules->filter(fn($s) => ($s->clientSchedulePayment->payment_type ?? '') == 'cash' && ($s->clientSchedulePayment->status ?? '') == 'paid');
                    $cashRecord = $cashSchedules->sum(fn($s) => $s->clientSchedulePayment->final_price ?? 0);

                    // Calculate HRs from Staff Log Hours (matched by route_id and week_start_date)
                    $weekStartDate = null;
                    foreach ($weeks as $week) {
                        if ((int) $week['week_number'] === (int) $currentWeekNum) {
                            $weekStartDate = $week['start_date']->format('Y-m-d');
                            break;
                        }
                    }

                    $totalHours = $allStaffLogHours
                        ->where('route_id', $routeId)
                        ->when($weekStartDate, fn($c) => $c->filter(
                            fn($log) => \Carbon\Carbon::parse($log->week_start_date)->format('Y-m-d') === $weekStartDate
                        ))
                        ->sum('duration_hours');

                    // Build detailed breakdowns with client names using RichText for selective bold

                    // Total Sales breakdown: Cash (bold) + Invoice (bold) + Total Sales (bold) with amounts not bold
                    $totalSalesRich = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
//                    $cashLabel = $totalSalesRich->createTextRun("Cash: ");
//                    $cashLabel->getFont()->setBold(true);
//                    $totalSalesRich->createText(number_format($cashRecord, 2) . "\n");
//                    $invoiceLabel = $totalSalesRich->createTextRun("Invoice: ");
//                    $invoiceLabel->getFont()->setBold(true);
//                    $totalSalesRich->createText(number_format($invoiceSchedules->sum(fn($s) => $s->clientSchedulePayment->final_price ?? 0), 2) . "\n");
                    $totalLabel = $totalSalesRich->createTextRun("$ ");
                    $totalLabel->getFont()->setBold(true)->setSize(9);
                    $totalSalesRich->createText(number_format($totalSales, 2));

                    // Cash Record breakdown: Client names (bold) with amounts (not bold)
                    $cashRecordRich = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
                    if ($cashSchedules->isEmpty()) {
                        $nameRun = $cashRecordRich->createTextRun('$ ');
                        $nameRun->getFont()->setBold(true)->setSize(9);
                        $cashRecordRich->createText('0');
                    } else {
                        $nameRun = $cashRecordRich->createTextRun('$ ');
                        $nameRun->getFont()->setBold(true)->setSize(9);
                        $cashRecordRich->createText(number_format($cashRecord, 2));
                    }

                    // Billed breakdown: Client Name (Scope) (bold) + Price (not bold), one client per line
                    $billedSchedules = $schedules->filter(function ($s) {
                        $payment = $s->clientSchedulePayment;
                        return ($payment->payment_type ?? '') == 'invoice';
                    });
                    $billedRich = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
                    if ($billedSchedules->isEmpty()) {
                        $billedRich->createText('-');
                    } else {
                        $firstBilled = true;
                        foreach ($billedSchedules as $schedule) {
                            if (!$firstBilled) $billedRich->createText("\n\n");
                            $firstBilled = false;
                            $clientName = $schedule->clientName->name ?? 'Unknown';
                            $scope = $schedule->clientSchedulePrice
                                ->map(fn ($price) => $price->clientPaymentPrice?->name)
                                ->filter()
                                ->implode(', ');
                            $serviceDate = $schedule->start_date
                                ? \Carbon\Carbon::parse($schedule->start_date)->format('m/d/Y')
                                : '';

                            $bulletRun = $billedRich->createTextRun("\u{25CF} ");
                            $bulletRun->getFont()->setBold(true)->setSize(9)
                                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E8449'));

                            $nameRun = $billedRich->createTextRun(
                                $scope ? "$clientName ($scope)" : $clientName
                            );
                            $nameRun->getFont()->setBold(true)->setSize(9)
                                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E8449'));

                            if ($serviceDate) {
                                $dateRun = $billedRich->createTextRun(" [Service Date: $serviceDate]");
                                $dateRun->getFont()->setItalic(true)->setSize(9);
                            }

                            $billedRich->createText(": ");
                            $amount = $schedule->clientSchedulePayment->final_price ?? 0;
                            $billedRich->createText(number_format($amount, 2));
                        }
                    }

                    // Unpaid breakdown: Client Name (bold) + Unpaid Amount (not bold), one client per line
                    $unpaidSchedules = $schedules->filter(function ($s) {
                        $payment = $s->clientSchedulePayment;
                        return ($payment->payment_type ?? '') == 'cash'
                            && ($payment->status ?? '') == 'pending';
                    });
                    $unpaidRich = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
                    if ($unpaidSchedules->isEmpty()) {
                        $unpaidRich->createText('-');
                    } else {
                        $firstUnpaid = true;
                        foreach ($unpaidSchedules as $schedule) {
                            if (!$firstUnpaid) $unpaidRich->createText("\n\n");
                            $firstUnpaid = false;
                            $clientName = $schedule->clientName->name ?? 'Unknown';
                            $amount = $schedule->clientSchedulePayment->final_price ?? 0;
                            $serviceDate = $schedule->start_date
                                ? \Carbon\Carbon::parse($schedule->start_date)->format('m/d/Y')
                                : '';

                            $bulletRun = $unpaidRich->createTextRun("\u{25CF} ");
                            $bulletRun->getFont()->setBold(true)->setSize(9)
                                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFB03A2E'));

                            $nameRun = $unpaidRich->createTextRun($clientName);
                            $nameRun->getFont()->setBold(true)->setSize(9)
                                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFB03A2E'));

                            if ($serviceDate) {
                                $dateRun = $unpaidRich->createTextRun(" [Service Date: $serviceDate]");
                                $dateRun->getFont()->setItalic(true)->setSize(9);
                            }

                            $unpaidRich->createText(": ");
                            $unpaidRich->createText(number_format($amount, 2));
                        }
                    }

                    // Omit breakdown: Client names (bold) + amount (normal) + Reason label (bold) + reason text (not bold)
                    $omitSchedules = $schedules->filter(fn($s) => ($s->clientSchedulePayment->option ?? '') == 'omit');
                    $omitRich = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
                    if ($omitSchedules->isEmpty()) {
                        $omitRich->createText('-');
                    } else {
                        $firstOmit = true;
                        foreach ($omitSchedules as $schedule) {
                            if (!$firstOmit) $omitRich->createText("\n\n");
                            $firstOmit = false;
                            $clientName = $schedule->clientName->name ?? 'Unknown';
                            $reason = $schedule->clientSchedulePayment->reason ?? '';

                            $nameRun = $omitRich->createTextRun($clientName . ": " . $reason);
                            $nameRun->getFont()->setSize(9);
                        }
                    }

                    // Partial breakdown: Client names (bold) + amount (normal) + Partial Scope label (bold) + scope text (not bold)
                    $partialSchedules = $schedules->filter(fn($s) => ($s->clientSchedulePayment->option ?? '') == 'partially');
                    $partialRich = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
                    if ($partialSchedules->isEmpty()) {
                        $partialRich->createText('-');
                    } else {
                        $firstPartial = true;
                        foreach ($partialSchedules as $schedule) {
                            if (!$firstPartial) $partialRich->createText("\n\n");
                            $firstPartial = false;
                            $clientName = $schedule->clientName->name ?? 'Unknown';
                            $scope = $schedule->clientSchedulePayment->partial_completed_scope ?? '';
                            $amount = $schedule->clientSchedulePayment->final_price ?? 0;

                            $nameRun = $partialRich->createTextRun(
                                $scope ? "$clientName ($scope): " : "$clientName: "
                            );
                            $nameRun->getFont()->setSize(9);
                            $amount = $schedule->clientSchedulePayment->final_price ?? 0;
                            $partialRich->createText(number_format($amount, 2));
                        }
                    }

                    // Write row data
                    $sheet->setCellValue("A$rowIndex", $routeName);
                    $sheet->setCellValue("B$rowIndex", $staffName);

                    // Total Sales with breakdown (bold labels, normal amounts)
                    $sheet->setCellValue("C$rowIndex", $totalSalesRich);
                    $sheet->getStyle("C$rowIndex")->getAlignment()->setWrapText(true);

                    // Cash Record with client details (bold names, normal amounts)
                    $sheet->setCellValue("D$rowIndex", $cashRecordRich);
                    $sheet->getStyle("D$rowIndex")->getAlignment()->setWrapText(true);

                    // Hours
                    $sheet->setCellValue("E$rowIndex", number_format($totalHours, 2));

                    // Billed with breakdown (bold labels, normal amounts) - shaded green
                    // so it reads as a distinct block from the Unpaid column
                    $sheet->setCellValue("F$rowIndex", $billedRich);
                    $sheet->getStyle("F$rowIndex")->getAlignment()->setWrapText(true);
                    $sheet->getStyle("F$rowIndex")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFE9F7EF');

                    // Unpaid with breakdown (bold labels, normal amounts) - shaded red/pink
                    // so Billed vs Unpaid are visually distinguishable at a glance
                    $sheet->setCellValue("G$rowIndex", $unpaidRich);
                    $sheet->getStyle("G$rowIndex")->getAlignment()->setWrapText(true);
                    $sheet->getStyle("G$rowIndex")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFFDEDEC');

                    // Omit with client names and reasons (bold names and labels, normal text)
                    $sheet->setCellValue("H$rowIndex", $omitRich);
                    $sheet->getStyle("H$rowIndex")->getAlignment()->setWrapText(true);

                    // Partial with client names and scope (bold names and labels, normal text)
                    $sheet->setCellValue("I$rowIndex", $partialRich);
                    $sheet->getStyle("I$rowIndex")->getAlignment()->setWrapText(true);

                    // Zebra-stripe the non-Billed/Unpaid columns so rows are easy to
                    // scan without diluting the fixed green/red column shading
                    if ($routeRowCounter % 2 === 0) {
                        $sheet->getStyle("A$rowIndex:E$rowIndex")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFF7F7F9');
                        $sheet->getStyle("H$rowIndex:I$rowIndex")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFF7F7F9');
                    }

                    // Thin grid border around the whole row for a clean, printable look
                    $sheet->getStyle("A$rowIndex:I$rowIndex")->getBorders()->getAllBorders()
                        ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                        ->getColor()->setARGB('FFDDDDDD');

                    // Align all cells in this row to the top (rows grow tall due to
                    // wrapped multi-line rich text, and the default vertical
                    // alignment is bottom, which pushes content down)
                    $sheet->getStyle("A$rowIndex:I$rowIndex")->getAlignment()
                        ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

                    $rowIndex++;
                }
            } else {
                $sheet->setCellValue("A$rowIndex", "No Schedule To This Week");
                $sheet->mergeCells("A$rowIndex:I$rowIndex");
                $sheet->getStyle("A$rowIndex")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $sheet->getStyle("A$rowIndex")->getFont()->setItalic(true)->getColor()->setARGB('FF888888');
                $sheet->getStyle("A$rowIndex")->getBorders()->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                    ->getColor()->setARGB('FFDDDDDD');
                $sheet->getRowDimension($rowIndex)->setRowHeight(20);
                $rowIndex++;
            }

            $rowIndex++; // Empty row between weeks
            $rowIndex++; // Another empty row
        }

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(11);
        $sheet->getColumnDimension('C')->setWidth(11);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(5);
        $sheet->getColumnDimension('F')->setWidth(30);
        $sheet->getColumnDimension('G')->setWidth(30);
        $sheet->getColumnDimension('H')->setWidth(30);
        $sheet->getColumnDimension('I')->setWidth(30);

        // Generate filename
        $filename = $exportType === 'all'
            ? "Route_Report_All_Weeks_$selectedMonth.xlsx"
            : "Route_Report_Week_{$weekNum}_$selectedMonth.xlsx";

        // Output Excel file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function sendScheduleNotification()
    {
        try {
            Log::info('Starting schedule notification process');

            $today      = Carbon::now();
            $targetDate = $today->copy()->addDays(20)->toDateString();

            Log::info('Target date', ['date' => $targetDate]);

            $upcomingSchedules = ClientSchedule::with('clientName')
                ->where('start_date', $targetDate)
                ->whereIn('note_type', ['8_weeks', '12_weeks', '24_weeks'])
                ->get();

            Log::info('Upcoming schedules found', ['count' => $upcomingSchedules->count()]);

            if ($upcomingSchedules->isEmpty()) {
                Log::info('No upcoming schedules found for date: ' . $targetDate);
                return response()->json(['message' => 'No upcoming schedules']);
            }

            // Admins hamesha milenge
            $admins = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->get();

            foreach ($upcomingSchedules as $schedule) {
                $clientName = optional($schedule->clientName)->name ?? 'Unknown Client';
                $clientId   = $schedule->client_id;

                // Step 1: client_id se route_id lo (client_routes table)
                $clientRoute = DB::table('client_routes')
                    ->where('client_id', $clientId)
                    ->first();

                if (!$clientRoute) {
                    Log::info('No route found for client', ['client_id' => $clientId]);
                    continue;
                }

                $routeId = $clientRoute->route_id;

                // Step 2: route_id se staff lo (assign_routes table)
                $assignedStaffIds = DB::table('assign_routes')
                    ->where('route_id', $routeId)
                    ->pluck('staff_id');

                $assignedStaff = User::whereIn('id', $assignedStaffIds)->get();

                Log::info('Staff found for route', [
                    'route_id'   => $routeId,
                    'staff_count' => $assignedStaff->count(),
                ]);

                // Step 3: Staff + Admins merge karo
                $allUsers = $admins->merge($assignedStaff);

                // Step 4: Notification bhejo
                foreach ($allUsers as $user) {
                    try {
                        $alreadyExists = Notification::where('user_id', $user->id)
                            ->where('action_id', $schedule->id)
                            ->where('type', 'schedule_reminder')
                            ->exists();

                        if ($alreadyExists) {
                            Log::info('Notification already exists — skipping', [
                                'user_id'   => $user->id,
                                'action_id' => $schedule->id,
                            ]);
                            continue;
                        }

                        $date = \Carbon\Carbon::parse($schedule->start_date)->format('m-d-Y');
                        Notification::create([
                            'user_id'   => $user->id,
                            'action_id' => $schedule->id,
                            'title'     => $clientName . ' - Upcoming Schedule Reminder',
                            'message'   => 'Client ' . $clientName . ' has a scheduled visit on ' . $date  . ' (Note type: ' . $schedule->note_type . ')',
                            'type'      => 'schedule_reminder',
                        ]);

                        Log::info('Notification created', [
                            'user_id'    => $user->id,
                            'client'     => $clientName,
                            'start_date' => $schedule->start_date,
                            'note_type'  => $schedule->note_type,
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Error creating notification', [
                            'user_id' => $user->id,
                            'error'   => $e->getMessage()
                        ]);
                    }
                }
            }

            return response()->json(['message' => 'Schedule notifications sent successfully']);
        } catch (\Exception $e) {
            Log::error('Error in sendScheduleNotification', [
                'error'       => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to send notifications'], 500);
        }
    }
}
