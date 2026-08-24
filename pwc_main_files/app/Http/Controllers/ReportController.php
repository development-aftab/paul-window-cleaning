<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ClientPayment;
use App\Models\ClientSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    private function getCalendarData($selectedMonthStr)
    {
        $currentDate = now();
        $selectedYear = $currentDate->year;

        if ($selectedMonthStr) {
            preg_match('/\d{4}/', $selectedMonthStr, $yearMatch);
            $selectedYear = $yearMatch[0] ?? $selectedYear;
        }

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

        if ($selectedMonthStr) {
            $baseMonthName = trim(str_replace($selectedYear, '', $selectedMonthStr));
            if (!array_key_exists($baseMonthName, $customStartDates)) {
                $baseMonthName = "January - February";
            }
        } else {
            $baseMonthName = "January - February";
            foreach ($customStartDates as $range => $startDate) {
                if ($currentDate->gte($startDate) && $currentDate->lt($startDate->copy()->addWeeks(4))) {
                    $baseMonthName = $range;
                    break;
                }
            }
        }

        $selectedMonth = "$baseMonthName $selectedYear";
        $monthStartDate = $customStartDates[$baseMonthName];
        $monthEndDate = $monthStartDate->copy()->addWeeks(4)->subDay();

        $monthNames = array_keys($customStartDates);
        $months = [];
        foreach ($monthNames as $monthName) {
            $months[] = $monthName . ' ' . $selectedYear;
        }

        $currentIndex = array_search($baseMonthName, $monthNames);
        if ($currentIndex === false) { $currentIndex = 0; }

        if ($currentIndex > 0) {
            $previousMonth = $monthNames[$currentIndex - 1] . ' ' . $selectedYear;
        } else {
            $previousMonth = $monthNames[count($monthNames) - 1] . ' ' . ($selectedYear - 1);
        }

        if ($currentIndex < count($monthNames) - 1) {
            $nextMonth = $monthNames[$currentIndex + 1] . ' ' . $selectedYear;
        } else {
            $nextMonth = $monthNames[0] . ' ' . ($selectedYear + 1);
        }

        return [
            'baseMonthName' => $baseMonthName,
            'selectedYear' => $selectedYear,
            'selectedMonth' => $selectedMonth,
            'start_date' => $monthStartDate,
            'end_date' => $monthEndDate,
            'months' => $months,
            'previousMonth' => $previousMonth,
            'nextMonth' => $nextMonth,
        ];
    }

    public function unpaidAccounts(Request $request)
    {
        $cal = $this->getCalendarData($request->input('month'));
        extract($cal);

        // Get staff IDs from assign_routes table
        $assignedStaffIds = DB::table('assign_routes')
            ->whereNull('deleted_at')
            ->pluck('staff_id')
            ->unique();

        if (Auth::user()->hasRole('staff')) {
            $assignedStaffIds = $assignedStaffIds->filter(fn($id) => $id == Auth::id());
        }

//        $schedules = ClientSchedule::with(['clientSchedulePayment', 'clientName.clientRouteStaff.route', 'StaffName'])
//            ->where('status', 'completed')
//            ->whereIn('staff_id', $assignedStaffIds)
////            ->whereBetween('start_date', [$start_date->format('Y-m-d'), $end_date->format('Y-m-d')])
//            ->where(function($q) {
//                $q->whereHas('clientSchedulePayment', function ($sub) {
//                    $sub->where('status', '!=', 'paid');
//                })->orWhereDoesntHave('clientSchedulePayment');
//            })
//            ->get();
        $schedules = ClientSchedule::with([
            'clientSchedulePayment',
            'clientName.clientRouteStaff.route',
            'StaffName'
        ])
            ->where('client_schedules.status', 'completed')
            ->whereHas('clientName', function ($q) {
                $q->where('payment_type', 'cash');
            })
            ->whereIn('client_schedules.staff_id', $assignedStaffIds)
            ->where(function($q) {
                $q->whereHas('clientSchedulePayment', function ($sub) {
                    $sub->where('status', 'pending');
                })->orWhereDoesntHave('clientSchedulePayment');
            })
            ->join('clients', 'client_schedules.client_id', '=', 'clients.id')
            ->orderBy('client_schedules.created_at', 'asc')
            ->select('client_schedules.*')
            ->get();

        // Attach route name via client's assigned route
        foreach ($schedules as $schedule) {
            $schedule->route_name = $schedule->clientName?->clientRouteStaff?->first()?->route?->name ?? 'N/A';
        }

        // Group by staff name
        $groupedData = $schedules->groupBy(function ($item) {
            return optional($item->StaffName)->first_name
                ?? optional($item->StaffName)->name
                ?? 'Unknown Staff';
        });
        $groupedData = $groupedData->sortKeys();

        return view('dashboard.reports.unpaid_accounts', compact(
            'groupedData', 'months', 'selectedMonth', 'previousMonth', 'nextMonth'
        ));
    }

    public function markPaymentPaid(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|exists:client_payments,id',
            'payment_date' => 'required|date',
        ]);

        $payment = ClientPayment::with('clientSchedule')->findOrFail($request->payment_id);

        if ($payment->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Payment is already marked as paid.',
            ], 422);
        }

        if (Auth::user()->hasRole('staff')) {
            $scheduleStaffId = $payment->clientSchedule?->staff_id;
            if ($scheduleStaffId != Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to update this payment.',
                ], 403);
            }
        }

        $payment->update([
            'status' => 'paid',
            'payment_date' => $request->payment_date,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment marked as paid successfully.',
        ]);
    }
}
