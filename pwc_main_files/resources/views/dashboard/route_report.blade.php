@extends('theme.layout.master')
@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        html, body {
            color-scheme: light only;
        }

        .route_report_filters_wrapper {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .billed-amount {
            background: rgba(40, 167, 69, 0.08);
            color: #28a745;
            font-weight: bold;
            border-radius: 6px;
            padding: 8px 12px;
            display: inline-block;
        }

        button.btn_global.btn_dark_blue.clearAllFiltersBtn {
            width: 60px;
            height: 60px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        button.btn_global.btn_dark_blue.clearAllFiltersBtn i {
            font-size: 25px;
            margin: 0
        }

        .filter_selects_wrapper {
            display: flex;
            gap: 15px;
            flex: 1;
            max-width: 600px;
        }

        .filter_selects_wrapper .txt_field {
            flex: 1;
            min-width: 200px;
        }

        .filter_selects_wrapper .txt_field label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #32346A;
            font-size: 14px;
        }

        .filter_selects_wrapper .form-select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            color: #32346A;
            background-color: #fff;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter_selects_wrapper .form-select:focus {
            border-color: #00ADEE;
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 173, 238, 0.1);
        }

        /* Month Pagination Styling */
        .months-pagination {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .months-pagination .pag-btn {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 50px;
            color: #32346A;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .months-pagination .pag-btn:hover {
            background: rgba(0, 173, 238, 0.05);
            color: #fff;
            border-color: rgba(0, 173, 238, 0.05);
        }

        .dropdown_months_wrapper .btn {
            padding: 10px 20px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            color: #32346A;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .dropdown_months_wrapper .btn:hover {
            border-color: #00ADEE;
            background: rgba(0, 173, 238, 0.05);
        }

        .dropdown_months_wrapper .btn i {
            color: #00ADEE;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .route_report_filters_wrapper {
                flex-direction: column;
                align-items: stretch;
            }

            .filter_selects_wrapper {
                max-width: 100%;
                flex-direction: column;
            }

            .months-pagination {
                justify-content: center;
            }
        }

        /* Custom Tooltip Styling for Route Reports */
        .table_hover {
            position: relative;
            cursor: pointer;
        }

        .table_hover h3 {
            margin: 0;
            padding: 8px 12px;
            border-radius: 6px;
            transition: all 0.3s ease;
            background: rgba(0, 173, 238, 0.05);
            color: #32346A;
            font-size: 16px;
            font-weight: 600;
        }

        .table_hover:hover h3 {
            background: rgba(0, 173, 238, 0.15);
            transform: scale(1.05);
        }

        .tooltip_hover {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            margin-top: 10px;
            color-scheme: light only;
            background: #fff;
            border: none;
            border-radius: 12px;
            padding: 12px 16px;
            min-width: 260px;
            max-width: 360px;
            max-height: 340px;
            overflow-y: auto;
            box-shadow: 0 12px 32px rgba(20, 30, 60, 0.22), 0 2px 8px rgba(20, 30, 60, 0.1);
            opacity: 0;
            visibility: hidden;
            transition: all 0.25s ease;
            z-index: 1000;
        }

        .table_hover:hover .tooltip_hover {
            opacity: 1;
            visibility: visible;
            margin-top: 5px;
        }

        /* Arrow for tooltip */
        .tooltip_hover::before {
            content: '';
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 8px solid transparent;
            border-bottom-color: #32346A;
        }

        .tooltip_hover::after {
            content: '';
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 6px solid transparent;
            border-bottom-color: #fff;
            margin-bottom: -2px;
        }

        .tooltip_hover ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .tooltip_hover ul li {
            padding: 10px 8px;
            margin-bottom: 4px;
            border-bottom: 1px solid #eef0f5;
            border-radius: 6px;
            color: #1f2430;
            font-size: 13.5px;
            font-weight: 500;
            font-family: 'Hellix-Regular', sans-serif;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .tooltip_hover ul li:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .tooltip_hover ul li strong {
            color: #32346A;
            font-family: 'Hellix-SemiBold', sans-serif;
            margin-right: 10px;
        }

        .tooltip_hover .empty-state {
            display: block;
            width: 100%;
            text-align: center;
            color: #6c757d;
            font-style: italic;
            background: transparent !important;
        }

        /* Payment type / status badges inside tooltips */
        .tooltip_hover .payment-tag {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .3px;
            color: #fff;
            margin-top: 3px;
        }

        .tooltip_hover .payment-tag.cash {
            background: #1e8449;
        }

        .tooltip_hover .payment-tag.invoice {
            background: #0d6efd;
        }

        .tooltip_hover .service-date {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            color: #32346A;
            font-size: 12px;
            font-weight: 700;
            margin-top: 3px;
        }

        .tooltip_hover .service-date i {
            color: #00ADEE;
            font-size: 11px;
        }

        .tooltip_hover .reason-text,
        .tooltip_hover .scope-text {
            display: flex;
            align-items: flex-start;
            gap: 6px;
            width: 100%;
            margin-top: 8px;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 12.5px;
            font-weight: 600;
            line-height: 1.4;
        }

        .tooltip_hover .reason-text {
            background: #f8d7da;
            color: #842029;
            border-left: 4px solid #dc3545;
        }

        .tooltip_hover .scope-text {
            background: #cfe2ff;
            color: #084298;
            border-left: 4px solid #0d6efd;
        }

        .tooltip_hover .reason-text i,
        .tooltip_hover .scope-text i {
            margin-top: 2px;
            font-size: 11px;
        }

        .tooltip_hover .reason-text strong,
        .tooltip_hover .scope-text strong {
            color: inherit !important;
            margin-right: 4px;
        }

        /* ===== Sales Summary Popup (Total Sales tooltip) ===== */
        .sales-tooltip {
            background: rgba(255, 255, 255, 0.98);
            -webkit-backdrop-filter: blur(14px);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(15, 23, 42, 0.06);
            border-radius: 16px;
            padding: 14px;
            box-shadow: 0 24px 48px -12px rgba(15, 23, 42, 0.28), 0 4px 14px rgba(15, 23, 42, 0.1);
        }

        .sales-tooltip::before {
            border-bottom-color: rgba(15, 23, 42, 0.9);
        }

        .sales-tooltip .sales-tooltip-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: #94a3b8;
            padding: 0 2px 10px;
            margin: 0;
        }

        .sales-tooltip ul {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .sales-tooltip .customer-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            background: #f8fafc;
            border: 1px solid #eef1f6;
            border-radius: 10px;
            padding: 10px 12px;
            margin: 0;
            transition: all 0.18s ease;
        }

        .sales-tooltip .customer-card:hover {
            background: #ffffff;
            border-color: #cfe2ff;
            box-shadow: 0 6px 14px rgba(15, 23, 42, 0.08);
            transform: translateY(-1px);
        }

        .sales-tooltip .customer-info {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
            min-width: 0;
        }

        .sales-tooltip .customer-name {
            font-size: 13.5px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.3;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 190px;
        }

        .sales-tooltip .customer-price {
            font-size: 14.5px;
            font-weight: 800;
            color: #0f172a;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .sales-tooltip .payment-tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .2px;
            margin-top: 0;
        }

        .sales-tooltip .payment-tag::before {
            content: '';
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: currentColor;
        }

        .sales-tooltip .payment-tag.cash {
            background: #dcfce7;
            color: #15803d;
        }

        .sales-tooltip .payment-tag.invoice {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .sales-tooltip .payment-tag.pending {
            background: #ffedd5;
            color: #c2410c;
        }

        .sales-tooltip .payment-tag.omitted {
            background: #fee2e2;
            color: #b91c1c;
        }

        .sales-tooltip .payment-tag.partial {
            background: #ede9fe;
            color: #6d28d9;
        }

        .sales-tooltip .customer-info .service-date {
            color: #64748b;
            font-size: 11.5px;
            font-weight: 600;
        }

        .sales-tooltip .customer-info .service-date i {
            color: #94a3b8;
        }

        /* Stacked variant: for cards that carry an extra note (reason / scope) below */
        .sales-tooltip .customer-card.stacked {
            flex-direction: column;
            align-items: stretch;
            gap: 8px;
        }

        .sales-tooltip .customer-card.stacked .card-top-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            gap: 12px;
        }

        .sales-tooltip .customer-card .reason-text,
        .sales-tooltip .customer-card .scope-text {
            width: 100%;
            margin-top: 0;
        }

        .sales-tooltip .empty-state {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            background: #f8fafc !important;
            border: 1px dashed #dbe3ee;
            border-radius: 10px;
            color: #94a3b8;
            font-style: normal;
            font-weight: 600;
            font-size: 12.5px;
            padding: 14px;
        }

        .sales-tooltip .total-summary-row.danger {
            background: linear-gradient(135deg, #b91c1c 0%, #7f1d1d 100%);
            box-shadow: 0 8px 18px rgba(185, 28, 28, 0.32);
        }

        .sales-tooltip .total-summary-row.purple {
            background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
            box-shadow: 0 8px 18px rgba(124, 58, 237, 0.32);
        }

        .sales-tooltip .sales-divider {
            border: none;
            border-top: 1px solid #e7eaf1;
            margin: 12px 0 10px;
        }

        .sales-tooltip .total-summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #32346A 0%, #1a1c3d 100%);
            color: #fff;
            padding: 12px 14px;
            border-radius: 10px;
            box-shadow: 0 8px 18px rgba(50, 52, 106, 0.35);
        }

        .sales-tooltip .total-label {
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: rgba(255, 255, 255, 0.75);
        }

        .sales-tooltip .total-value {
            font-size: 19px;
            font-weight: 800;
            color: #fff;
            letter-spacing: .2px;
        }

        /* Responsive tooltip positioning */
        @media (max-width: 768px) {
            .tooltip_hover {
                min-width: 200px;
                max-width: 280px;
                font-size: 12px;
            }

            .sales-tooltip .customer-name {
                max-width: 130px;
            }
        }

        /* Week Distinction Styles */
        .week-header-row {
            background-color: #f0f4f8 !important;
            border-top: 4px solid #32346A !important;
            border-bottom: 2px solid #32346A !important;
        }

        .week-header-row h3 {
            color: #32346A !important;
            margin: 0;
            font-size: 1.2rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .week-spacer-row {
            height: 30px !important;
            background-color: #fff !important;
            border: none !important;
        }

        .week-spacer-row td {
            border: none !important;
            padding: 0 !important;
            background-color: #fff !important;
        }
    </style>
@endpush
@section('navbar-title')
    <div class="custom_justify_between">
        <h2 class="navbar_PageTitle">Routes Reports</h2>
    </div>

    <div class="custom_search txt_field custom_search">
        <input type="search" placeholder="Search" class="search_input custom_search_box">
        <i class="fa-solid fa-magnifying-glass search_icon"></i>
    </div>
@endsection
@section('content')
    @php
        $isAdminReportView = auth()->user()->hasRole('admin');
    @endphp
    <section class="client_management staff_manag route_report_section">
        <div class="container-fluid custom_container">
            <div class="row">
                <div class="col-md-12">
                    <div class="custom_div">
                        <div class="custom_justify_between">
                            <div class="custom_flex routes_head_content">
                                <div class="txt_field custom_select_route">
                                    <select name="route" id="routeFilter" class="form-select selectRoute" data-placeholder="Select Route">
                                        <option value="">All Routes</option>
                                        @forelse($routes as $route)
                                            <option value="{{ $route->id }}" {{ $selectedRouteId == $route->id ? 'selected' : '' }}>
                                                {{ $route->name }}
                                            </option>
                                        @empty
                                            <option value="" disabled>no route available</option>
                                        @endforelse
                                    </select>
                                </div>
                                @if($isAdminReportView)
                                    <div class="txt_field custom_select_route">
                                        <select name="staff" id="staffFilter" class="form-select selectRoute" data-placeholder="Select Staff">
                                            <option value="">All Staff</option>
                                            @forelse($staffs as $staff)
                                                <option value="{{ $staff->id }}" {{ $selectedStaffId == $staff->id ? 'selected' : '' }}>
                                                    {{ $staff->name }}
                                                </option>
                                            @empty
                                                <option value="" disabled>no staff available</option>
                                            @endforelse
                                        </select>
                                    </div>
                                @endif
                                <div class="months-pagination filter_download_dropdown_wrapper">
                                    <a href="{{ request()->fullUrlWithQuery(['month' => $previousMonth]) }}" type="button" class="pag-btn prevMonthBtn">
                                        <i class="fas fa-arrow-left"></i>
                                    </a>

                                    <div class="dropdown dropdown_months_wrapper">
                                        <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fa-regular fa-calendar"></i>
                                            <span class="selected_month_text">{{ $selectedMonth }}</span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            @foreach ($months as $month)
                                                <li>
                                                    <a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['month' => $month]) }}">
                                                        {{ $month }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>

                                    <a href="{{ request()->fullUrlWithQuery(['month' => $nextMonth]) }}" class="pag-btn nextMonthBtn" type="button">
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="custom_flex">
                                <button type="button" class="btn_global btn_dark_blue exportAllWeeksBtn" style="margin-right: 10px;">
                                    Export All <i class="fa-solid fa-file-excel"></i>
                                </button>
                                <button type="button" class="btn_global btn_dark_blue clearAllFiltersBtn">
                                    <i class="fa-solid fa-arrows-rotate"></i>
                                    <div class="tooltip_hover">
                                        <p>Refresh Route Report</p>
                                    </div>
                                </button>
                            </div>
                        </div>
                        <div class="custom_table route_report_table">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Route</th>
                                            @if ($isAdminReportView)
                                                <th>Staff Name</th>
                                            @endif
                                            <th>Total Sales</th>
                                            <th>Cash Received</th>
                                            <th>HRs</th>
                                            <th>Billed</th>
                                            <th>Unpaid Accounts</th>
                                            <th>Omit</th>
                                            <th>Partial</th>
                                            @if ($isAdminReportView)
                                                <th>Reviewed</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>

                                            @foreach ($data as $weekName => $weekRoutes)
                                                @php
                                                    $weekLabel = $weekName;
                                                    preg_match('/Week\s+(\d+)/', $weekName, $weekMatches);
                                                    $currentWeekNum = isset($weekMatches[1]) ? (int) $weekMatches[1] : 1;
                                                    $dbWeekNum = $currentWeekNum - 1;

                                                    preg_match('/\d{4}/', $selectedMonth ?? '', $yearMatch);
                                                    $selectedYear = $yearMatch[0] ?? now()->year;

                                                    $selectedMonthName = trim(str_replace($selectedYear, '', $selectedMonth ?? ''));
                                                @endphp

                                                @if (!$loop->first)
                                                    <tr class="week-spacer-row">
                                                        <td colspan="{{ $isAdminReportView ? 10 : 8 }}"></td>
                                                    </tr>
                                                @endif

                                                <tr class="week-header-row">
                                                    <td colspan="{{ $isAdminReportView ? 4 : 3 }}" style="text-align: left; padding-left: 20px;">
                                                        <h3>{{ $weekLabel }}</h3>
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
                                                    <td colspan="{{ $isAdminReportView ? 10 : 8 }}" class="text-center text-muted">No Schedule To This
                                                        Week
                                                    </td>
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
                                                        $routeName = $schedules->first()->clientName?->clientRouteStaff->first()->route->name ?? 'N/A';
                                                        $totalSales = $schedules->sum(fn($s) => $s->calculateMergedInvoiceAmount() ?: ($s->clientSchedulePayment->final_price ?? 0));
                                                        $cashSchedules = $schedules->filter(fn($s) => ($s->clientSchedulePayment->payment_type ?? '') == 'cash' && ($s->clientSchedulePayment->status ?? '') == 'paid' && ($s->clientSchedulePayment->option ?? '') != 'omit');
                                                        $cashRecord = $cashSchedules->sum(fn($s) => $s->calculateMergedInvoiceAmount() ?: ($s->clientSchedulePayment->final_price ?? 0));
                                                        $weekString = 'week' . $dbWeekNum;
                                                        $matchingDeposits = $allDeposits->where('route_id', $routeId)->where('week', $weekString)->where('month', $selectedMonthName)->where('year', $selectedYear);
                                                        $totalDeposited = $matchingDeposits->sum('deposit_amount');
                                                        $invoiceSchedules = $schedules->filter(fn($s) => ($s->clientSchedulePayment->payment_type ?? '') == 'invoice');
                                                        $invoiceTotal = $invoiceSchedules->sum(fn($s) => $s->calculateMergedInvoiceAmount() ?: ($s->clientSchedulePayment->final_price ?? 0));
                                                        $invoicePaid = $invoiceSchedules->filter(fn($s) => ($s->clientSchedulePayment->payment_status ?? null) == 'paid')->sum(fn($s) => $s->calculateMergedInvoiceAmount() ?: ($s->clientSchedulePayment->final_price ?? 0));
                                                        $invoiceUnpaid = $invoiceSchedules->filter(fn($s) => ($s->clientSchedulePayment->payment_status ?? null) === null)->sum(fn($s) => $s->calculateMergedInvoiceAmount() ?: ($s->clientSchedulePayment->final_price ?? 0));
                                                        $billed = $totalDeposited + $invoicePaid;
                                                        $cashUnpaidAcc = $schedules->filter(fn($s) => ($s->clientSchedulePayment->payment_type ?? '') == 'cash' && ($s->clientSchedulePayment->status ?? '') == 'pending');
                                                        $unPaidTotal = $cashUnpaidAcc->sum(fn($s) => $s->calculateMergedInvoiceAmount() ?: ($s->clientSchedulePayment->final_price ?? 0));

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

                                                         $staffName =
                                                            $schedules->first()?->StaffName?->first_name
                                                            ?? $schedules->first()?->StaffName?->name
                                                            ?? 'N/A';
                                                    @endphp
                                                    <tr class="route-invoice">
                                                        <td>{{ $routeName }}</td>
                                                        @if ($isAdminReportView)
                                                            <td>{{ $staffName }}</td>
                                                        @endif
                                                        {{-- Total Sales Column with Tooltip --}}
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
                                                        {{-- Cash Record Column with Hover --}}
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
                                                        {{-- HRs Column (Total Hours from Staff Log Hours) --}}
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
                                                                                    @if (!empty($s->clientSchedulePayment->reason))
                                                                                        <span class="reason-text"><i class="fa-solid fa-triangle-exclamation"></i><span><strong>Reason:</strong> {{ $s->clientSchedulePayment->reason }}</span></span>
                                                                                    @endif
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
                                                                                    @if (!empty($s->clientSchedulePayment->reason))
                                                                                        <span class="reason-text"><i class="fa-solid fa-triangle-exclamation"></i><span><strong>Reason:</strong> {{ $s->clientSchedulePayment->reason }}</span></span>
                                                                                    @endif
                                                                                    @if (!empty($s->clientSchedulePayment->partial_completed_scope))
                                                                                        <span class="scope-text"><i class="fa-solid fa-list-check"></i><span><strong>Partial Scope:</strong> {{ $s->clientSchedulePayment->partial_completed_scope }}</span></span>
                                                                                    @endif
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
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('js')
    {{-- XLSX Library for Excel Export --}}
    <script src="https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.bundle.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {

            $(".selectRoute").select2({
                allowClear: true
            });

            // Real-time AJAX filtering
            function loadRouteReportData() {
                let route = $('#routeFilter').val();
                let staff = $('#staffFilter').val();
                let month = $('.selected_month_text').text().trim() || '{{ $selectedMonth }}';

                // Show loading state
                $('.route_report_table tbody').html(`
                    <tr>
                        <td colspan="{{ $isAdminReportView ? 10 : 8 }}" class="text-center" style="padding: 50px;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3"><b> Loading Report...<i class="fa-solid fa-spinner fa-spin"></i></b></p>
                        </td>
                    </tr>
                `);

                $.ajax({
                    url: '{{ route('route.report.ajax') }}',
                    type: 'GET',
                    cache: false,
                    data: {
                        route: route,
                        staff: staff,
                        month: month,
                        _t: new Date().getTime()
                    },
                    success: function(response) {
                        $('.route_report_table tbody').html(response.html);

                        // Update selected month text
                        if (response.selectedMonth) {
                            $('.selected_month_text').text(response.selectedMonth);
                        }

                        // Update previous/next month buttons
                        if (response.previousMonth) {
                            $('.prevMonthBtn').attr('href', '?month=' + encodeURIComponent(response
                                .previousMonth));
                        }
                        if (response.nextMonth) {
                            $('.nextMonthBtn').attr('href', '?month=' + encodeURIComponent(response
                                .nextMonth));
                        }

                        // Update dropdown months with new year
                        if (response.months && response.months.length > 0) {
                            let dropdownHtml = '';
                            response.months.forEach(function(month) {
                                dropdownHtml += '<li><a class="dropdown-item" href="#">' +
                                    month + '</a></li>';
                            });
                            $('.dropdown_months_wrapper .dropdown-menu').html(dropdownHtml);

                            // Re-attach click event to new dropdown items
                            $('.dropdown-item').off('click').on('click', function(e) {
                                e.preventDefault();
                                let selectedMonth = $(this).text().trim();
                                $('.selected_month_text').text(selectedMonth);

                                // Update URL without reload
                                let url = new URL(window.location);
                                url.searchParams.set('month', selectedMonth);
                                window.history.pushState({}, '', url);

                                loadRouteReportData();
                            });
                        }
                        // Note: Export buttons are already bound with event delegation
                        // No need to re-bind them here as they use $(document).on()
                    },
                    error: function(xhr) {
                        $('.route_report_table tbody').html(`
                            <tr>
                                <td colspan="{{ $isAdminReportView ? 10 : 8 }}" class="text-center text-danger" style="padding: 50px;">
                                    <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                                    <p>Error loading data. Please try again.</p>
                                </td>
                            </tr>
                        `);
                    }
                });
            }

            // Route filter change
            $('#routeFilter').on('change', function() {
                loadRouteReportData();
            });

            // Staff filter change
            $('#staffFilter').on('change', function() {
                loadRouteReportData();
            });

            // Month dropdown click
            $('.dropdown-item').on('click', function(e) {
                e.preventDefault();
                let selectedMonth = $(this).text().trim();
                $('.selected_month_text').text(selectedMonth);

                // Update URL without reload
                let url = new URL(window.location);
                url.searchParams.set('month', selectedMonth);
                window.history.pushState({}, '', url);

                loadRouteReportData();
            });

            // Previous/Next month buttons - Use event delegation for dynamic buttons
            $(document).on('click', '.prevMonthBtn, .nextMonthBtn', function(e) {
                e.preventDefault();
                let monthUrl = $(this).attr('href');
                let urlParams = new URLSearchParams(monthUrl.split('?')[1]);
                let selectedMonth = urlParams.get('month');

                $('.selected_month_text').text(selectedMonth);

                // Update URL without reload
                let url = new URL(window.location);
                url.searchParams.set('month', selectedMonth);
                window.history.pushState({}, '', url);

                loadRouteReportData();
            });

            // Clear All Filters Button
            $('.clearAllFiltersBtn').on('click', function() {
                // Redirect to the base route report page
                window.location.href = "{{ route('route.report') }}";
            });

            // Toggle Review Checkbox
            $(document).on('change', '.review-checkbox', function() {
                let checkbox = $(this);
                let isChecked = checkbox.is(':checked') ? 1 : 0;
                let route = checkbox.data('route');
                let week = checkbox.data('week');
                let month = checkbox.data('month');
                let year = checkbox.data('year');

                checkbox.prop('disabled', true);

                $.ajax({
                    url: '{{ route('route.report.review.toggle') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        route_id: route,
                        week: week,
                        month: month,
                        year: year,
                        is_reviewed: isChecked
                    },
                    success: function(response) {
                        checkbox.prop('disabled', false);
                        Swal.fire({
                            icon: "success",
                            title: "Success",
                            text: `Route report status updated`,
                            confirmButtonColor: "#3085d6",
                            confirmButtonText: "OK"
                        });
                    },
                    error: function(xhr) {
                        checkbox.prop('disabled', false);
                        checkbox.prop('checked', !isChecked);
                        console.error('Error saving review status');
                    }
                });
            });

            // ========== EXCEL EXPORT FUNCTIONS (AJAX-BASED) ==========

            // Export Single Week - Using event delegation for dynamically loaded buttons
            $(document).on('click', '.exportWeekBtn', function() {
                let weekNum = $(this).data('week-num');
                let month = $('.selected_month_text').text().trim() || '{{ $selectedMonth }}';
                let route = $('#routeFilter').val();
                let staff = $('#staffFilter').val();

                // Build export URL
                let exportUrl = '{{ route('route.report.export') }}' +
                    '?type=single' +
                    '&week=' + weekNum +
                    '&month=' + encodeURIComponent(month) +
                    '&route=' + (route || '') +
                    '&staff=' + (staff || '');

                // Redirect to download
                window.location.href = exportUrl;
            });

            // Export All Weeks (1-4) - Using event delegation for consistency
            $(document).on('click', '.exportAllWeeksBtn', function() {
                let month = $('.selected_month_text').text().trim() || '{{ $selectedMonth }}';
                let route = $('#routeFilter').val();
                let staff = $('#staffFilter').val();

                // Build export URL
                let exportUrl = '{{ route('route.report.export') }}' +
                    '?type=all' +
                    '&month=' + encodeURIComponent(month) +
                    '&route=' + (route || '') +
                    '&staff=' + (staff || '');

                // Redirect to download
                window.location.href = exportUrl;
            });

            // OLD DOM-BASED EXPORT FUNCTIONS REMOVED - NOW USING AJAX-BASED EXPORT
            // The export now happens via backend route: route.report.export
            // This provides cleaner Excel files with proper formatting and no client details

            /* REMOVED OLD FUNCTIONS:
            - exportWeekData()
            - exportAllWeeksData()
            - createStyledExcel()
            - createStyledExcelWithComments()
            These are no longer needed as export is handled by backend
            */

            // Keep only the search functionality below
            function exportWeekData_OLD_REMOVED(weekName, weekNum) {
                let month = $('.selected_month_text').text().trim() || '{{ $selectedMonth }}';
                let formattedData = [];

                // Title Row
                formattedData.push([`Route Report - ${weekName} - ${month}`]);
                formattedData.push([]); // Empty row

                // Header Row
                formattedData.push([
                    'Route',
                    'Staff Name',
                    'Total Sales',
                    'Cash Record',
                    'HRs',
                    'Billed',
                    'Unpaid',
                    'Omit',
                    'Partial'
                ]);

                // Find ALL rows in the table (not just tbody)
                let allRows = $('.route_report_table tr');

                // Find the index of the week header
                let weekHeaderIndex = -1;
                allRows.each(function(index) {
                    let h3Text = $(this).find('h3').text().trim();
                    if (h3Text === weekName) {
                        weekHeaderIndex = index;
                        return false; // break
                    }
                });

                if (weekHeaderIndex === -1) {
                    Swal.fire({
                        icon: "warning",
                        title: "No Data Available!",
                        text: `Week section not found for ${weekName}.`,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "OK"
                    });
                    return;
                }

                // Find the next week header index (or end of table)
                // IMPORTANT: Check if h3 contains "Week" text, not just any h3
                let nextWeekHeaderIndex = allRows.length;
                for (let i = weekHeaderIndex + 1; i < allRows.length; i++) {
                    let h3Text = $(allRows[i]).find('h3').text().trim();
                    if (h3Text.startsWith('Week ')) {
                        nextWeekHeaderIndex = i;
                        break;
                    }
                }

                // Get all route-invoice rows between weekHeaderIndex and nextWeekHeaderIndex
                let dataRows = [];
                for (let i = weekHeaderIndex + 1; i < nextWeekHeaderIndex; i++) {
                    let row = $(allRows[i]);
                    if (row.hasClass('route-invoice')) {
                        dataRows.push(row);
                    }
                }

                // Extract data from each row - Combine main value + tooltip details
                dataRows.forEach(function(row, rowIdx) {
                    let rowData = [];

                    $(row).find('td').each(function(colIdx) {
                        let cell = $(this);
                        let mainValue = '';
                        let tooltipText = '';

                        // Get main value (h3 or text)
                        let cellClone = cell.clone();
                        cellClone.find('.tooltip_hover').remove();
                        mainValue = cellClone.find('h3').length > 0 ? cellClone.find('h3').text()
                            .trim() : cellClone.text().trim();
                        mainValue = mainValue.replace(/\s+/g, ' ').trim();

                        // Get tooltip data if exists
                        let tooltip = cell.find('.tooltip_hover');
                        if (tooltip.length > 0) {
                            let tooltipItems = [];
                            tooltip.find('li').each(function() {
                                let itemText = $(this).text().trim().replace(/\s+/g, ' ');
                                if (itemText && itemText !== 'No Cash Records' &&
                                    itemText !== 'No Timelogs') {
                                    tooltipItems.push('  ' +
                                        itemText); // Add indent for details
                                }
                            });
                            if (tooltipItems.length > 0) {
                                tooltipText = '\n' + tooltipItems.join('\n');
                            }
                        }

                        // Combine: Main value + tooltip details
                        rowData.push(mainValue + tooltipText);
                    });

                    if (rowData.length > 0) {
                        formattedData.push(rowData);
                    }
                });

                console.log('Formatted Data:', formattedData);

                // Check if data exists
                if (formattedData.length <= 3) {
                    Swal.fire({
                        icon: "warning",
                        title: "No Data Available!",
                        text: `There is no data to export for ${weekName}.`,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "OK"
                    });
                    return;
                }

                // Create Excel file with combined data (main value + details)
                createStyledExcel(formattedData, `Route_Report_${weekName}_${month}.xlsx`);
            }

            // Function to export all weeks data
            function exportAllWeeksData() {
                let month = $('.selected_month_text').text().trim() || '{{ $selectedMonth }}';
                let formattedData = [];

                // Title Row
                formattedData.push([`Route Report - All Weeks - ${month}`]);
                formattedData.push([]); // Empty row

                // Always loop through all 4 weeks, even if empty
                for (let weekNum = 1; weekNum <= 4; weekNum++) {
                    let weekName = '';
                    // Try to find the actual week label (with date range) in the table
                    let allRows = $('.route_report_table tr');
                    let weekHeaderIndex = -1;
                    let foundLabel = '';
                    allRows.each(function(index) {
                        let h3Text = $(this).find('h3').text().trim();
                        if (h3Text.startsWith(`Week ${weekNum}`)) {
                            weekHeaderIndex = index;
                            foundLabel = h3Text;
                            return false; // break
                        }
                    });
                    weekName = foundLabel || `Week ${weekNum}`;

                    // Find the next week header index (or end of table)
                    let nextWeekHeaderIndex = allRows.length;
                    for (let i = weekHeaderIndex + 1; i < allRows.length; i++) {
                        let h3Text = $(allRows[i]).find('h3').text().trim();
                        if (h3Text.startsWith('Week ')) {
                            nextWeekHeaderIndex = i;
                            break;
                        }
                    }

                    // Get all route-invoice rows between weekHeaderIndex and nextWeekHeaderIndex
                    let dataRows = [];
                    if (weekHeaderIndex !== -1) {
                        for (let i = weekHeaderIndex + 1; i < nextWeekHeaderIndex; i++) {
                            let row = $(allRows[i]);
                            if (row.hasClass('route-invoice')) {
                                dataRows.push(row);
                            }
                        }
                    }

                    // Week Header
                    formattedData.push([weekName]);
                    formattedData.push([]); // Empty row

                    // Column Headers
                    formattedData.push([
                        'Route',
                        'Staff Name',
                        'Total Sales',
                        'Cash Record',
                        'HRs',
                        'Billed',
                        'Unpaid',
                        'Omit',
                        'Partial'
                    ]);

                    // Extract data from each row - Combine main value + tooltip details
                    if (dataRows.length > 0) {
                        dataRows.forEach(function(row, rowIdx) {
                            let rowData = [];

                            $(row).find('td').each(function(colIdx) {
                                let cell = $(this);
                                let mainValue = '';
                                let tooltipText = '';

                                // Get main value (h3 or text)
                                let cellClone = cell.clone();
                                cellClone.find('.tooltip_hover').remove();
                                mainValue = cellClone.find('h3').length > 0 ? cellClone.find('h3')
                                    .text()
                                    .trim() : cellClone.text().trim();
                                mainValue = mainValue.replace(/\s+/g, ' ').trim();

                                // Get tooltip data if exists
                                let tooltip = cell.find('.tooltip_hover');
                                if (tooltip.length > 0) {
                                    let tooltipItems = [];
                                    tooltip.find('li').each(function() {
                                        let itemText = $(this).text().trim().replace(/\s+/g,
                                            ' ');
                                        if (itemText && itemText !== 'No Cash Records' &&
                                            itemText !== 'No Timelogs') {
                                            tooltipItems.push('  ' +
                                                itemText); // Add indent for details
                                        }
                                    });
                                    if (tooltipItems.length > 0) {
                                        tooltipText = '\n' + tooltipItems.join('\n');
                                    }
                                }

                                // Combine: Main value + tooltip details
                                rowData.push(mainValue + tooltipText);
                            });

                            if (rowData.length > 0) {
                                formattedData.push(rowData);
                            }
                        });
                    } else {
                        // Add an empty row to indicate No Schedule To This Week
                        formattedData.push(['No data available for this week', '', '', '', '', '', '', '', '']);
                    }

                    // Add spacing between weeks
                    formattedData.push([]);
                    formattedData.push([]);
                }

                console.log('All Weeks Formatted Data:', formattedData);

                // Always allow export, even if all weeks are empty (to match visible table)
                createStyledExcel(formattedData, `Route_Report_All_Weeks_${month}.xlsx`);
            }

            // Function to create styled Excel file WITH TOOLTIP DATA (separate sheet approach)
            function createStyledExcelWithComments(data, tooltipsData, fileName) {
                // Create main summary sheet
                let ws = XLSX.utils.aoa_to_sheet(data);

                // Set column widths
                const colWidths = [20, 20, 15, 15, 10, 15, 15, 10, 10];
                ws['!cols'] = colWidths.map(width => ({
                    wch: width
                }));

                // Create Details sheet with tooltip data
                let detailsData = [];
                detailsData.push(['ROUTE REPORT - DETAILED BREAKDOWN']);
                detailsData.push([]);
                detailsData.push(['Row', 'Route', 'Staff Name', 'Total Sales Details', 'Cash Record Details',
                    'HRs Details', 'Billed Details', 'Unpaid Details', 'Omit Details', 'Partial Details'
                ]);

                // Add tooltip data rows
                tooltipsData.forEach(function(rowTooltips, rowIdx) {
                    let detailRow = [rowIdx + 1]; // Row number

                    // Add main values from data sheet (route name, staff name)
                    if (data[rowIdx + 3]) { // +3 for title, empty row, headers
                        detailRow.push(data[rowIdx + 3][0] || ''); // Route
                        detailRow.push(data[rowIdx + 3][1] || ''); // Staff Name
                    } else {
                        detailRow.push('');
                        detailRow.push('');
                    }

                    // Add tooltip details for each column
                    rowTooltips.forEach(function(tooltipText) {
                        detailRow.push(tooltipText || '-');
                    });

                    detailsData.push(detailRow);
                });

                // Apply same styling as original function
                const titleStyle = {
                    font: {
                        bold: true,
                        sz: 16,
                        color: {
                            rgb: "FFFFFF"
                        }
                    },
                    fill: {
                        fgColor: {
                            rgb: "32346A"
                        }
                    },
                    alignment: {
                        horizontal: "center",
                        vertical: "center"
                    }
                };

                const headerStyle = {
                    font: {
                        bold: true,
                        sz: 12,
                        color: {
                            rgb: "FFFFFF"
                        }
                    },
                    fill: {
                        fgColor: {
                            rgb: "5bc4ea"
                        }
                    },
                    alignment: {
                        horizontal: "center",
                        vertical: "center"
                    },
                    border: {
                        top: {
                            style: "thin",
                            color: {
                                rgb: "000000"
                            }
                        },
                        bottom: {
                            style: "thin",
                            color: {
                                rgb: "000000"
                            }
                        },
                        left: {
                            style: "thin",
                            color: {
                                rgb: "000000"
                            }
                        },
                        right: {
                            style: "thin",
                            color: {
                                rgb: "000000"
                            }
                        }
                    }
                };

                const dataStyle = {
                    alignment: {
                        horizontal: "left",
                        vertical: "center"
                    },
                    border: {
                        top: {
                            style: "thin",
                            color: {
                                rgb: "CCCCCC"
                            }
                        },
                        bottom: {
                            style: "thin",
                            color: {
                                rgb: "CCCCCC"
                            }
                        },
                        left: {
                            style: "thin",
                            color: {
                                rgb: "CCCCCC"
                            }
                        },
                        right: {
                            style: "thin",
                            color: {
                                rgb: "CCCCCC"
                            }
                        }
                    }
                };

                // Apply styles to cells
                const range = XLSX.utils.decode_range(ws['!ref']);

                for (let R = range.s.r; R <= range.e.r; ++R) {
                    for (let C = range.s.c; C <= range.e.c; ++C) {
                        const cellAddress = XLSX.utils.encode_cell({
                            r: R,
                            c: C
                        });
                        if (!ws[cellAddress]) continue;

                        const cellValue = ws[cellAddress].v;

                        // Title row (first row)
                        if (R === 0) {
                            ws[cellAddress].s = titleStyle;
                        }
                        // Column headers (row 2)
                        else if (R === 2) {
                            ws[cellAddress].s = headerStyle;
                        }
                        // Data rows
                        else if (cellValue && cellValue !== '') {
                            ws[cellAddress].s = dataStyle;
                        }
                    }
                }

                // Merge title cell
                if (!ws['!merges']) ws['!merges'] = [];
                ws['!merges'].push({
                    s: {
                        r: 0,
                        c: 0
                    },
                    e: {
                        r: 0,
                        c: 8
                    }
                });

                // Set row heights
                if (!ws['!rows']) ws['!rows'] = [];
                ws['!rows'][0] = {
                    hpt: 30
                }; // Title row height

                // Create Details sheet
                let wsDetails = XLSX.utils.aoa_to_sheet(detailsData);

                // Set column widths for Details sheet
                const detailsColWidths = [8, 20, 20, 40, 40, 40, 40, 40, 15, 15];
                wsDetails['!cols'] = detailsColWidths.map(width => ({
                    wch: width
                }));

                // Style Details sheet
                const detailsTitleStyle = {
                    font: {
                        bold: true,
                        sz: 14,
                        color: {
                            rgb: "FFFFFF"
                        }
                    },
                    fill: {
                        fgColor: {
                            rgb: "32346A"
                        }
                    },
                    alignment: {
                        horizontal: "center",
                        vertical: "center"
                    }
                };

                const detailsHeaderStyle = {
                    font: {
                        bold: true,
                        sz: 11,
                        color: {
                            rgb: "FFFFFF"
                        }
                    },
                    fill: {
                        fgColor: {
                            rgb: "5bc4ea"
                        }
                    },
                    alignment: {
                        horizontal: "center",
                        vertical: "center",
                        wrapText: true
                    },
                    border: {
                        top: {
                            style: "thin",
                            color: {
                                rgb: "000000"
                            }
                        },
                        bottom: {
                            style: "thin",
                            color: {
                                rgb: "000000"
                            }
                        },
                        left: {
                            style: "thin",
                            color: {
                                rgb: "000000"
                            }
                        },
                        right: {
                            style: "thin",
                            color: {
                                rgb: "000000"
                            }
                        }
                    }
                };

                const detailsDataStyle = {
                    alignment: {
                        horizontal: "left",
                        vertical: "top",
                        wrapText: true
                    },
                    border: {
                        top: {
                            style: "thin",
                            color: {
                                rgb: "CCCCCC"
                            }
                        },
                        bottom: {
                            style: "thin",
                            color: {
                                rgb: "CCCCCC"
                            }
                        },
                        left: {
                            style: "thin",
                            color: {
                                rgb: "CCCCCC"
                            }
                        },
                        right: {
                            style: "thin",
                            color: {
                                rgb: "CCCCCC"
                            }
                        }
                    }
                };

                // Apply styles to Details sheet
                const detailsRange = XLSX.utils.decode_range(wsDetails['!ref']);
                for (let R = detailsRange.s.r; R <= detailsRange.e.r; ++R) {
                    for (let C = detailsRange.s.c; C <= detailsRange.e.c; ++C) {
                        const cellAddress = XLSX.utils.encode_cell({
                            r: R,
                            c: C
                        });
                        if (!wsDetails[cellAddress]) continue;

                        // Title row
                        if (R === 0) {
                            wsDetails[cellAddress].s = detailsTitleStyle;
                        }
                        // Header row
                        else if (R === 2) {
                            wsDetails[cellAddress].s = detailsHeaderStyle;
                        }
                        // Data rows
                        else if (wsDetails[cellAddress].v && wsDetails[cellAddress].v !== '') {
                            wsDetails[cellAddress].s = detailsDataStyle;
                        }
                    }
                }

                // Merge title cell in Details sheet
                if (!wsDetails['!merges']) wsDetails['!merges'] = [];
                wsDetails['!merges'].push({
                    s: {
                        r: 0,
                        c: 0
                    },
                    e: {
                        r: 0,
                        c: 9
                    }
                });

                // Set row heights for Details sheet
                if (!wsDetails['!rows']) wsDetails['!rows'] = [];
                wsDetails['!rows'][0] = {
                    hpt: 25
                };
                wsDetails['!rows'][2] = {
                    hpt: 40
                }; // Header row height

                // Create workbook with both sheets
                let wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, 'Summary');
                XLSX.utils.book_append_sheet(wb, wsDetails, 'Details');
                XLSX.writeFile(wb, fileName);

                Swal.fire({
                    icon: "success",
                    title: "Export Successful!",
                    text: "Excel file downloaded! Check 'Details' sheet for tooltip data.",
                    confirmButtonColor: "#28a745",
                    confirmButtonText: "OK"
                });
            }

            // Function to create styled Excel file
            function createStyledExcel(data, fileName) {
                let ws = XLSX.utils.aoa_to_sheet(data);

                // Set column widths - WIDER for tooltip details
                const colWidths = [20, 20, 35, 35, 25, 35, 35, 20, 20];
                ws['!cols'] = colWidths.map(width => ({
                    wch: width
                }));

                // Styling
                const titleStyle = {
                    font: {
                        bold: true,
                        sz: 16,
                        color: {
                            rgb: "FFFFFF"
                        }
                    },
                    fill: {
                        fgColor: {
                            rgb: "32346A"
                        }
                    },
                    alignment: {
                        horizontal: "center",
                        vertical: "center"
                    }
                };

                const headerStyle = {
                    font: {
                        bold: true,
                        sz: 12,
                        color: {
                            rgb: "FFFFFF"
                        }
                    },
                    fill: {
                        fgColor: {
                            rgb: "5bc4ea"
                        }
                    },
                    alignment: {
                        horizontal: "center",
                        vertical: "center"
                    },
                    border: {
                        top: {
                            style: "thin",
                            color: {
                                rgb: "000000"
                            }
                        },
                        bottom: {
                            style: "thin",
                            color: {
                                rgb: "000000"
                            }
                        },
                        left: {
                            style: "thin",
                            color: {
                                rgb: "000000"
                            }
                        },
                        right: {
                            style: "thin",
                            color: {
                                rgb: "000000"
                            }
                        }
                    }
                };

                const weekHeaderStyle = {
                    font: {
                        bold: true,
                        sz: 14,
                        color: {
                            rgb: "000000"
                        }
                    },
                    fill: {
                        fgColor: {
                            rgb: "f8f9fa"
                        }
                    },
                    alignment: {
                        horizontal: "left",
                        vertical: "center"
                    }
                };

                const dataStyle = {
                    alignment: {
                        horizontal: "left",
                        vertical: "center",
                        wrapText: true
                    },
                    border: {
                        top: {
                            style: "thin",
                            color: {
                                rgb: "CCCCCC"
                            }
                        },
                        bottom: {
                            style: "thin",
                            color: {
                                rgb: "CCCCCC"
                            }
                        },
                        left: {
                            style: "thin",
                            color: {
                                rgb: "CCCCCC"
                            }
                        },
                        right: {
                            style: "thin",
                            color: {
                                rgb: "CCCCCC"
                            }
                        }
                    }
                };

                // Apply styles to cells
                const range = XLSX.utils.decode_range(ws['!ref']);

                for (let R = range.s.r; R <= range.e.r; ++R) {
                    for (let C = range.s.c; C <= range.e.c; ++C) {
                        const cellAddress = XLSX.utils.encode_cell({
                            r: R,
                            c: C
                        });
                        if (!ws[cellAddress]) continue;

                        const cellValue = ws[cellAddress].v;

                        // Title row (first row)
                        if (R === 0) {
                            ws[cellAddress].s = titleStyle;
                        }
                        // Week headers (rows containing "Week 1", "Week 2", etc.)
                        else if (cellValue && typeof cellValue === 'string' && cellValue.startsWith('Week ')) {
                            ws[cellAddress].s = weekHeaderStyle;
                        }
                        // Column headers (rows with "Route", "Staff Name", etc.)
                        else if (cellValue === 'Route' || cellValue === 'Staff Name' || cellValue ===
                            'Total Sales') {
                            ws[cellAddress].s = headerStyle;
                        }
                        // Data rows
                        else if (cellValue && cellValue !== '') {
                            ws[cellAddress].s = dataStyle;
                        }
                    }
                }

                // Merge title cell
                if (!ws['!merges']) ws['!merges'] = [];
                ws['!merges'].push({
                    s: {
                        r: 0,
                        c: 0
                    },
                    e: {
                        r: 0,
                        c: 8
                    }
                });

                // Set row heights
                if (!ws['!rows']) ws['!rows'] = [];
                ws['!rows'][0] = {
                    hpt: 30
                }; // Title row height

                // Create workbook and download
                let wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, 'Route Report');
                XLSX.writeFile(wb, fileName);

                Swal.fire({
                    icon: "success",
                    title: "Export Successful!",
                    text: "Your Excel file has been downloaded.",
                    confirmButtonColor: "#28a745",
                    confirmButtonText: "OK"
                });
            }
        });
    </script>
@endpush
