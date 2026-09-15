<?php

namespace App\Http\Controllers;

use App\Models\{Timelog, StaffRoute, User, ClientSchedule};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TimelogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin');

        if ($isAdmin) {
            $timelogs = Timelog::with('route', 'staff')->latest()->get();
            $routes = StaffRoute::where('status', 1)->get();

            $allStaff = User::whereHas('roles', function ($query) {
                $query->where('name', 'staff');
            })->get();
            $allRoutes = StaffRoute::where('status', 1)->get();
        } else {
            $timelogs = Timelog::with('route', 'staff')
                ->where('staff_id', $user->id)
                ->latest()
                ->get();

            $routes = StaffRoute::with('assignRoute')
                ->whereHas('assignRoute', function ($query) use ($user) {
                    $query->where('staff_id', $user->id);
                })
                ->where('status', 1)
                ->get();

            $allStaff = collect();
            $allRoutes = collect();
        }

        return view('dashboard.time_log', compact('timelogs', 'routes', 'isAdmin', 'allStaff', 'allRoutes'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->hasRole('admin')) {
            return redirect()->back()->with(['title' => 'Error', 'message' => 'Admin cannot create time logs', 'type' => 'error']);
        }

        $request->validate([
            'route_id' => 'required|exists:staff_routes,id',
            'service_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        $start = Carbon::parse($request->service_date . ' ' . $request->start_time);
        $end = Carbon::parse($request->service_date . ' ' . $request->end_time);

        $isAssigned = StaffRoute::whereHas('assignRoute', function ($query) {
            $query->where('staff_id', auth()->user()->id);
        })->where('id', $request->route_id)->exists();

        if (!$isAssigned) {
            return redirect()->back()->with(['title' => 'Error', 'message' => 'This route is not assigned to you', 'type' => 'error']);
        }

        $activeTimelog = Timelog::where('route_id', $request->route_id)
            ->where('staff_id', Auth::id())
            ->whereNull('end_time')
            ->first();

        if ($activeTimelog) {
            return redirect()->back()->with(['title' => 'Error', 'message' => 'You already have an active time log for this route. Please end it first.', 'type' => 'error']);
        }
        $serviceDate = Carbon::parse($request->service_date);
        $year = $serviceDate->year;
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

        $matchedMonth = "January";
        $weekNumber = 0;

        foreach ($customStartDates as $monthName => $startDate) {
            $endDate = $startDate->copy()->addWeeks(4)->subDay(); // 4 weeks period
            if ($serviceDate->between($startDate, $endDate)) {
                $matchedMonth = $monthName;

                $daysDiff = $serviceDate->diffInDays($startDate);
                $weekNumber = min(3, floor($daysDiff / 7)); // 0, 1, 2, or 3
                break;
            }
        }

        $weekString = 'week' . $weekNumber;

        $monthParts = explode(' - ', $matchedMonth);
        $monthName = ucfirst(strtolower($monthParts[0])); // "January", "February", etc.

        Timelog::create([
            'route_id' => $request->route_id,
            'staff_id' => Auth::id(),
            'service_date' => $serviceDate->format('Y-m-d'),
            'week' => $weekString,
            'month' => $monthName,
            'year' => $year,
//            'start_time' => now()->format('H:i:s'),
//            'end_time' => null,
//            'total_hours' => null,
            'start_time' => $start->format('H:i:s'),
            'end_time' => $end->format('H:i:s'),
            'total_hours' => $end->diffInMinutes($start) / 60,
            'notes' => $request->notes,
        ]);

        Log::info('Time log created', ['route_id' => $request->route_id, 'staff_id' => Auth::id(), 'start_time' => now()->format('H:i:s')]);

        return redirect()->back()->with(['title' => 'Success', 'message' => 'Time log started successfully', 'type' => 'success']);
    }

    public function endTime(string $id)
    {
        // Admin cannot end time logs
        if (auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Admin cannot end time logs'
            ], 403);
        }

        $timelog = Timelog::findOrFail($id);

        // Check if user is authorized (only own timelogs)
        if ($timelog->staff_id !== auth()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action'
            ], 403);
        }

        // Check if already ended
        if ($timelog->end_time) {
            return response()->json([
                'success' => false,
                'message' => 'Time log already ended'
            ]);
        }

        // Set end time and calculate total hours
        $timelog->end_time = now()->format('H:i:s');

        $startTime = \Carbon\Carbon::parse($timelog->service_date . ' ' . $timelog->start_time);
        $endTime = \Carbon\Carbon::parse($timelog->service_date . ' ' . $timelog->end_time);
        $timelog->total_hours = $endTime->diffInMinutes($startTime) / 60;

        $timelog->save();

        return response()->json([
            'success' => true,
            'message' => 'Time log ended successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Admin cannot delete time logs
        if (auth()->user()->hasRole('admin')) {
            return redirect()->back()->with(['title' => 'Error', 'message' => 'Admin cannot delete time logs', 'type' => 'error']);
        }

        $timelog = Timelog::findOrFail($id);

        // Check if user is authorized (only own timelogs)
        if ($timelog->staff_id !== auth()->user()->id) {
            return redirect()->back()->with(['title' => 'Error', 'message' => 'Unauthorized action', 'type' => 'error']);
        }

        $timelog->delete();

        return redirect()->back()->with(['title' => 'Success', 'message' => 'Time log deleted successfully', 'type' => 'success']);
    }

    /**
     * Start timer for a schedule
     */
    public function startTimer(Request $request)
    {
        // Admin cannot start timers
        if (auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Admin cannot start timers'
            ], 403);
        }

        $request->validate([
            'schedule_id' => 'required|exists:client_schedules,id',
            'route_id' => 'required|exists:staff_routes,id',
        ]);

        // Check if ANY staff has an active timer for this schedule (security check)
        $anyActiveTimelog = Timelog::where('schedule_id', $request->schedule_id)
            ->whereNull('end_time')
            ->first();

        if ($anyActiveTimelog) {
            // Check if it's the current staff's timer
            if ($anyActiveTimelog->staff_id === Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have an active timer for this schedule. Please end it first.'
                ]);
            } else {
                // Another staff is working on this schedule
                return response()->json([
                    'success' => false,
                    'message' => 'Another staff member is currently working on this schedule. Please wait until they finish.'
                ], 403);
            }
        }

        // Get schedule data to extract week, month, year
        $schedule = ClientSchedule::find($request->schedule_id);

        if (!$schedule) {
            return response()->json([
                'success' => false,
                'message' => 'Schedule not found'
            ], 404);
        }

        // Create timelog
        $timelog = Timelog::create([
            'route_id' => $request->route_id,
            'schedule_id' => $request->schedule_id,
            'staff_id' => Auth::id(),
            'service_date' => now()->format('Y-m-d'),
            'week' => $schedule->week,
            'month' => $schedule->month,
            'year' => now()->year,
            'start_time' => now()->format('H:i:s'),
            'end_time' => null,
            'total_hours' => null,
        ]);

        Log::info('Timer started', [
            'timelog_id' => $timelog->id,
            'schedule_id' => $request->schedule_id,
            'staff_id' => Auth::id(),
            'start_time' => now()->format('H:i:s')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Timer started successfully',
            'timelog_id' => $timelog->id,
            'start_time' => $timelog->start_time,
            'service_date' => $timelog->service_date
        ]);
    }

    /**
     * Stop timer for a schedule
     */
    public function stopTimer(Request $request)
    {
        // Admin cannot stop timers
        if (auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Admin cannot stop timers'
            ], 403);
        }

        $request->validate([
            'timelog_id' => 'required|exists:timelogs,id',
        ]);

        $timelog = Timelog::find($request->timelog_id);

        if (!$timelog) {
            return response()->json([
                'success' => false,
                'message' => 'Timelog not found'
            ], 404);
        }

        // Check if user is authorized (only own timelogs)
        if ($timelog->staff_id !== auth()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action. You can only stop your own timers.'
            ], 403);
        }

        // Check if already ended
        if ($timelog->end_time) {
            return response()->json([
                'success' => false,
                'message' => 'Timer already stopped'
            ]);
        }

        // Set end time and calculate total hours
        $timelog->end_time = now()->format('H:i:s');

        $startTime = \Carbon\Carbon::parse($timelog->service_date . ' ' . $timelog->start_time);
        $endTime = \Carbon\Carbon::parse($timelog->service_date . ' ' . $timelog->end_time);
        $timelog->total_hours = $endTime->diffInMinutes($startTime) / 60;

        $timelog->save();

        Log::info('Timer stopped', [
            'timelog_id' => $timelog->id,
            'end_time' => $timelog->end_time,
            'total_hours' => $timelog->total_hours
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Timer stopped successfully',
            'total_hours' => number_format($timelog->total_hours, 2)
        ]);
    }

    /**
     * Get active timers for ALL staff (to check if schedule is being worked on)
     */
    public function getActiveTimers(Request $request)
    {
        if (auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => true,
                'active_timers' => [],
                'completed_schedules' => []
            ]);
        }

        $activeTimers = Timelog::whereNull('end_time')
            ->get(['id', 'schedule_id', 'route_id', 'staff_id', 'start_time', 'service_date']);

        $completedSchedules = Timelog::whereNotNull('end_time')
            ->pluck('schedule_id')
            ->unique()
            ->toArray();

        return response()->json([
            'success' => true,
            'active_timers' => $activeTimers,
            'completed_schedules' => $completedSchedules,
            'current_staff_id' => Auth::id()
        ]);
    }
}
