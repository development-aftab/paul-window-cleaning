<?php

namespace App\Http\Controllers;

use App\Models\AssignRoute;
use App\Models\StaffExtraHour;
use App\Models\StaffRoute;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StaffExtraHoursController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        if (!auth()->user()->hasRole('staff')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'route_id' => 'required|exists:staff_routes,id',
            'week_start_date' => 'required|date',
        ]);

        if (!$this->routeIsAssignedToStaff($validated['route_id'], auth()->id())) {
            return response()->json(['success' => false, 'message' => 'This route is not assigned to you'], 403);
        }

        $entries = StaffExtraHour::where('staff_id', auth()->id())
            ->where('route_id', $validated['route_id'])
            ->whereDate('week_start_date', $validated['week_start_date'])
            ->orderBy('service_date')
            ->orderBy('start_time')
            ->get()
            ->map(fn(StaffExtraHour $entry) => $this->formatEntry($entry));

        $totalHours = round($entries->sum('duration_hours'), 2);

        return response()->json([
            'success' => true,
            'entries' => $entries,
            'total_hours' => $totalHours,
        ]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasRole('staff')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'route_id' => 'required|exists:staff_routes,id',
            'week_number' => 'required|integer|min:1|max:4',
            'week_start_date' => 'required|date',
            'week_end_date' => 'required|date|after_or_equal:week_start_date',
            'service_date' => 'required|date|after_or_equal:week_start_date|before_or_equal:week_end_date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'rate_type' => 'required|in:normal,training',
        ]);

        if (!$this->routeIsAssignedToStaff($validated['route_id'], auth()->id())) {
            return response()->json(['success' => false, 'message' => 'This route is not assigned to you'], 403);
        }

        $start = Carbon::parse($validated['service_date'] . ' ' . $validated['start_time']);
        $end = Carbon::parse($validated['service_date'] . ' ' . $validated['end_time']);

        if (!$end->gt($start)) {
            return response()->json([
                'success' => false,
                'message' => 'End time must be after start time.',
            ], 422);
        }

        $durationHours = round($end->diffInMinutes($start) / 60, 2);

        $profile = auth()->user()->profile;
        $rateAmount = $validated['rate_type'] === 'training'
            ? $profile?->training_rate
            : $profile?->normal_rate;

        $entry = StaffExtraHour::create([
            'staff_id' => auth()->id(),
            'route_id' => $validated['route_id'],
            'week_number' => $validated['week_number'],
            'week_start_date' => $validated['week_start_date'],
            'service_date' => $validated['service_date'],
            'start_time' => $validated['start_time'] . ':00',
            'end_time' => $validated['end_time'] . ':00',
            'rate_type' => $validated['rate_type'],
            'rate_amount' => $rateAmount,
            'duration_hours' => $durationHours,
        ]);

        $entries = StaffExtraHour::where('staff_id', auth()->id())
            ->where('route_id', $validated['route_id'])
            ->whereDate('week_start_date', $validated['week_start_date'])
            ->orderBy('service_date')
            ->orderBy('start_time')
            ->get()
            ->map(fn(StaffExtraHour $item) => $this->formatEntry($item));

        return response()->json([
            'success' => true,
            'message' => 'Extra hours added successfully.',
            'entry' => $this->formatEntry($entry),
            'entries' => $entries,
            'total_hours' => round($entries->sum('duration_hours'), 2),
        ]);
    }

    private function routeIsAssignedToStaff($routeId, $staffId): bool
    {
        return StaffRoute::whereHas('assignRoute', function ($query) use ($staffId) {
            $query->where('staff_id', $staffId);
        })->where('id', $routeId)->exists();
    }

    private function formatEntry(StaffExtraHour $entry): array
    {
        $serviceDate = Carbon::parse($entry->service_date);

        return [
            'id' => $entry->id,
            'day' => $serviceDate->format('l, M j, Y'),
            'service_date' => $serviceDate->format('Y-m-d'),
            'start_time' => Carbon::parse($entry->start_time)->format('g:i A'),
            'end_time' => Carbon::parse($entry->end_time)->format('g:i A'),
            'rate_type' => $entry->rate_type,
            'rate_type_label' => ucfirst($entry->rate_type),
            'rate_amount' => $entry->rate_amount !== null ? (float) $entry->rate_amount : null,
            'duration_hours' => (float) $entry->duration_hours,
            'duration_label' => $this->formatDurationLabel((float) $entry->duration_hours),
        ];
    }

    private function formatDurationLabel(float $hours): string
    {
        $totalMinutes = (int) round($hours * 60);
        $hourPart = intdiv($totalMinutes, 60);
        $minutePart = $totalMinutes % 60;

        if ($hourPart > 0 && $minutePart > 0) {
            return "{$hourPart}h {$minutePart}m";
        }

        if ($hourPart > 0) {
            return "{$hourPart}h";
        }

        return "{$minutePart}m";
    }
}
