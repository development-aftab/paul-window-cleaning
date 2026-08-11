@php
    $isAdminReportView = auth()->user()->hasRole('admin');
@endphp
@foreach ($data as $weekName => $weekRoutes)
    @php
        // 1. Setup Week Context - Extract only the week number from "Week 1 | 02 February - 08 February"
        preg_match('/Week\s+(\d+)/', $weekName, $weekMatches);
        $currentWeekNum = isset($weekMatches[1]) ? (int) $weekMatches[1] : 1;
        $dbWeekNum = $currentWeekNum - 1;
        $weekString = 'week' . $dbWeekNum;

        // 2. Extract Year and Month Name
        preg_match('/\d{4}/', $selectedMonth ?? '', $yearMatch);
        $selectedYear = $yearMatch[0] ?? now()->year;

        // Extract full month name (March - April, not just March)
        // Remove year from string to get complete month range
        $selectedMonthName = trim(str_replace($selectedYear, '', $selectedMonth ?? ''));
        // Result: "March - April" or "January - February"
    @endphp

    @if (!$loop->first)
        <tr class="week-spacer-row">
            <td colspan="{{ $isAdminReportView ? 10 : 8 }}"></td>
        </tr>
    @endif

    <tr class="week-header-row">
        <td colspan="{{ $isAdminReportView ? 4 : 3 }}" style="text-align: left; padding-left: 20px;">
            <h3 class="m-0">{{ $weekName }}</h3>
        </td>
        <td colspan="{{ $isAdminReportView ? 6 : 5 }}" class="text-end" style="padding-right:20px">
            <div style="display: flex; justify-content: flex-end; align-items: center; gap: 15px; height: 100%;">
                <button type="button" class="btn_global btn_dark_blue exportWeekBtn" data-week="{{ $weekName }}" data-week-num="{{ $currentWeekNum }}">
                    Export Excel <i class="fa-solid fa-file-excel"></i>
                </button>
            </div>
        </td>
    </tr>

    @if ($weekRoutes->isEmpty())
        <tr>
            <td colspan="{{ $isAdminReportView ? 10 : 8 }}" class="text-center text-muted">No Schedule To This Week</td>
        </tr>
    @else
        @php
            $sortedWeekRoutes = collect($weekRoutes)->sortBy(function($schedules) {
                $staffName = $schedules->first()?->StaffName?->first_name ?? $schedules->first()?->StaffName?->name ?? 'zzzz';
                return strtolower($staffName);
            });
        @endphp
        @foreach ($sortedWeekRoutes as $routeId => $schedules)
            @php
                // --- ROUTE CALCULATIONS ---
                $routeName = $schedules->first()->clientName->clientRouteStaff->first()->route->name ?? 'N/A';
               $staffName = $schedules->first()?->StaffName?->first_name
                            ?? $schedules->first()?->StaffName?->name
                            ?? 'N/A';

                // Total Sales
                $totalSales = $schedules->sum(fn($s) => $s->clientSchedulePayment->final_price ?? 0);

                // Cash Logic
                $cashSchedules = $schedules->filter(fn($s) => ($s->clientSchedulePayment->payment_type ?? '') == 'cash' && ($s->clientSchedulePayment->status ?? '') == 'paid');
                $cashRecord = $cashSchedules->sum(fn($s) => $s->clientSchedulePayment->final_price ?? 0);

                // Deposits
                $matchingDeposits = $allDeposits->where('route_id', $routeId)->where('week', $weekString)->where('month', $selectedMonthName)->where('year', $selectedYear);
                $totalDeposited = $matchingDeposits->sum('deposit_amount');

                // Invoice Logic
                $invoiceSchedules = $schedules->filter(fn($s) => ($s->clientSchedulePayment->payment_type ?? '') == 'invoice');
                $invoiceTotal = $invoiceSchedules->sum(fn($s) => $s->clientSchedulePayment->final_price ?? 0);
                $invoicePaid = $invoiceSchedules->filter(fn($s) => ($s->clientSchedulePayment->payment_status ?? null) == 'paid')->sum(fn($s) => $s->clientSchedulePayment->final_price ?? 0);
                $invoiceUnpaid = $invoiceSchedules->filter(fn($s) => ($s->clientSchedulePayment->payment_status ?? null) === null)->sum(fn($s) => $s->clientSchedulePayment->final_price ?? 0);

                // Un Paid
                $cashUnpaidAcc = $schedules->filter(fn($s) => ($s->clientSchedulePayment->payment_type ?? '') == 'cash' && ($s->clientSchedulePayment->status ?? '') == 'pending');
                $unPaidTotal = $cashUnpaidAcc->sum(fn($s) => $s->clientSchedulePayment->final_price ?? 0);

                // Totals
                $billed = $totalDeposited + $invoicePaid;
                $unpaid = $cashRecord - $totalDeposited + $invoiceUnpaid;

                // Calculate HRs from Staff Log Hours (matched by route_id and week_start_date)
                $currentWeekStartDate = null;
                foreach ($weeks as $week) {
                    if ((int) $week['week_number'] === $currentWeekNum) {
                        $currentWeekStartDate = $week['start_date']->format('Y-m-d');
                        break;
                    }
                }
                $staffLogHoursForRoute = $allStaffLogHours
                    ->where('route_id', $routeId)
                    ->when($currentWeekStartDate, fn($c) => $c->filter(
                        fn($log) => \Carbon\Carbon::parse($log->week_start_date)->format('Y-m-d') === $currentWeekStartDate
                    ));
                $totalHours = $staffLogHoursForRoute->sum('duration_hours');
            @endphp

            <tr class="route-invoice">
                <td>{{ $routeName }}</td>
                @if ($isAdminReportView)
                    <td>{{ $staffName }}</td>
                @endif

                <td>
                    <div class="table_hover">
                        <h3>{{ number_format($totalSales, 2) }}</h3>
                        <div class="tooltip_hover" style="min-width: 350px;">
                            <ul>
                                @foreach ($schedules as $s)
                                    <li style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0;">
                                        <span style="flex: 1;">
                                            {{ $s->clientName->name ?? 'Client' }}<br>
                                            <small style="color: {{ ($s->clientSchedulePayment->payment_type ?? '') == 'cash' ? '#28a745' : '#007bff' }}; font-weight: 600; font-size: 11px;">
                                                {{ ucfirst($s->clientSchedulePayment->payment_type ?? 'N/A') }}
                                            </small>
                                        </span>
                                        <span style="font-weight: 600; margin-left: 15px;">
                                            ${{ number_format($s->clientSchedulePayment->final_price ?? 0, 2) }}
                                        </span>
                                    </li>
                                @endforeach
                                <li style="border-top: 2px solid #ddd; margin-top: 8px; padding-top: 8px; display: flex; justify-content: space-between;">
                                    <strong>Total:</strong>
                                    <strong>${{ number_format($totalSales, 2) }}</strong>
                                </li>
                            </ul>
                        </div>
                    </div>
                </td>

                <td>
                    <div class="table_hover">
                        <h3>{{ number_format($cashRecord, 2) }}</h3>
                        <div class="tooltip_hover">
                            <ul>
                                @forelse ($cashSchedules as $s)
                                    <li style="display: flex; justify-content: space-between;">
                                        <span>{{ $s->clientName->name ?? 'Client' }}</span>
                                        <span>${{ number_format($s->clientSchedulePayment->final_price ?? 0, 2) }}</span>
                                    </li>
                                @empty
                                    <li style="justify-content: center; color: #858585;">No Cash Records</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </td>

                <td>
                    <div class="table_hover">
                        <h3>{{ $totalHours }}</h3>
                        @if ($staffLogHoursForRoute->count() > 0)
                            <div class="tooltip_hover">
                                <ul class="m-0 p-0" style="list-style: none;">
                                    @foreach ($staffLogHoursForRoute as $logEntry)
                                        <li style="display: flex; justify-content: space-between; padding: 4px 0;">
                                            <span>{{ \Carbon\Carbon::parse($logEntry->service_date)->format('d M Y') }}</span>
                                            <span>{{ $logEntry->duration_hours }} hrs</span>
                                        </li>
                                    @endforeach
                                    <li style="border-top: 1px solid #ddd; margin-top: 5px; padding-top: 5px; display: flex; justify-content: space-between;">
                                        <strong>Total:</strong>
                                        <strong>{{ $totalHours }} hrs</strong>
                                    </li>
                                </ul>
                            </div>
                        @endif
                    </div>
                </td>
                {{-- Billed Column (Cash Received + Invoice Paid) --}}
                <td>
                    <div class="table_hover">
                        <h3>{{ number_format($invoiceTotal, 2) }}</h3>
                        <div class="tooltip_hover">
                            <ul>
                                @forelse ($schedules->filter(fn($s) => ($s->clientSchedulePayment->payment_type ?? '') == 'invoice') as $s)
                                    <li>
                                        <span>{{ $s->clientName->name ?? 'Client' }}</span>
                                        <span>${{ number_format($s->clientSchedulePayment->final_price ?? 0, 2) }}</span>
                                    </li>
                                @empty
                                    <li style="justify-content: center; color: #858585;">
                                        No Cash Records</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </td>

                {{-- Unpaid Column with Tooltip --}}
                <td>
                    <div class="table_hover">
                        <h3>{{ number_format($unPaidTotal, 2) }}</h3>
                        <div class="tooltip_hover">
                            <ul>
                                @forelse ($cashUnpaidAcc as $s)
                                    <li>
                                        <span>{{ $s->clientName->name ?? 'Client' }}</span>
                                        <span>${{ number_format($s->clientSchedulePayment->final_price ?? 0, 2) }}</span>
                                    </li>
                                @empty
                                    <li style="justify-content: center; color: #858585;">
                                        No Cash Records</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </td>


                {{-- Omit Column with Conditional Hover --}}
                <td>
                    @php
                        $omitCount = $schedules->filter(fn($s) => ($s->clientSchedulePayment->option ?? '') == 'omit')->count();
                    @endphp
                    @if ($omitCount > 0)
                        <div class="table_hover">
                            <h3>{{ $omitCount }}</h3>
                            <div class="tooltip_hover">
                                <ul>
                                    @foreach ($schedules->filter(fn($s) => ($s->clientSchedulePayment->option ?? '') == 'omit') as $s)
                                        <li style="display: flex; flex-direction: column; align-items: flex-start;">
                                            <span><strong>{{ $s->clientName->name ?? 'Client' }}</strong> <span style="float:right; font-weight:600;">${{ number_format($s->clientSchedulePayment->final_price ?? 0, 2) }}</span></span>
                                            <span style="color:#dc3545; font-size:12px;">Reason:
                                                {{ isset($s->clientSchedulePayment->reason) && $s->clientSchedulePayment->reason !== '' ? $s->clientSchedulePayment->reason : '-' }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                </td>

                {{-- Partial Column with Conditional Hover --}}
                <td>
                    @php
                        $partialCount = $schedules->filter(fn($s) => ($s->clientSchedulePayment->option_five ?? '') == 'partially')->count();
                    @endphp
                    @if ($partialCount > 0)
                        <div class="table_hover">
                            <h3>{{ $partialCount }}</h3>
                            <div class="tooltip_hover">
                                <ul>
                                    @foreach ($schedules->filter(fn($s) => ($s->clientSchedulePayment->option_five ?? '') == 'partially') as $s)
                                        <li style="display: flex; flex-direction: column; align-items: flex-start;">
                                            <span><strong>{{ $s->clientName->name ?? 'Client' }}</strong> <span style="float:right; font-weight:600;">${{ number_format($s->clientSchedulePayment->final_price ?? 0, 2) }}</span></span>
                                            <span style="color:#dc3545; font-size:12px;">Reason:
                                                {{ isset($s->clientSchedulePayment->reason) && $s->clientSchedulePayment->reason !== '' ? $s->clientSchedulePayment->reason : '-' }}</span>
                                            <span style="color:#007bff; font-size:12px;">Partial Scope:
                                                {{ isset($s->clientSchedulePayment->partial_completed_scope) && $s->clientSchedulePayment->partial_completed_scope !== '' ? $s->clientSchedulePayment->partial_completed_scope : '-' }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                </td>

                {{-- Reviewed Column (Route + Week level) --}}
                @if ($isAdminReportView)
                    <td>
                        @php
                            $routeReview = $allRouteReportReviews->where('route_id', $routeId)
                                ->where('week', $weekString)
                                ->where('month', $selectedMonthName)
                                ->where('year', $selectedYear)
                                ->first();
                            $isRouteReviewed = $routeReview ? $routeReview->is_reviewed : false;
                        @endphp
                        <input type="checkbox" class="form-check-input review-checkbox" style="width: 20px; height: 20px; cursor: pointer; margin-top: 0;"
                            data-route="{{ $routeId }}"
                            data-week="{{ $weekString }}"
                            data-month="{{ $selectedMonthName }}"
                            data-year="{{ $selectedYear }}"
                            {{ $isRouteReviewed ? 'checked' : '' }}>
                    </td>
                @endif
            </tr>
        @endforeach
    @endif
@endforeach
