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
                $totalSales = $schedules->sum(fn($s) => $s->calculateMergedInvoiceAmount() ?: ($s->clientSchedulePayment->final_price ?? 0));

                // Cash Logic
                $cashSchedules = $schedules->filter(fn($s) => ($s->clientSchedulePayment->payment_type ?? '') == 'cash' && ($s->clientSchedulePayment->status ?? '') == 'paid');
                $cashRecord = $cashSchedules->sum(fn($s) => $s->calculateMergedInvoiceAmount() ?: ($s->clientSchedulePayment->final_price ?? 0));

                // Deposits
                $matchingDeposits = $allDeposits->where('route_id', $routeId)->where('week', $weekString)->where('month', $selectedMonthName)->where('year', $selectedYear);
                $totalDeposited = $matchingDeposits->sum('deposit_amount');

                // Invoice Logic
                $invoiceSchedules = $schedules->filter(fn($s) => ($s->clientSchedulePayment->payment_type ?? '') == 'invoice');
                $invoiceTotal = $invoiceSchedules->sum(fn($s) => $s->calculateMergedInvoiceAmount() ?: ($s->clientSchedulePayment->final_price ?? 0));
                $invoicePaid = $invoiceSchedules->filter(fn($s) => ($s->clientSchedulePayment->payment_status ?? null) == 'paid')->sum(fn($s) => $s->calculateMergedInvoiceAmount() ?: ($s->clientSchedulePayment->final_price ?? 0));
                $invoiceUnpaid = $invoiceSchedules->filter(fn($s) => ($s->clientSchedulePayment->payment_status ?? null) === null)->sum(fn($s) => $s->calculateMergedInvoiceAmount() ?: ($s->clientSchedulePayment->final_price ?? 0));

                // Un Paid
                $cashUnpaidAcc = $schedules->filter(fn($s) => ($s->clientSchedulePayment->payment_type ?? '') == 'cash' && ($s->clientSchedulePayment->status ?? '') == 'pending');
                $unPaidTotal = $cashUnpaidAcc->sum(fn($s) => $s->calculateMergedInvoiceAmount() ?: ($s->clientSchedulePayment->final_price ?? 0));

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
                        <div class="tooltip_hover sales-tooltip" style="min-width: 320px;">
                            <p class="sales-tooltip-title">Sales Breakdown</p>
                            <ul>
                                @foreach ($schedules as $s)
                                    <li class="customer-card">
                                        <span class="customer-info">
                                            <span class="customer-name">{{ $s->clientName->name ?? 'Client' }}</span>
                                            <span class="payment-tag {{ ($s->clientSchedulePayment->payment_type ?? '') == 'cash' ? 'cash' : 'invoice' }}">
                                                {{ ucfirst($s->clientSchedulePayment->payment_type ?? 'N/A') }}
                                            </span>
                                        </span>
                                        <span class="customer-price">${{ number_format($s->calculateMergedInvoiceAmount() ?: ($s->clientSchedulePayment->final_price ?? 0), 2) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                            <hr class="sales-divider">
                            <div class="total-summary-row">
                                <span class="total-label">Total Sales</span>
                                <span class="total-value">${{ number_format($totalSales, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </td>

                <td>
                    <div class="table_hover">
                        <h3>{{ number_format($cashRecord, 2) }}</h3>
                        <div class="tooltip_hover sales-tooltip" style="min-width: 300px;">
                            <p class="sales-tooltip-title">Cash Received</p>
                            <ul>
                                @forelse ($cashSchedules as $s)
                                    <li class="customer-card">
                                        <span class="customer-info">
                                            <span class="customer-name">{{ $s->clientName->name ?? 'Client' }}</span>
                                        </span>
                                        <span class="customer-price">${{ number_format($s->calculateMergedInvoiceAmount() ?: ($s->clientSchedulePayment->final_price ?? 0), 2) }}</span>
                                    </li>
                                @empty
                                    <li class="empty-state">No Cash Records</li>
                                @endforelse
                            </ul>
                            @if ($cashSchedules->isNotEmpty())
                                <hr class="sales-divider">
                                <div class="total-summary-row">
                                    <span class="total-label">Total Cash</span>
                                    <span class="total-value">${{ number_format($cashRecord, 2) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </td>

                <td>
                    <div class="table_hover">
                        <h3>{{ $totalHours }}</h3>
                        @if ($staffLogHoursForRoute->count() > 0)
                            <div class="tooltip_hover sales-tooltip" style="min-width: 280px;">
                                <p class="sales-tooltip-title">Hours Logged</p>
                                <ul>
                                    @foreach ($staffLogHoursForRoute as $logEntry)
                                        <li class="customer-card">
                                            <span class="customer-name">{{ \Carbon\Carbon::parse($logEntry->service_date)->format('d M Y') }}</span>
                                            <span class="customer-price">{{ $logEntry->duration_hours }} hrs</span>
                                        </li>
                                    @endforeach
                                </ul>
                                <hr class="sales-divider">
                                <div class="total-summary-row">
                                    <span class="total-label">Total Hours</span>
                                    <span class="total-value">{{ $totalHours }} hrs</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </td>
                {{-- Billed Column (Cash Received + Invoice Paid) --}}
                <td>
                    <div class="table_hover">
                        <h3>{{ number_format($invoiceTotal, 2) }}</h3>
                        <div class="tooltip_hover sales-tooltip" style="min-width: 300px;">
                            <p class="sales-tooltip-title">Invoice Payments</p>
                            <ul>
                                @forelse ($schedules->filter(fn($s) => ($s->clientSchedulePayment->payment_type ?? '') == 'invoice') as $s)
                                    <li class="customer-card">
                                        <span class="customer-info">
                                            <span class="customer-name">{{ $s->clientName->name ?? 'Client' }}</span>
                                            <span class="service-date"><i class="fa-regular fa-calendar"></i>{{ $s->service_date ? \Carbon\Carbon::parse($s->service_date)->format('d M Y') : 'N/A' }}</span>
                                        </span>
                                        <span class="customer-price">${{ number_format($s->calculateMergedInvoiceAmount() ?: ($s->clientSchedulePayment->final_price ?? 0), 2) }}</span>
                                    </li>
                                @empty
                                    <li class="empty-state">No Cash Records</li>
                                @endforelse
                            </ul>
                            @if ($invoiceSchedules->isNotEmpty())
                                <hr class="sales-divider">
                                <div class="total-summary-row">
                                    <span class="total-label">Total Billed</span>
                                    <span class="total-value">${{ number_format($invoiceTotal, 2) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </td>

                {{-- Unpaid Column with Tooltip --}}
                <td>
                    <div class="table_hover">
                        <h3>{{ number_format($unPaidTotal, 2) }}</h3>
                        <div class="tooltip_hover sales-tooltip" style="min-width: 300px;">
                            <p class="sales-tooltip-title">Unpaid Accounts</p>
                            <ul>
                                @forelse ($cashUnpaidAcc as $s)
                                    <li class="customer-card">
                                        <span class="customer-info">
                                            <span class="customer-name">{{ $s->clientName->name ?? 'Client' }}</span>
                                            <span class="service-date"><i class="fa-regular fa-calendar"></i>{{ $s->service_date ? \Carbon\Carbon::parse($s->service_date)->format('d M Y') : 'N/A' }}</span>
                                        </span>
                                        <span class="customer-price">${{ number_format($s->calculateMergedInvoiceAmount() ?: ($s->clientSchedulePayment->final_price ?? 0), 2) }}</span>
                                    </li>
                                @empty
                                    <li class="empty-state">No Cash Records</li>
                                @endforelse
                            </ul>
                            @if ($cashUnpaidAcc->isNotEmpty())
                                <hr class="sales-divider">
                                <div class="total-summary-row">
                                    <span class="total-label">Total Unpaid</span>
                                    <span class="total-value">${{ number_format($unPaidTotal, 2) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </td>


                {{-- Omit Column with Conditional Hover --}}
                <td>
                    @php
                        $omitSchedules = $schedules->filter(fn($s) => ($s->clientSchedulePayment->option ?? '') == 'omit');
                        $omitCount = $omitSchedules->count();
                        $omitTotal = $omitSchedules->sum(fn($s) => $s->calculateMergedInvoiceAmount() ?: ($s->clientSchedulePayment->final_price ?? 0));
                    @endphp
                    @if ($omitCount > 0)
                        <div class="table_hover">
                            <h3>{{ $omitCount }}</h3>
                            <div class="tooltip_hover sales-tooltip" style="min-width: 300px;">
                                <p class="sales-tooltip-title">Omitted Services</p>
                                <ul>
                                    @foreach ($omitSchedules as $s)
                                        <li class="customer-card stacked">
                                            <div class="card-top-row">
                                                <span class="customer-name">{{ $s->clientName->name ?? 'Client' }}</span>
                                                <span class="customer-price">${{ number_format($s->calculateMergedInvoiceAmount() ?: ($s->clientSchedulePayment->final_price ?? 0), 2) }}</span>
                                            </div>
                                            <span class="reason-text"><i class="fa-solid fa-triangle-exclamation"></i><span><strong>Reason:</strong> {{ isset($s->clientSchedulePayment->reason) && $s->clientSchedulePayment->reason !== '' ? $s->clientSchedulePayment->reason : '-' }}</span></span>
                                        </li>
                                    @endforeach
                                </ul>
                                <hr class="sales-divider">
                                <div class="total-summary-row danger">
                                    <span class="total-label">Total Omitted</span>
                                    <span class="total-value">${{ number_format($omitTotal, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </td>

                {{-- Partial Column with Conditional Hover --}}
                <td>
                    @php
                        $partialSchedules = $schedules->filter(fn($s) => ($s->clientSchedulePayment->option_five ?? '') == 'partially');
                        $partialCount = $partialSchedules->count();
                        $partialTotal = $partialSchedules->sum(fn($s) => $s->calculateMergedInvoiceAmount() ?: ($s->clientSchedulePayment->final_price ?? 0));
                    @endphp
                    @if ($partialCount > 0)
                        <div class="table_hover">
                            <h3>{{ $partialCount }}</h3>
                            <div class="tooltip_hover sales-tooltip" style="min-width: 300px;">
                                <p class="sales-tooltip-title">Partially Completed</p>
                                <ul>
                                    @foreach ($partialSchedules as $s)
                                        <li class="customer-card stacked">
                                            <div class="card-top-row">
                                                <span class="customer-name">{{ $s->clientName->name ?? 'Client' }}</span>
                                                <span class="customer-price">${{ number_format($s->calculateMergedInvoiceAmount() ?: ($s->clientSchedulePayment->final_price ?? 0), 2) }}</span>
                                            </div>
                                            <span class="reason-text"><i class="fa-solid fa-triangle-exclamation"></i><span><strong>Reason:</strong> {{ isset($s->clientSchedulePayment->reason) && $s->clientSchedulePayment->reason !== '' ? $s->clientSchedulePayment->reason : '-' }}</span></span>
                                            <span class="scope-text"><i class="fa-solid fa-list-check"></i><span><strong>Partial Scope:</strong> {{ isset($s->clientSchedulePayment->partial_completed_scope) && $s->clientSchedulePayment->partial_completed_scope !== '' ? $s->clientSchedulePayment->partial_completed_scope : '-' }}</span></span>
                                        </li>
                                    @endforeach
                                </ul>
                                <hr class="sales-divider">
                                <div class="total-summary-row purple">
                                    <span class="total-label">Total Partial</span>
                                    <span class="total-value">${{ number_format($partialTotal, 2) }}</span>
                                </div>
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
