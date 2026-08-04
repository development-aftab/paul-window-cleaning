@extends('theme.layout.master')
@push('css')
    <style>
        .months-pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .months-pagination .pag-btn {
            width: 50px;
            height: 50px;
            border-radius: 50px;
            border: 1px solid black;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 10px;
        }

        .months-pagination .pag-btn i {
            color: black;
            font-size: 16px;
        }

        .select_radio_button {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        body .details_routes_wrapper .select_radio_button button.btn.select_arrow_btn {
            padding: 0px 10px !important;
        }

        body .details_routes_wrapper .select_radio_button button.btn.select_arrow_btn.btn_purple {
            background: rebeccapurple;
            color: white;
        }

        body .details_routes_wrapper .select_radio_button button.btn.select_arrow_btn i {
            color: white;
            padding: 0;
            font-size: 10px;
        }

        body .details_routes_wrapper .select_radio_button button.btn.select_arrow_btn.btn_yellow {
            background: #ffc700;
        }
    </style>
@endpush
@section('navbar-title')
    <div class="back_btn_navbar back_btn_navbar_create_staff">
        <a href="{{ url('staffroutes') }}">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h2 class="navbar_PageTitle">{{ $staffRoute->name ?? '' }}</h2>
    </div>
    <div class="custom_search txt_field custom_search">
        <input type="search" placeholder="Search" class="search_input">
        <i class="fa-solid fa-magnifying-glass search_icon"></i>
    </div>
@endsection
@section('content')

    @if (auth()->user()->hasRole('admin'))
        <section class="create_staff_member_two_sec routes_detail_sec">
            <div class="container-fluid custom_container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="tabs_wrapper shadow_box_wrapper">
                            <div class="filter_download_dropdown_wrapper">

                                <div class="months-pagination">
                                    <a href="?month={{ urlencode($previousMonth) }}&tab=all_staff" type="button" class="pag-btn prevMonthBtn" id="prevMonthBtn_all_staff">
                                        <i class="fas fa-arrow-left"></i>
                                    </a>

                                    <div class="dropdown dropdown_months_wrapper">
                                        <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fa-regular fa-calendar selected_month_ajax" selected-month="{{ $selectedMonth }}" staff-route-id="{{ $staffRoute->id }}" staff-route-name="{{ $staffRoute->name }}"></i>
                                            {{ $selectedMonth }}
                                        </button>
                                        <ul class="dropdown-menu">
                                            @foreach ($months as $month)
                                                <li>
                                                    <a class="dropdown-item" href="?month={{ urlencode($month) }}">
                                                        {{ $month }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>

                                    <a href="?month={{ urlencode($nextMonth) }}&tab=all_staff" class="pag-btn" type="button" id="nextMonthBtn">
                                        <i class="fas fa-arrow-right"></i>
                                    </a>

                                    <a href="?month={{ urlencode($nextYearFirstMonth) }}&tab=all_staff" class="pag-btn btn_global btn_dark_blue" type="button" id="nextYearBtn" style="width: fit-content;">
                                        Next Year <i class="fas fa-forward"></i>
                                    </a>
                                </div>
                                <div class="searchbar_download_filter_wrapper">
                                    <button type="button" id="exportExcel" class="btn_global btn_dark_blue exportBtn exportExcel">Export Excel <i class="fa-solid fa-file-excel"></i></button>
                                    <div class="dropdown btn-primary">
                                        <button class="btn dropdown-toggle" type="button" id="sortButton" data-bs-toggle="dropdown" aria-expanded="false">
                                            Filter
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="sortButton">
                                            <li><a class="dropdown-item" href="#" id="sortAtoZ">A to Z</a></li>
                                            <li><a class="dropdown-item" href="#" id="sortMostRecent">Most Recent</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="sectionToPrint staff_routes_week_row">

                                @foreach ($mergedSchedules as $key => $schedule)
                                    @php
                                        $cashTotal = 0;
                                        $cashPendingTotal = 0;
                                        $cashPaidTotal = 0;
                                        $invoiceTotal = 0;

                                        foreach ($schedule['routes'] as $route) {
                                            $amount = $route['invoice_amount'] ?? 0;
                                            if (($route['payment_type'] ?? '') === 'cash') {
                                                $cashTotal += $amount;
                                                if (($route['payment_status'] ?? '') === 'paid') {
                                                    $cashPaidTotal += $amount;
                                                } else {
                                                    $cashPendingTotal += $amount;
                                                }
                                            } elseif (($route['payment_type'] ?? '') === 'invoice') {
                                                $invoiceTotal += $amount;
                                            }
                                        }
                                        $total = $cashTotal + $invoiceTotal;
                                        $grandTotal = $invoiceTotal + $cashPaidTotal;

                                        $routeId = $staffRoute->id ?? null;
                                    @endphp
                                    <div class="staff_routes_week_wrapper">
                                        <div class="details_routes_wrapper">
                                            <div class="week_wrapper">
                                                <div>
                                                    <h4>Week {{ $schedule['week_number'] }}</h4>
                                                    <h4 style="font-size: 12px">
                                                        {{ \Carbon\Carbon::parse($schedule['start_date'])->format('d F') }}
                                                        - {{ \Carbon\Carbon::parse($schedule['end_date'])->format('d F') }}
                                                    </h4>
                                                    <div class="collapse_btn">
                                                        <button type="button" class="btn_global btn_dark_blue"><i class="fa-solid fa-arrow-right"></i>
                                                            <div class="hoverable_txt"><span>Collapsing week For Full
                                                                    View</span>
                                                            </div>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="week_details_wrapper">
                                                    <div class="week_details_wrapper_total">
                                                        <label>Total Expected Sales :</label>
                                                        <span>${{ number_format($total, 2) }}</span>
                                                    </div>
                                                    <div>
                                                        <label>Expected Cash Received :</label>
                                                        <span>${{ number_format($cashPendingTotal, 2) }}</span>
                                                    </div>
                                                    <div class="week_details_wrapper_total">
                                                        <label>Cash rec. to date :</label>
                                                        <span>${{ number_format($cashPaidTotal, 2) }}</span>
                                                    </div>
                                                    <div>
                                                        <label>Invoice Total :</label>
                                                        <span>${{ number_format($invoiceTotal, 2) }}</span>
                                                    </div>
                                                    <div class="week_details_wrapper_total">
                                                        <label>Grand Total :</label>
                                                        <span>${{ number_format($grandTotal, 2) }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Select All Section - Above Routes -->
                                            @if (count($schedule['routes']) > 0)
                                                <div class="select_radio_button">
                                                    <div style="display: flex; align-items: center; gap: 10px;">
                                                        <input type="checkbox" name="select_all" class="select-all-week" data-week="{{ $schedule['week_number'] }}" style="width: 20px; height: 20px; cursor: pointer;">
                                                        <label style="margin: 0; font-weight: bold; cursor: pointer;" onclick="$(this).prev('input').click();">
                                                            Select All
                                                        </label>
                                                    </div>
                                                    <div style="display: none; flex-direction: column; gap: 10px; align-items: flex-end;" class="move-controls-container" data-week="{{ $schedule['week_number'] }}">
                                                        <!-- Move Type Selection -->
                                                        <div style="display: flex; gap: 10px; align-items: center;" class="move-type-container" data-week="{{ $schedule['week_number'] }}">
                                                            <!-- <label style="margin: 0; font-weight: bold;">Move Type:</label> -->
                                                            <div style="display: flex; gap: 10px;">
                                                                <label style="margin: 0; cursor: pointer; display: flex; align-items: center; gap: 5px;" title="Temporary Move - Move selected schedules temporarily (dates only)">
                                                                    <i class="fa-solid fa-arrow-left" style="font-size: 16px;"></i>
                                                                    <input type="radio" name="move_type_{{ $schedule['week_number'] }}" value="particular" class="move-type-radio" data-week="{{ $schedule['week_number'] }}" checked>
                                                                    <i class="fa-solid fa-arrow-right" style="font-size: 16px;"></i>
                                                                </label>
                                                                <label style="margin: 0; cursor: pointer; display: flex; align-items: center; gap: 5px;" title="Permanent Move - Permanently move ONLY selected schedules to next week">
                                                                    <input type="radio" name="move_type_{{ $schedule['week_number'] }}" value="selective_permanent" class="move-type-radio" data-week="{{ $schedule['week_number'] }}">
                                                                    <i class="fa-solid fa-check-double" style="font-size: 16px; color: #28a745;"></i>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <!-- Bulk Move Buttons - Only Next Week -->
                                                        <div style="display: flex; gap: 10px;" class="bulk-move-buttons-container" data-week="{{ $schedule['week_number'] }}">
                                                            <button type="button" class="btn select_arrow_btn btn_yellow bulk-move-btn" data-week="{{ $schedule['week_number'] }}" data-direction="next">
                                                                <span class="btn-icon"><i class="fa-solid fa-arrow-right"></i></span>
                                                                <span class="btn-text">Temporary Move </span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="routes_wrapper" id="week-{{ $schedule['week_number'] }}" data-week="{{ $schedule['week_number'] }}">
                                                @foreach ($schedule['routes'] as $key => $route)
                                                    <div class="muller_honda_wrapper muller_honda_wrapper_update" data-schedule-id="{{ $route['schedule_id'] }}" data-client-name="{{ $route['client_name'] }}" data-date="{{ $route['created_at'] ?? now() }}">
                                                        <div class="accordion" id="accordion-{{ $schedule['week_number'] }}-{{ $key }}">
                                                            <div class="accordion-item">
                                                                <h2 class="accordion-header" style="display: flex; align-items: center; gap: 10px;">
                                                                    <input type="checkbox" class="schedule-checkbox" data-week="{{ $schedule['week_number'] }}" data-schedule-id="{{ $route['schedule_id'] }}" data-client-name="{{ $route['client_name'] }}" data-status="{{ $route['is_completed'] }}" data-service-frequency="{{ $route['service_frequency'] ?? '' }}"
                                                                        style="width: 18px; height: 18px; cursor: pointer; margin-left: 10px;">
                                                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne-{{ $schedule['week_number'] }}-{{ $key }}" aria-expanded="false" aria-controls="collapseOne-{{ $schedule['week_number'] }}-{{ $key }}" style="flex: 1;">
                                                                        {{ ucfirst($route['client_name']) }}
                                                                        @if ($route['service_frequency'] == 'monthly' || $route['service_frequency'] == 'biMonthly')
                                                                            <i class="fas fa-edit edit-monthly-schedule-btn" data-schedule-id="{{ $route['schedule_id'] }}" data-client-name="{{ $route['client_name'] }}" data-start-date="{{ $route['client_start_week'] }}" data-end-date="{{ $route['client_end_week'] }}" data-service-frequency="{{ $route['service_frequency'] }}"
                                                                                style="margin-left: 10px; color: #007bff; cursor: pointer; font-size: 14px;" title="Edit Schedule Date"></i>
                                                                        @endif
                                                                    </button>
                                                                    <span class="schedule-error-message" data-schedule-id="{{ $route['schedule_id'] }}" style="color: red; font-weight: bold; font-size: 9px; display: none; margin-left: 10px;">
                                                                    </span>
                                                                </h2>

                                                                <div id="collapseOne-{{ $schedule['week_number'] }}-{{ $key }}" class="accordion-collapse collapse" data-bs-parent="#accordion-{{ $schedule['week_number'] }}-{{ $key }}">
                                                                    <div class="accordion-body">
                                                                        <div>
                                                                            <div class="muller_honda_details">
                                                                                <div>
                                                                                    @php
                                                                                        $parts = array_filter([$route['house_no'] ?? null, $route['address'] ?? null, $route['city'] ?? null, $route['state'] ?? null, $route['zip_code'] ?? null]);
                                                                                    @endphp

                                                                                    <span><strong>{{ $parts ? implode(' ', $parts) : 'Not Available' }}</strong></span>
                                                                                </div>
                                                                                <div class="d-flex flex-row">
                                                                                    <span><strong>{{ ucfirst($route['payment_type']) }}
                                                                                            : </strong></span>
                                                                                    <span>${{ number_format($route['invoice_amount'], 2) }}</span>
                                                                                </div>
                                                                                <div class="d-flex flex-row">
                                                                                    <label>Scope :</label>
                                                                                    <div class="d-flex flex-column">
                                                                                        @foreach ($route['multiPrice'] ?? [] as $index => $price)
                                                                                            @if (!empty($price['name']))
                                                                                                <span>{{ $price['name'] }}{{ $index < count($route['multiPrice']) - 1 ? ',' : '' }}</span>
                                                                                            @endif
                                                                                        @endforeach
                                                                                    </div>
                                                                                </div>
                                                                                <div class="d-flex flex-row flex-wrap">
                                                                                    <span><strong>Note :</strong></span>
                                                                                    <span>{{ str_ireplace(', null', '', str_ireplace('null', '', $route['note'])) }}</span>
                                                                                </div>
                                                                                <div class="d-flex flex-row flex-wrap">
                                                                                    <label>Best Time To Service :</label>
                                                                                    @foreach (collect($route['client_hours'] ?? [])->sortBy('start_hour') as $hour)
                                                                                        <span>{{ $hour['start_hour'] ?? '' }} - {{ $hour['end_hour'] ?? '' }}</span>
                                                                                    @endforeach
                                                                                </div>
                                                                                <div class="d-flex flex-row flex-wrap">
                                                                                    <label>Closed :</label>
                                                                                    @php
                                                                                        $days = collect($route['client_unavailable_days'] ?? [])
                                                                                            ->pluck('day')
                                                                                            ->map(function ($day) {
                                                                                                return ucfirst(strtolower($day));
                                                                                            })
                                                                                            ->implode(' , ');
                                                                                    @endphp

                                                                                    <span>{{ $days ?: '-' }}</span>
                                                                                </div>

                                                                            </div>
                                                                        </div>
                                                                        @if ($route['clientSchedule'] == 'completed')
                                                                            <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#completedModal">
                                                                                <div class="completed_wrapper">
                                                                                    <i class="fa-solid fa-check"></i>
                                                                                    @if (isset($route['payment_type']) && $route['payment_type'] == 'invoice')
                                                                                        <a href="{{ url('view_client_invoice/' . $route['client_id']) }}?start_date={{ $route['client_start_week'] }}&end_date={{ $route['client_end_week'] }}">
                                                                                            <h5>Complete</h5>
                                                                                        </a>
                                                                                    @elseif(isset($route['payment_type']) && $route['payment_type'] == 'cash')
                                                                                        <a href="{{ url('view_client_cash' . '/' . $route['client_id']) }}?start_date={{ $route['client_start_week'] }}&end_date={{ $route['client_end_week'] }}">
                                                                                            <h5>Complete</h5>
                                                                                        </a>
                                                                                    @endif
                                                                                    {{--                                                                        <h5>Completed</h5> --}}
                                                                                </div>
                                                                            </a>
                                                                        @elseif($route['clientSchedule'] == 'pending')
                                                                            <div class="mark_as_complete_wrapper mt-4">
                                                                                <h5>Pending</h5>
                                                                            </div>
                                                                        @endif
                                                                        <a href="#" data-note="{{ $route['note'] }}" data-schedule-id="{{ $route['schedule_id'] }}" data-client-price-list="{{ is_array($route['client_price_list']) ? json_encode($route['client_price_list']) : json_encode($route['client_price_list']->toArray()) }}" data-bs-toggle="modal" data-bs-target="#editNoteModal">
                                                                            <div class="completed_wrapper">
                                                                                <i class="fa-solid fa-edit"></i>
                                                                                <h5>Edit Note</h5>
                                                                            </div>
                                                                        </a>
                                                                        @php
                                                                            $weekNumber = (int) preg_replace('/[^0-9]/', '', $route['week'] ?? 'week0');
                                                                        @endphp
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @elseif(auth()->user()->hasRole('staff'))
        <section class="create_staff_member_two_sec">
            <div class="container-fluid custom_container">
                <div class="row custom_row">
                    <div class="col-md-12">
                        <div class="tabs_wrapper shadow_box_wrapper custom_row">
                            <div class="create_staff_tabs_btn_wrapper">
                                <ul class="nav nav-tabs" id="myTab_staff" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link @if (request()->query('tab') === 'all_staff' || !request()->query('tab')) active @endif" id="all-tab_staff" data-bs-toggle="tab" data-bs-target="#all_staff" type="button" role="tab" aria-controls="all_staff" aria-selected="true">All
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link @if (request()->query('tab') === 'inprogress_staff') active @endif" id="inprogress-tab_staff" data-bs-toggle="tab" data-bs-target="#inprogress_staff" type="button" role="tab" aria-controls="inprogress_staff" aria-selected="false">In-Progress
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link @if (request()->query('tab') === 'completed_staff') active @endif" id="completed-tab_staff" data-bs-toggle="tab" data-bs-target="#completed_staff" type="button" role="tab" aria-controls="completed_staff" aria-selected="false">Completed
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            <div class="tab-content" id="myTabContent_staff">
                                <div class="tab-pane fade @if (request()->query('tab') === 'all_staff' || !request()->query('tab')) show active @endif" id="all_staff" role="tabpanel" aria-labelledby="all-tab_staff">
                                    <div class="filter_download_dropdown_wrapper filter_download_dropdown_wrapper_all">

                                        <div class="months-pagination">
                                            <a href="?month={{ urlencode($previousMonth) }}&tab=all_staff" type="button" class="pag-btn prevMonthBtn" id="prevMonthBtn_all_staff2">
                                                <i class="fas fa-arrow-left"></i>
                                            </a>

                                            <div class="dropdown dropdown_months_wrapper">
                                                <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fa-regular fa-calendar selected_month_ajax" selected-month="{{ $selectedMonth }}" staff-route-id="{{ $staffRoute->id }}" staff-route-name="{{ $staffRoute->name }}"></i>
                                                    {{ $selectedMonth }}
                                                </button>
                                                <ul class="dropdown-menu">
                                                    @foreach ($months as $month)
                                                        <li>
                                                            <a class="dropdown-item" href="?month={{ urlencode($month) }}&tab=all_staff">
                                                                {{ $month }}
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>

                                            <a href="?month={{ urlencode($nextMonth) }}&tab=all_staff" class="pag-btn" type="button" id="nextMonthBtn">
                                                <i class="fas fa-arrow-right"></i>
                                            </a>

                                            <a href="?month={{ urlencode($nextYearFirstMonth) }}&tab=all_staff" class="pag-btn btn_global btn_dark_blue" type="button" id="nextYearBtn2" style="width: fit-content;">
                                                Next Year <i class="fas fa-forward"></i>
                                            </a>
                                        </div>

                                        <div class="searchbar_download_filter_wrapper">
                                            <button type="button" id="exportExcel" class="btn_global btn_dark_blue exportBtn exportExcel">Export Excel
                                                <i class="fa-solid fa-file-excel"></i></button>
                                        </div>
                                    </div>
                                    {{-- <div class="staff_routes_week_row">

                                        @foreach ($mergedSchedules as $schedule)
                                            @php
                                                $cashTotal = 0;
                                                $invoiceTotal = 0;

                                                foreach ($schedule['routes'] as $route) {
                                                    if ($route['payment_type'] === 'cash') {
                                                        $cashTotal += $route['invoice_amount'];
                                                    } elseif ($route['payment_type'] === 'invoice') {
                                                        $invoiceTotal += $route['invoice_amount'];
                                                    }
                                                }

                                                $total = $cashTotal + $invoiceTotal;
                                                $completedTotal = $schedule['routes']->where('is_completed', 'completed')->sum('invoice_amount');
                                            @endphp

                                            <div class="staff_routes_week_wrapper allTab">
                                                <div class="details_routes_wrapper">
                                                    <div class="week_wrapper">
                                                        <div>
                                                            <h4>Week {{ $schedule['week_number'] }}</h4>
                                                            <h4>{{ \Carbon\Carbon::parse($schedule['start_date'])->format('d F') }}
                                                                -
                                                                {{ \Carbon\Carbon::parse($schedule['end_date'])->format('d F') }}
                                                            </h4>
                                                            <div class="collapse_btn">
                                                                <button type="button" class="btn_global btn_dark_blue">
                                                                    <i class="fa-solid fa-arrow-right"></i>
                                                                    <div class="hoverable_txt">
                                                                        <span>Collapsing week For Full View</span>
                                                                    </div>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div class="week_details_wrapper">
                                                            <div>
                                                                <label>Cash Total :</label>
                                                                <span>${{ number_format($cashTotal, 2) }}</span>
                                                            </div>
                                                            <div>
                                                                <label>Invoice Total :</label>
                                                                <span>${{ number_format($invoiceTotal, 2) }}</span>
                                                            </div>
                                                            <div class="week_details_wrapper_total">
                                                                <label>Total :</label>
                                                                <span>${{ number_format($total, 2) }}</span>
                                                            </div>
                                                            <div class="week_details_wrapper_total">
                                                                <label>Grand Total :</label>
                                                                <span>${{ number_format($completedTotal, 2) }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @forelse ($schedule['routes'] as $route)
                                                        <div class="muller_honda_wrapper muller_honda_wrapper_update">
                                                            <div>
                                                                <h2>{{ ucfirst($route['client_name']) }}
                                                                </h2>
                                                                <div class="muller_honda_details">
                                                                    <div>
                                                                        @php
                                                                            $parts = array_filter([$route['address'] ?? null, $route['city'] ?? null, $route['state'] ?? null, $route['zip_code'] ?? null]);
                                                                        @endphp

                                                                        <span><strong>{{ $parts ? implode(', ', $parts) : 'Not Available' }}</strong></span>
                                                                    </div>
                                                                    <div class="d-flex flex-row">
                                                                        <span><strong>{{ ucfirst($route['payment_type']) }}
                                                                                : </strong></span>
                                                                        <span>${{ number_format($route['invoice_amount'], 2) }}</span>
                                                                    </div>
                                                                    <div class="d-flex flex-row flex-wrap">
                                                                        <label>Scope :</label>
                                                                        @foreach ($route['multiPrice'] ?? [] as $index => $price)
                                                                            <span>{{ $price['name'] ?? '' }}{{ $index < count($route['multiPrice']) - 1 ? ',' : '' }}</span>
                                                                        @endforeach
                                                                    </div>
                                                                    <div class="d-flex flex-row flex-wrap">
                                                                        <span><strong>Note :</strong></span>
                                                                        <span>{{ $route['note'] }}</span>
                                                                    </div>
                                                                    <div class="d-flex flex-row flex-wrap">
                                                                        <label>Best Time To Service :</label>
                                                                        @foreach ($route['client_hours'] ?? [] as $hour)
                                                                            <span>{{ $hour['start_hour'] ?? '' }} -
                                                                                {{ $hour['end_hour'] ?? '' }}</span>
                                                                        @endforeach
                                                                    </div>
                                                                    <div class="d-flex flex-row flex-wrap">
                                                                        <label>Closed :</label>
                                                                        @php
                                                                            $days = collect($route['client_unavailable_days'] ?? [])
                                                                                ->pluck('day')
                                                                                ->map(function ($day) {
                                                                                    return ucfirst(strtolower($day));
                                                                                })
                                                                                ->implode(' , ');
                                                                        @endphp
                                                                        <span>{{ $days ?: '-' }}</span>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            @if ($route['clientSchedule'] == 'completed')
                                                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#completedModal">
                                                                    <div class="completed_wrapper">
                                                                        <i class="fa-solid fa-check"></i>
                                                                        @if (isset($route['payment_type']) && $route['payment_type'] == 'invoice')
                                                                            <a href="{{ url('view_client_invoice/' . $route['client_id']) }}?start_date={{ $route['client_start_week'] }}&end_date={{ $route['client_end_week'] }}">
                                                                                <h5>Complete</h5>
                                                                            </a>
                                                                        @elseif(isset($route['payment_type']) && $route['payment_type'] == 'cash')
                                                                            <a href="{{ url('view_client_cash' . '/' . $route['client_id']) }}?start_date={{ $route['client_start_week'] }}&end_date={{ $route['client_end_week'] }}">
                                                                                <h5>Complete</h5>
                                                                            </a>
                                                                        @endif
                                                                    </div>
                                                                </a>
                                                            @elseif($route['clientSchedule'] == 'pending')
                                                                <div class="mark_as_complete_wrapper">
                                                                    @if ($route['payment_type'] == 'invoice')
                                                                        <a href="{{ url('client_invoice/' . $route['client_id']) }}?start_date={{ $route['client_start_week'] }}&end_date={{ $route['client_end_week'] }}&month={{ urlencode($selectedMonth ?? '') }}">
                                                                            <h5>Report Status</h5>
                                                                        </a>
                                                                    @elseif($route['payment_type'] == 'cash')
                                                                        <a href="{{ url('client_cash' . '/' . $route['client_id']) }}?start_date={{ $route['client_start_week'] }}&end_date={{ $route['client_end_week'] }}&month={{ urlencode($selectedMonth ?? '') }}">
                                                                            <h5>Report Status</h5>
                                                                        </a>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                            <a href="#" data-note="{{ $route['note'] }}" data-schedule-id="{{ $route['schedule_id'] }}" data-client-price-list="{{ is_array($route['client_price_list']) ? json_encode($route['client_price_list']) : json_encode($route['client_price_list']->toArray()) }}" data-bs-toggle="modal" data-bs-target="#editNoteModal">
                                                                <div class="completed_wrapper">
                                                                    <i class="fa-solid fa-edit"></i>
                                                                    <h5>Edit Note</h5>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    @empty
                                                        <div class="text-muted">
                                                            No Data Available
                                                        </div>
                                                    @endforelse

                                                </div>
                                            </div>
                                        @endforeach
                                    </div> --}}
                                    <div class="sectionToPrint staff_routes_week_row">
                                        @foreach ($mergedSchedules as $key => $schedule)
                                            @php
                                                $cashTotal = 0;
                                                $cashPendingTotal = 0;
                                                $cashPaidTotal = 0;
                                                $invoiceTotal = 0;

                                                foreach ($schedule['routes'] as $route) {
                                                    $amount = $route['invoice_amount'] ?? 0;
                                                    if (($route['payment_type'] ?? '') === 'cash') {
                                                        $cashTotal += $amount;
                                                        if (($route['payment_status'] ?? '') === 'paid') {
                                                            $cashPaidTotal += $amount;
                                                        } else {
                                                            $cashPendingTotal += $amount;
                                                        }
                                                    } elseif (($route['payment_type'] ?? '') === 'invoice') {
                                                        $invoiceTotal += $amount;
                                                    }
                                                }
                                                $total = $cashTotal + $invoiceTotal;
                                                $grandTotal = $invoiceTotal + $cashPaidTotal;

                                                $routeId = $staffRoute->id ?? null;
                                            @endphp
                                            <div class="staff_routes_week_wrapper">
                                                @include('staffroutes.partials.log_hours_button', ['schedule' => $schedule, 'staffRoute' => $staffRoute])
                                                <div class="details_routes_wrapper">
                                                    <div class="week_wrapper">
                                                        <div>
                                                            <h4>Week {{ $schedule['week_number'] }}</h4>
                                                            <h4 style="font-size: 12px">
                                                                {{ \Carbon\Carbon::parse($schedule['start_date'])->format('d F') }}
                                                                - {{ \Carbon\Carbon::parse($schedule['end_date'])->format('d F') }}
                                                            </h4>
                                                            <div class="collapse_btn">
                                                                <button type="button" class="btn_global btn_dark_blue"><i class="fa-solid fa-arrow-right"></i>
                                                                    <div class="hoverable_txt"><span>Collapsing week For Full
                                                                            View</span>
                                                                    </div>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div class="week_details_wrapper">
                                                            <div class="week_details_wrapper_total">
                                                                <label>Total Expected Sales :</label>
                                                                <span>${{ number_format($total, 2) }}</span>
                                                            </div>
                                                            <div>
                                                                <label>Expected Cash Received :</label>
                                                                <span>${{ number_format($cashPendingTotal, 2) }}</span>
                                                            </div>
                                                            <div class="week_details_wrapper_total">
                                                                <label>Cash rec. to date :</label>
                                                                <span>${{ number_format($cashPaidTotal, 2) }}</span>
                                                            </div>
                                                            <div>
                                                                <label>Invoice Total :</label>
                                                                <span>${{ number_format($invoiceTotal, 2) }}</span>
                                                            </div>
                                                            <div class="week_details_wrapper_total">
                                                                <label>Grand Total :</label>
                                                                <span>${{ number_format($grandTotal, 2) }}</span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="routes_wrapper" id="week-{{ $schedule['week_number'] }}" data-week="{{ $schedule['week_number'] }}">
                                                        @foreach ($schedule['routes'] as $key => $route)
                                                            <div class="muller_honda_wrapper muller_honda_wrapper_update" data-schedule-id="{{ $route['schedule_id'] }}" data-client-name="{{ $route['client_name'] }}" data-date="{{ $route['created_at'] ?? now() }}">
                                                                <div class="accordion" id="accordion-{{ $schedule['week_number'] }}-{{ $key }}">
                                                                    <div class="accordion-item">
                                                                        <h2 class="accordion-header" style="display: flex; align-items: center; gap: 10px;">
                                                                            {{-- <input type="checkbox" class="schedule-checkbox" data-week="{{ $schedule['week_number'] }}" data-schedule-id="{{ $route['schedule_id'] }}" data-client-name="{{ $route['client_name'] }}" data-status="{{ $route['is_completed'] }}" data-service-frequency="{{ $route['service_frequency'] ?? '' }}"
                                                                        style="width: 18px; height: 18px; cursor: pointer; margin-left: 10px;"> --}}
                                                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne-{{ $schedule['week_number'] }}-{{ $key }}" aria-expanded="false" aria-controls="collapseOne-{{ $schedule['week_number'] }}-{{ $key }}" style="flex: 1;">
                                                                                {{ ucfirst($route['client_name']) }}
                                                                                {{-- @if ($route['service_frequency'] == 'monthly' || $route['service_frequency'] == 'biMonthly')
                                                                                    <i class="fas fa-edit edit-monthly-schedule-btn" data-schedule-id="{{ $route['schedule_id'] }}" data-client-name="{{ $route['client_name'] }}" data-start-date="{{ $route['client_start_week'] }}" data-end-date="{{ $route['client_end_week'] }}" data-service-frequency="{{ $route['service_frequency'] }}"
                                                                                        style="margin-left: 10px; color: #007bff; cursor: pointer; font-size: 14px;" title="Edit Schedule Date"></i>
                                                                                @endif --}}
                                                                            </button>
                                                                            <span class="schedule-error-message" data-schedule-id="{{ $route['schedule_id'] }}" style="color: red; font-weight: bold; font-size: 9px; display: none; margin-left: 10px;">
                                                                            </span>
                                                                        </h2>

                                                                        <div id="collapseOne-{{ $schedule['week_number'] }}-{{ $key }}" class="accordion-collapse collapse" data-bs-parent="#accordion-{{ $schedule['week_number'] }}-{{ $key }}">
                                                                            <div class="accordion-body">
                                                                                <div>
                                                                                    <div class="muller_honda_details">
                                                                                        <div>
                                                                                            @php
                                                                                                $parts = array_filter([$route['house_no'] ?? null, $route['address'] ?? null, $route['city'] ?? null, $route['state'] ?? null, $route['zip_code'] ?? null]);
                                                                                            @endphp

                                                                                            <span><strong>{{ $parts ? implode(' ', $parts) : 'Not Available' }}</strong></span>
                                                                                        </div>
                                                                                        <div class="d-flex flex-row">
                                                                                            <span><strong>{{ ucfirst($route['payment_type']) }}
                                                                                                    : </strong></span>
                                                                                            <span>${{ number_format($route['invoice_amount'], 2) }}</span>
                                                                                        </div>
                                                                                        <div class="d-flex flex-row">
                                                                                            <label>Scope :</label>
                                                                                            <div class="d-flex flex-column">
                                                                                                @foreach ($route['multiPrice'] ?? [] as $index => $price)
                                                                                                    @if (!empty($price['name']))
                                                                                                        <span>{{ $price['name'] }}{{ $index < count($route['multiPrice']) - 1 ? ',' : '' }}</span>
                                                                                                    @endif
                                                                                                @endforeach
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="d-flex flex-row flex-wrap">
                                                                                            <span><strong>Note :</strong></span>
                                                                                            <span>{{ str_ireplace(', null', '', str_ireplace('null', '', $route['note'])) }}</span>
                                                                                        </div>
                                                                                        <div class="d-flex flex-row flex-wrap">
                                                                                            <label>Best Time To Service :</label>
                                                                                            @foreach (collect($route['client_hours'] ?? [])->sortBy('start_hour') as $hour)
                                                                                                <span>{{ $hour['start_hour'] ?? '' }} - {{ $hour['end_hour'] ?? '' }}</span>
                                                                                            @endforeach
                                                                                        </div>
                                                                                        <div class="d-flex flex-row flex-wrap">
                                                                                            <label>Closed :</label>
                                                                                            @php
                                                                                                $days = collect($route['client_unavailable_days'] ?? [])
                                                                                                    ->pluck('day')
                                                                                                    ->map(function ($day) {
                                                                                                        return ucfirst(strtolower($day));
                                                                                                    })
                                                                                                    ->implode(' , ');
                                                                                            @endphp

                                                                                            <span>{{ $days ?: '-' }}</span>
                                                                                        </div>

                                                                                    </div>
                                                                                </div>
                                                                                @if ($route['clientSchedule'] == 'completed')
                                                                                    <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#completedModal">
                                                                                        <div class="completed_wrapper">
                                                                                            <i class="fa-solid fa-check"></i>
                                                                                            @if (isset($route['payment_type']) && $route['payment_type'] == 'invoice')
                                                                                                <a href="{{ url('view_client_invoice/' . $route['client_id']) }}?start_date={{ $route['client_start_week'] }}&end_date={{ $route['client_end_week'] }}">
                                                                                                    <h5>Complete</h5>
                                                                                                </a>
                                                                                            @elseif(isset($route['payment_type']) && $route['payment_type'] == 'cash')
                                                                                                <a href="{{ url('view_client_cash' . '/' . $route['client_id']) }}?start_date={{ $route['client_start_week'] }}&end_date={{ $route['client_end_week'] }}">
                                                                                                    <h5>Complete</h5>
                                                                                                </a>
                                                                                            @endif
                                                                                            {{--                                                                        <h5>Completed</h5> --}}
                                                                                        </div>
                                                                                    </a>
                                                                                    <br>
                                                                                @elseif($route['clientSchedule'] == 'pending')
                                                                                    <div class="mark_as_complete_wrapper">
                                                                                        @if ($route['payment_type'] == 'invoice')
                                                                                            <a href="{{ url('client_invoice/' . $route['client_id']) }}?start_date={{ $route['client_start_week'] }}&end_date={{ $route['client_end_week'] }}&month={{ urlencode($selectedMonth ?? '') }}">
                                                                                                <h5>Report Status</h5>
                                                                                            </a>
                                                                                        @elseif($route['payment_type'] == 'cash')
                                                                                            <a href="{{ url('client_cash' . '/' . $route['client_id']) }}?start_date={{ $route['client_start_week'] }}&end_date={{ $route['client_end_week'] }}&month={{ urlencode($selectedMonth ?? '') }}">
                                                                                                <h5>Report Status</h5>
                                                                                            </a>
                                                                                        @endif
                                                                                    </div>
                                                                                @endif
                                                                                <a href="#" data-note="{{ $route['note'] }}" data-schedule-id="{{ $route['schedule_id'] }}" data-client-price-list="{{ is_array($route['client_price_list']) ? json_encode($route['client_price_list']) : json_encode($route['client_price_list']->toArray()) }}" data-bs-toggle="modal"
                                                                                    data-bs-target="#editNoteModal">
                                                                                    <div class="completed_wrapper">
                                                                                        <i class="fa-solid fa-edit"></i>
                                                                                        <h5>Edit Note</h5>
                                                                                    </div>
                                                                                </a>
                                                                                @php
                                                                                    $weekNumber = (int) preg_replace('/[^0-9]/', '', $route['week'] ?? 'week0');
                                                                                @endphp
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="tab-pane fade @if (request()->query('tab') === 'inprogress_staff') show active @endif" id="inprogress_staff" role="tabpanel" aria-labelledby="inprogress-tab_staff">
                                    <div class="filter_download_dropdown_wrapper filter_download_dropdown_wrapper_all">
                                        <div class="months-pagination">
                                            <a href="?month={{ urlencode($previousMonth) }}&tab=inprogress_staff" type="button" class="pag-btn prevMonthBtn" id="prevMonthBtn_inprogress_staff">
                                                <i class="fas fa-arrow-left"></i>
                                            </a>
                                            <div class="dropdown dropdown_months_wrapper">
                                                <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fa-regular fa-calendar selected_month_ajax" selected-month="{{ $selectedMonth }}" staff-route-id="{{ $staffRoute->id }}" staff-route-name="{{ $staffRoute->name }}"></i>
                                                    {{ $selectedMonth }}
                                                </button>
                                                <ul class="dropdown-menu">
                                                    @foreach ($months as $month)
                                                        <li>
                                                            <a class="dropdown-item" href="?month={{ urlencode($month) }}&tab=inprogress_staff">
                                                                {{ $month }}
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>

                                            <a href="?month={{ urlencode($nextMonth) }}&tab=inprogress_staff" class="pag-btn" type="button" id="nextMonthBtn_inprogress">
                                                <i class="fas fa-arrow-right"></i>
                                            </a>

                                            <a href="?month={{ urlencode($nextYearFirstMonth) }}&tab=inprogress_staff" class="pag-btn btn_global btn_dark_blue" type="button" id="nextYearBtn_inprogress" style="width: fit-content;">
                                                Next Year <i class="fas fa-forward"></i>
                                            </a>
                                        </div>
                                        <div class="searchbar_download_filter_wrapper">
                                            <button type="button" id="exportExcel" class="btn_global btn_dark_blue exportBtn exportExcel">Export Excel
                                                <i class="fa-solid fa-file-excel"></i></button>
                                        </div>
                                    </div>
                                    <div class="staff_routes_week_row inprogressTab">

                                        @foreach ($mergedSchedules as $schedule)
                                            @php
                                                $cashTotal = 0;
                                                $invoiceTotal = 0;

                                                $pendingRoutes = collect($schedule['routes'])->filter(function ($route) {
                                                    return $route['clientSchedule'] === 'pending';
                                                });

                                                foreach ($pendingRoutes as $route) {
                                                    if ($route['payment_type'] === 'cash') {
                                                        $cashTotal += $route['invoice_amount'];
                                                    } elseif ($route['payment_type'] === 'invoice') {
                                                        $invoiceTotal += $route['invoice_amount'];
                                                    }
                                                }

                                                $total = $cashTotal + $invoiceTotal;
                                                $completedTotal = $schedule['routes']->where('is_completed', 'completed')->sum('invoice_amount');

                                            @endphp

                                            <div class="staff_routes_week_wrapper">
                                                @include('staffroutes.partials.log_hours_button', ['schedule' => $schedule, 'staffRoute' => $staffRoute])
                                                <div class="details_routes_wrapper">
                                                    <div class="week_wrapper">
                                                        <div>
                                                            <h4>Week {{ $schedule['week_number'] }}</h4>
                                                            <h4>{{ \Carbon\Carbon::parse($schedule['start_date'])->format('d F') }}
                                                                -
                                                                {{ \Carbon\Carbon::parse($schedule['end_date'])->format('d F') }}
                                                            </h4>
                                                            <div class="collapse_btn">
                                                                <button type="button" class="btn_global btn_dark_blue">
                                                                    <i class="fa-solid fa-arrow-right"></i>
                                                                    <div class="hoverable_txt"><span>Collapsing week For
                                                                            Full View</span>
                                                                    </div>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div class="week_details_wrapper">
                                                            <div>
                                                                <label>Cash Total :</label>
                                                                <span>${{ number_format($cashTotal, 2) }}</span>
                                                            </div>
                                                            <div>
                                                                <label>Invoice Total :</label>
                                                                <span>${{ number_format($invoiceTotal, 2) }}</span>
                                                            </div>
                                                            <div class="week_details_wrapper_total">
                                                                <label>Total :</label>
                                                                <span>${{ number_format($total, 2) }}</span>
                                                            </div>
                                                            <div class="week_details_wrapper_total">
                                                                <label>Grand Total :</label>
                                                                <span>${{ number_format($completedTotal, 2) }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @forelse ($pendingRoutes as $route)
                                                        <div class="muller_honda_wrapper muller_honda_wrapper_update">
                                                            <div>
                                                                <h2>{{ ucfirst($route['client_name']) }}</h2>
                                                                <div class="muller_honda_details">
                                                                    <div>
                                                                        @php
                                                                            $parts = array_filter([$route['address'] ?? null, $route['city'] ?? null, $route['state'] ?? null, $route['zip_code'] ?? null]);
                                                                        @endphp

                                                                        <span><strong>{{ $parts ? implode(', ', $parts) : 'Not Available' }}</strong></span>
                                                                    </div>
                                                                    <div class="d-flex flex-row">
                                                                        <span><strong>{{ ucfirst($route['payment_type']) }}
                                                                                : </strong></span>
                                                                        <span>${{ number_format($route['invoice_amount'], 2) }}</span>
                                                                    </div>
                                                                    <div class="d-flex flex-row flex-wrap">
                                                                        <label>Scope :</label>
                                                                        @foreach ($route['multiPrice'] ?? [] as $index => $price)
                                                                            <span>{{ $price['name'] ?? '' }}{{ $index < count($route['multiPrice']) - 1 ? ',' : '' }}</span>
                                                                        @endforeach
                                                                    </div>
                                                                    <div class="d-flex flex-row flex-wrap">
                                                                        <span><strong>Note :</strong></span>
                                                                        <span>{{ $route['note'] }}</span>
                                                                    </div>
                                                                    <div class="d-flex flex-row flex-wrap">
                                                                        <label>Best Time To Service :</label>
                                                                        @foreach ($route['client_hours'] ?? [] as $hour)
                                                                            <span>{{ $hour['start_hour'] ?? '' }} -
                                                                                {{ $hour['end_hour'] ?? '' }}</span>
                                                                        @endforeach
                                                                    </div>
                                                                    <div class="d-flex flex-row flex-wrap">
                                                                        <label>Closed :</label>
                                                                        @php
                                                                            $days = collect($route['client_unavailable_days'] ?? [])
                                                                                ->pluck('day')
                                                                                ->map(function ($day) {
                                                                                    return ucfirst(strtolower($day));
                                                                                })
                                                                                ->implode(' , ');
                                                                        @endphp

                                                                        <span>{{ $days ?: '-' }}</span>
                                                                    </div>

                                                                </div>

                                                            </div>
                                                            <div class="mark_as_complete_wrapper">
                                                                @if ($route['payment_type'] == 'invoice')
                                                                    <a href="{{ url('client_invoice/' . $route['client_id']) }}?start_date={{ $route['client_start_week'] }}&end_date={{ $route['client_end_week'] }}&month={{ urlencode($selectedMonth ?? '') }}">
                                                                        <h5>Report Status</h5>
                                                                    </a>
                                                                @elseif($route['payment_type'] == 'cash')
                                                                    <a href="{{ url('client_cash' . '/' . $route['client_id']) }}?start_date={{ $route['client_start_week'] }}&end_date={{ $route['client_end_week'] }}&month={{ urlencode($selectedMonth ?? '') }}">
                                                                        <h5>Report Status</h5>
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <div class="text-muted">
                                                            No Data Available
                                                        </div>
                                                    @endforelse
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="tab-pane fade @if (request()->query('tab') === 'completed_staff') show active @endif" id="completed_staff" role="tabpanel" aria-labelledby="completed-tab_staff">
                                    <div class="filter_download_dropdown_wrapper filter_download_dropdown_wrapper_all">
                                        <div class="months-pagination">
                                            <a href="?month={{ urlencode($previousMonth) }}&tab=completed_staff" type="button" class="pag-btn prevMonthBtn" id="prevMonthBtn_completed_staff">
                                                <i class="fas fa-arrow-left"></i>
                                            </a>
                                            <div class="dropdown dropdown_months_wrapper">
                                                <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fa-regular fa-calendar selected_month_ajax" selected-month="{{ $selectedMonth }}" staff-route-id="{{ $staffRoute->id }}" staff-route-name="{{ $staffRoute->name }}">
                                                    </i>
                                                    {{ $selectedMonth }}
                                                </button>
                                                <ul class="dropdown-menu">
                                                    @foreach ($months as $month)
                                                        <li>
                                                            <a class="dropdown-item" href="?month={{ urlencode($month) }}&tab=completed_staff">
                                                                {{ $month }}
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>

                                            <a href="?month={{ urlencode($nextMonth) }}&tab=completed_staff" class="pag-btn" type="button" id="nextMonthBtn_completed">
                                                <i class="fas fa-arrow-right"></i>
                                            </a>

                                            <a href="?month={{ urlencode($nextYearFirstMonth) }}&tab=completed_staff" class="pag-btn btn_global btn_dark_blue" type="button" id="nextYearBtn_completed" style="width: fit-content;">
                                                Next Year <i class="fas fa-forward"></i>
                                            </a>
                                        </div>
                                        <div class="searchbar_download_filter_wrapper">
                                            <button type="button" id="exportExcel" class="btn_global btn_dark_blue exportBtn exportExcel">
                                                Export Excel <i class="fa-solid fa-file-excel"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="staff_routes_week_row">
                                        @foreach ($mergedSchedules as $schedule)
                                            @php
                                                $cashTotal = 0;
                                                $invoiceTotal = 0;

                                                $pendingRoutes = collect($schedule['routes'])->filter(function ($route) {
                                                    return $route['clientSchedule'] === 'completed';
                                                });

                                                foreach ($pendingRoutes as $route) {
                                                    if ($route['payment_type'] === 'cash') {
                                                        $cashTotal += $route['invoice_amount'];
                                                    } elseif ($route['payment_type'] === 'invoice') {
                                                        $invoiceTotal += $route['invoice_amount'];
                                                    }
                                                }

                                                $total = $cashTotal + $invoiceTotal;
                                                $completedTotal = $schedule['routes']->where('is_completed', 'completed')->sum('invoice_amount');
                                            @endphp

                                            <div class="staff_routes_week_wrapper completedTab">
                                                @include('staffroutes.partials.log_hours_button', ['schedule' => $schedule, 'staffRoute' => $staffRoute])
                                                <div class="details_routes_wrapper">
                                                    <div class="week_wrapper">
                                                        <div>
                                                            <h4>Week {{ $schedule['week_number'] }}</h4>
                                                            <h4>{{ \Carbon\Carbon::parse($schedule['start_date'])->format('d F') }}
                                                                -
                                                                {{ \Carbon\Carbon::parse($schedule['end_date'])->format('d F') }}
                                                            </h4>
                                                            <div class="collapse_btn">
                                                                <button type="button" class="btn_global btn_dark_blue">
                                                                    <i class="fa-solid fa-arrow-right"></i>
                                                                    <div class="hoverable_txt"><span>Collapsing week For
                                                                            Full View</span>
                                                                    </div>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div class="week_details_wrapper">
                                                            <div>
                                                                <label>Cash Total :</label>
                                                                <span>${{ number_format($cashTotal, 2) }}</span>
                                                            </div>
                                                            <div>
                                                                <label>Invoice Total :</label>
                                                                <span>${{ number_format($invoiceTotal, 2) }}</span>
                                                            </div>
                                                            <div class="week_details_wrapper_total">
                                                                <label>Total :</label>
                                                                <span>${{ number_format($total, 2) }}</span>
                                                            </div>
                                                            <div class="week_details_wrapper_total">
                                                                <label>Grand Total :</label>
                                                                <span>${{ number_format($completedTotal, 2) }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @forelse ($pendingRoutes as $route)
                                                        <div class="muller_honda_wrapper muller_honda_wrapper_update">
                                                            <div>
                                                                <h2>{{ ucfirst($route['client_name']) }}
                                                                    <!-- Timer Icon -->
                                                                    {{-- <i class="fas fa-play-circle timer-icon timer-start-icon"
                                                                        data-schedule-id="{{ $route['schedule_id'] }}"
                                                                        data-route-id="{{ $staffRoute->id }}"
                                                                        data-timelog-id=""
                                                                        style="margin-left: 10px; color: #28a745; cursor: pointer; font-size: 20px;"
                                                                        title="Start Timer"></i>
                                                                    <i class="fas fa-stop-circle timer-icon timer-stop-icon"
                                                                        data-schedule-id="{{ $route['schedule_id'] }}"
                                                                        data-route-id="{{ $staffRoute->id }}"
                                                                        data-timelog-id=""
                                                                        style="margin-left: 10px; color: #dc3545; cursor: pointer; font-size: 20px; display: none;"
                                                                        title="Stop Timer"></i>
                                                                    <i class="fas fa-spinner fa-spin timer-icon timer-loading-icon"
                                                                        style="margin-left: 10px; color: #6c757d; font-size: 20px; display: none;"></i> --}}
                                                                </h2>
                                                                <div class="muller_honda_details">
                                                                    <div>
                                                                        @php
                                                                            $parts = array_filter([$route['address'] ?? null, $route['city'] ?? null, $route['state'] ?? null, $route['zip_code'] ?? null]);
                                                                        @endphp

                                                                        <span><strong>{{ $parts ? implode(', ', $parts) : 'Not Available' }}</strong></span>
                                                                    </div>
                                                                    <div class="d-flex flex-row">
                                                                        <span><strong>{{ ucfirst($route['payment_type']) }}
                                                                                : </strong></span>
                                                                        <span>${{ number_format($route['invoice_amount'], 2) }}</span>
                                                                    </div>
                                                                    <div class="d-flex flex-row flex-wrap">
                                                                        <label>Scope :</label>
                                                                        @foreach ($route['multiPrice'] ?? [] as $index => $price)
                                                                            <span>{{ $price['name'] ?? '' }}{{ $index < count($route['multiPrice']) - 1 ? ',' : '' }}</span>
                                                                        @endforeach
                                                                    </div>
                                                                    <div class="d-flex flex-row flex-wrap">
                                                                        <span><strong>Note :</strong></span>
                                                                        <span>{{ $route['note'] }}</span>
                                                                    </div>
                                                                    <div class="d-flex flex-row flex-wrap">
                                                                        <label>Best Time To Service :</label>
                                                                        @foreach ($route['client_hours'] ?? [] as $hour)
                                                                            <span>{{ $hour['start_hour'] ?? '' }} -
                                                                                {{ $hour['end_hour'] ?? '' }}</span>
                                                                        @endforeach
                                                                    </div>
                                                                    <div class="d-flex flex-row flex-wrap">
                                                                        <label>Closed :</label>
                                                                        @php
                                                                            $days = collect($route['client_unavailable_days'] ?? [])
                                                                                ->pluck('day')
                                                                                ->map(function ($day) {
                                                                                    return ucfirst(strtolower($day));
                                                                                })
                                                                                ->implode(' , ');
                                                                        @endphp

                                                                        <span>{{ $days ?: '-' }}</span>
                                                                    </div>

                                                                </div>
                                                            </div>
                                                            <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#completedModal">
                                                                <div class="completed_wrapper">
                                                                    <i class="fa-solid fa-check"></i>
                                                                    <h5>Completed</h5>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    @empty
                                                        <div class="text-muted">
                                                            No Data Available
                                                        </div>
                                                    @endforelse
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>
        <div class="modal fade" id="completedModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog  modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title " id="exampleModalLabel">Detail</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Completed
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn_global btn_grey" data-bs-dismiss="modal">Close <i class="fa-solid fa-x"></i></button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="modal fade" id="editNoteModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="exampleModalLabel">EDIT NOTE</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{ route('clientschedule.note.update') }}" class="form-horizontal" enctype="multipart/form-data">
                    <div class="modal-body">
                        @csrf
                        {{-- <input type="hidden" name="schedule_id" id="schedule_id">
                        <h3>Select additional price :</h3>
                        <div id="client_price_list"></div> --}}

                        <div class="txt_field mt-4">
                            <input type="text" class="form-control" name="note" id="note" placeholder="Schedule Note" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn_global btn_grey" data-bs-dismiss="modal">Close <i class="fa-solid fa-x"></i></button>
                        <button type="submit" class="btn_global btn_blue">Save <i class="fa-solid fa-check"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Temporary Move   Modal (Admin Only) --}}
    <div class="modal fade" id="moveToNextWeekModal" tabindex="-1" aria-labelledby="moveToNextWeekModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="moveToNextWeekModalLabel">Temporary Move </h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="move_schedule_id">
                    <p>Are you sure you want to move <strong id="move_client_name"></strong>'s schedule to the next week?
                    </p>
                    <p class="text-muted">This will shift the schedule dates by 7 days.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn_global btn_grey" data-bs-dismiss="modal">Cancel <i class="fa-solid fa-x"></i></button>
                    <button type="button" class="btn_global btn_blue" id="confirmMoveToNextWeek">Confirm <i class="fa-solid fa-check"></i></button>
                </div>
            </div>
        </div>
    </div>

    {{-- Move to Previous Week Modal (Admin Only) --}}
    <div class="modal fade" id="moveToPreviousWeekModal" tabindex="-1" aria-labelledby="moveToPreviousWeekModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="moveToPreviousWeekModalLabel">MOVE TO PREVIOUS WEEK</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="move_previous_schedule_id">
                    <p>Are you sure you want to move <strong id="move_previous_client_name"></strong>'s schedule to the
                        previous week?
                    </p>
                    <p class="text-muted">This will shift the schedule dates back by 7 days.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn_global btn_grey" data-bs-dismiss="modal">Cancel <i class="fa-solid fa-x"></i></button>
                    <button type="button" class="btn_global btn_blue" id="confirmMoveToPreviousWeek">Confirm <i class="fa-solid fa-check"></i></button>
                </div>
            </div>
        </div>
    </div>


    <!-- Edit Monthly/BiMonthly Schedule Modal -->
    <div class="modal fade" id="editMonthlyScheduleModal" tabindex="-1" aria-labelledby="editMonthlyScheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editMonthlyScheduleModalLabel">Edit Schedule Date</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editMonthlyScheduleId">

                    <div class="mb-3">
                        <label class="form-label"><strong>Client:</strong></label>
                        <p id="editMonthlyClientName" class="mb-0"></p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Service Frequency:</strong></label>
                        <p id="editMonthlyServiceFrequency" class="mb-0"></p>
                    </div>

                    <div class="mb-3">
                        <label for="editMonthlyStartDate" class="form-label"><strong>New Start Date:</strong></label>
                        <input type="date" class="form-control" id="editMonthlyStartDate" required>
                        <small class="form-text text-muted">End date will be automatically set to 7 days after start
                            date</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveMonthlyScheduleBtn">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    @if (auth()->user()->hasRole('staff'))
        <div class="modal fade" id="logHoursModal" tabindex="-1" aria-labelledby="logHoursModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h2 class="modal-title" id="logHoursModalLabel">Log Hours</h2>
                            <p class="mb-0 text-muted" id="logHoursWeekLabel"></p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="logHoursForm">
                            @csrf
                            <input type="hidden" id="log_hours_route_id" name="route_id">
                            <input type="hidden" id="log_hours_week_number" name="week_number">
                            <input type="hidden" id="log_hours_week_start" name="week_start_date">
                            <input type="hidden" id="log_hours_week_end" name="week_end_date">

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="txt_field form-floating">
                                        <select class="form-control" id="log_hours_day" name="service_date" required>
                                            <option value="">Select Day</option>
                                        </select>
                                        <label for="log_hours_day">Day *</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="txt_field form-floating">
                                        <input type="time" class="form-control" id="log_hours_start_time" name="start_time" required>
                                        <label for="log_hours_start_time">Start Time *</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="txt_field form-floating">
                                        <input type="time" class="form-control" id="log_hours_end_time" name="end_time" required>
                                        <label for="log_hours_end_time">End Time *</label>
                                    </div>
                                </div>
                            </div>
                            <p id="logHoursFormError" class="text-danger mt-2 mb-0" style="display: none;"></p>
                            <div class="mt-3">
                                <button type="submit" class="btn_global btn_blue" id="logHoursSubmitBtn">
                                    Add Entry <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                        </form>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Time Journal</h5>
                            <div>
                                <strong>Total Hours: </strong>
                                <span id="logHoursTotal">0.00</span> hrs
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Day</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Duration</th>
                                    </tr>
                                </thead>
                                <tbody id="logHoursTableBody">
                                    <tr id="logHoursEmptyRow">
                                        <td colspan="4" class="text-center text-muted">No log hours added yet.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn_global btn_grey" data-bs-dismiss="modal">Close <i class="fa-solid fa-x"></i></button>
                    </div>
                </div>
            </div>
        </div>
    @endif

@endsection
@push('js')
    {{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.bundle.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>

    <script>
        // Define route variables for use in JavaScript
        // Get route ID from model (properly escaped for UUID or integer)
        const staffRouteId = @json($staffRoute->id ?? null);
        const selectedMonth = @json($selectedMonth);

        console.log('Route ID from model:', staffRouteId, 'Type:', typeof staffRouteId);

        $('#editNoteModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var scheduleId = button.data('schedule-id');
            var note = button.data('note');
            var clientPriceList = button.data('client-price-list');

            var modal = $(this);
            modal.find('#schedule_id').val(scheduleId);
            modal.find('#note').val(note);

            var priceHtml = '';
            clientPriceList.forEach(function(price, index) {
                priceHtml += `
                <div class="form-check mt-3">
                    <input class="form-check-input" type="radio" name="additional_price" id="price_${index}" value="${price.value ?? '0.00'}">
                    <label class="form-check-label" for="price_${index}">
                        ${price.name ?? 'No Name'}: $${price.value ?? '0.00'}
                    </label>
                </div>
            `;
            });

            modal.find('#client_price_list').html(priceHtml);
        });

        // Move Week Button Click Handler - Show direction choice
        $(document).on('click', '.move-week-btn', function(e) {
            e.preventDefault();
            var button = $(this);
            var scheduleId = button.data('schedule-id');
            var clientName = button.data('client-name');
            var status = button.data('status');

            // Check if status is completed (1 or 'completed' or true)
            var completedWarning = '';
            if (status == 1 || status == 'completed' || status === true) {
                completedWarning = '<strong>' + clientName +
                    '</strong>\'s schedule is already <span style="color: green; font-weight: bold;">COMPLETED</span>.<br><br>';
            }

            // Show SweetAlert with 2 direction options
            Swal.fire({
                title: 'Choose Direction',
                html: completedWarning + 'Where do you want to move <strong>' + clientName +
                    '</strong>\'s schedule?',
                icon: 'question',
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonText: '<i class="fa-solid fa-arrow-right"></i> Next Week',
                denyButtonText: '<i class="fa-solid fa-arrow-left"></i> Previous Week',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#3085d6',
                denyButtonColor: '#6c757d',
                cancelButtonColor: '#d33'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Next Week selected
                    $('#move_schedule_id').val(scheduleId);
                    $('#move_client_name').text(clientName);
                    $('#moveToNextWeekModal').modal('show');
                } else if (result.isDenied) {
                    // Previous Week selected
                    $('#move_previous_schedule_id').val(scheduleId);
                    $('#move_previous_client_name').text(clientName);
                    $('#moveToPreviousWeekModal').modal('show');
                }
            });
        });


        // $(document).ready(function() {
        //     $(".exportExcel").click(function() {
        //         let exportData = [];
        //         let selectedMonth = $(".selected_month_ajax").attr("selected-month");
        //         let staffRouteId = $(".selected_month_ajax").attr("staff-route-id");

        //         Swal.fire({
        //             title: 'Loading...',
        //             html: 'Fetching data for export',
        //             didOpen: () => {
        //                 Swal.showLoading();
        //             }
        //         });

        //         $.ajax({
        //             url: "{{ route('staffroute.export_schedule', ':id') }}".replace(':id',
        //                 staffRouteId),
        //             method: 'GET',
        //             data: {
        //                 selectedMonth: selectedMonth,
        //                 staffRouteId: staffRouteId
        //             },
        //             success: function(response) {
        //                 exportData = response;

        //                 if (exportData.length === 0) {
        //                     Swal.fire({
        //                         icon: "warning",
        //                         title: "No Data Available!",
        //                         text: "There is no data to export. Please check again.",
        //                         confirmButtonColor: "#3085d6",
        //                         confirmButtonText: "OK"
        //                     });
        //                     return;
        //                 }

        //                 let formattedData = [];
        //                 let weekHeaderRows = [];
        //                 let columnHeaderRows = [];
        //                 let dataRowsRanges = [];

        //                 exportData.data.forEach(monthData => {
        //                     monthData.forEach(weekData => {
        //                         let weeklyCashTotal = 0;
        //                         let weeklyInvoiceTotal = 0;

        //                         weekData.routes.forEach(route => {
        //                             if (route.Pay.toLowerCase() ===
        //                                 'cash') {
        //                                 weeklyCashTotal += parseFloat(
        //                                     route.Amount);
        //                             } else if (route.Pay
        //                                 .toLowerCase() === 'invoice') {
        //                                 weeklyInvoiceTotal +=
        //                                     parseFloat(route.Amount);
        //                             }
        //                         });

        //                         let weeklyTotal = weeklyCashTotal +
        //                             weeklyInvoiceTotal;

        //                         let headerRowIndex = formattedData.length;
        //                         weekHeaderRows.push(headerRowIndex);

        //                         formattedData.push([
        //                             `{{ $staffRoute->name ?? '' }}`,
        //                             `Week ${weekData.week_number}`,
        //                             `${weekData.start_date} to ${weekData.end_date}`,
        //                             "",
        //                             "",
        //                             "",
        //                             ""
        //                         ]);

        //                         let columnHeaderIndex = formattedData.length;
        //                         columnHeaderRows.push(
        //                             columnHeaderIndex
        //                         ); // Track column header row

        //                         formattedData.push([
        //                             "Name",
        //                             "Address",
        //                             "City",
        //                             "C",
        //                             "I",
        //                             "Scope",
        //                             "Note / Time",
        //                         ]);

        //                         let dataRowStart = formattedData.length;

        //                         weekData.routes.forEach(route => {
        //                             formattedData.push([
        //                                 route["Client Name"],
        //                                 route.Address,
        //                                 route.City,
        //                                 route.Pay === 'Cash' ?
        //                                 `$${parseFloat(route.Amount).toFixed(2)}` :
        //                                 "$0.00",
        //                                 route.Pay ===
        //                                 'Invoice' ?
        //                                 `$${parseFloat(route.Amount).toFixed(2)}` :
        //                                 "$0.00",
        //                                 route.Service,
        //                                 route.Note + (route
        //                                     .Time ?
        //                                     `${route.Time}` : ""
        //                                 ),
        //                             ]);
        //                         });

        //                         let dataRowEnd = formattedData.length - 1;
        //                         dataRowsRanges.push({
        //                             start: dataRowStart,
        //                             end: dataRowEnd
        //                         });

        //                         formattedData.push(["", "", "", "", "", "",
        //                             ""
        //                         ]);
        //                         formattedData.push(["", "", "", "", "", "",
        //                             ""
        //                         ]);

        //                         formattedData.push([
        //                             "Totals:",
        //                             `Cash: $${weeklyCashTotal.toFixed(2)}`,
        //                             `Billed: $${weeklyInvoiceTotal.toFixed(2)}`,
        //                             `Sales: `,
        //                             `$${weeklyTotal.toFixed(2)}`,
        //                             "",
        //                             ""
        //                         ]);

        //                         formattedData.push(["", "", "", "", "", "",
        //                             ""
        //                         ]);
        //                     });
        //                 });

        //                 let fileName = `{{ $staffRoute->name ?? '' }}_Client_Schedules.xlsx`;

        //                 let ws = XLSX.utils.aoa_to_sheet(formattedData);

        //                 const colWidths = [20, 20, 16, 7, 7, 25, 25];
        //                 ws['!cols'] = colWidths.map(width => ({
        //                     wch: width
        //                 }));

        //                 const wrapStyle = {
        //                     alignment: {
        //                         wrapText: true,
        //                         vertical: "top",
        //                         horizontal: "left"
        //                     }
        //                 };

        //                 const range = XLSX.utils.decode_range(ws['!ref']);
        //                 for (let row = range.s.r; row <= range.e.r; row++) {
        //                     for (let col = range.s.c; col <= range.e.c; col++) {
        //                         const cellRef = XLSX.utils.encode_cell({
        //                             r: row,
        //                             c: col
        //                         });
        //                         if (ws[cellRef]) {
        //                             if (ws[cellRef].s) {
        //                                 ws[cellRef].s.alignment = {
        //                                     ...ws[cellRef].s.alignment,
        //                                     wrapText: true,
        //                                     vertical: "top"
        //                                 };
        //                             } else {
        //                                 ws[cellRef].s = wrapStyle;
        //                             }
        //                         }
        //                     }
        //                 }

        //                 // Week header style (Green header with staff name and week)
        //                 const headerStyle = {
        //                     font: {
        //                         bold: true,
        //                         sz: 10,
        //                         color: {
        //                             rgb: "FFFFFF"
        //                         }
        //                     },
        //                     fill: {
        //                         fgColor: {
        //                             rgb: "4CAF50"
        //                         }
        //                     },
        //                     alignment: {
        //                         vertical: "center",
        //                         horizontal: "left"
        //                     }
        //                 };

        //                 // Column header style (Name, Address, Note/Time, etc.)
        //                 const columnHeaderStyle = {
        //                     font: {
        //                         bold: true,
        //                         sz: 9
        //                     },
        //                     fill: {
        //                         fgColor: {
        //                             rgb: "E8E8E8"
        //                         } // Light grey for column headers
        //                     },
        //                     alignment: {
        //                         vertical: "center",
        //                         horizontal: "left",
        //                         wrapText: true
        //                     },
        //                     border: {
        //                         bottom: {
        //                             style: "thin",
        //                             color: {
        //                                 rgb: "000000"
        //                             }
        //                         }
        //                     }
        //                 };

        //                 // Alternating row colors (light grey) - ONLY for client data rows
        //                 const lightGreyStyle = {
        //                     fill: {
        //                         fgColor: {
        //                             rgb: "F5F5F5"
        //                         }
        //                     },
        //                     alignment: {
        //                         wrapText: true,
        //                         vertical: "top",
        //                         horizontal: "left"
        //                     }
        //                 };

        //                 const whiteStyle = {
        //                     alignment: {
        //                         wrapText: true,
        //                         vertical: "top",
        //                         horizontal: "left"
        //                     }
        //                 };

        //                 // Apply alternating colors ONLY to client data rows
        //                 dataRowsRanges.forEach(range => {
        //                     for (let row = range.start; row <= range.end; row++) {
        //                         const isEvenRow = (row - range.start) % 2 === 0;
        //                         const style = isEvenRow ? whiteStyle : lightGreyStyle;

        //                         ['A', 'B', 'C', 'D', 'E', 'F', 'G'].forEach(col => {
        //                             const cellRef = col + (row + 1);
        //                             if (ws[cellRef]) {
        //                                 ws[cellRef].s = style;
        //                             }
        //                         });
        //                     }
        //                 });

        //                 // Apply column header styles (Name, Address, Note/Time, C, I, Scope, City)
        //                 columnHeaderRows.forEach(rowIndex => {
        //                     const excelRow = rowIndex + 1;
        //                     ['A', 'B', 'C', 'D', 'E', 'F', 'G'].forEach(col => {
        //                         const cellRef = col + excelRow;
        //                         if (ws[cellRef]) {
        //                             ws[cellRef].s = columnHeaderStyle;
        //                         }
        //                     });
        //                 });

        //                 // Apply week header styles (Staff name, Week number, dates)
        //                 weekHeaderRows.forEach(rowIndex => {
        //                     const excelRow = rowIndex + 1;
        //                     ['A', 'B', 'C', 'D', 'E', 'F', 'G'].forEach(col => {
        //                         const cellRef = col + excelRow;
        //                         if (ws[cellRef]) {
        //                             ws[cellRef].s = headerStyle;
        //                         }
        //                     });
        //                 });

        //                 if (!ws['!rows']) ws['!rows'] = [];
        //                 weekHeaderRows.forEach(rowIndex => {
        //                     ws['!rows'][rowIndex] = {
        //                         hpt: 25
        //                     };
        //                 });

        //                 let wb = XLSX.utils.book_new();
        //                 XLSX.utils.book_append_sheet(wb, ws, 'Staff Route Data');

        //                 XLSX.writeFile(wb, fileName);

        //                 Swal.fire({
        //                     icon: "success",
        //                     title: "Export Successful!",
        //                     text: "Your Excel file has been downloaded.",
        //                     confirmButtonColor: "#28a745",
        //                     confirmButtonText: "OK"
        //                 });
        //             },
        //             error: function(error) {
        //                 Swal.fire({
        //                     icon: "error",
        //                     title: "Error!",
        //                     text: "An error occurred while fetching the data. Please try again.",
        //                     confirmButtonColor: "#d33",
        //                     confirmButtonText: "OK"
        //                 });
        //             }
        //         });
        //     });

        //     $(window).on("load", function() {
        //         $(".app-sidebar-toggle").click();
        //     });

        //     $(".collapse_btn .btn_global").click(function() {
        //         const $currentWrapper = $(this).closest(".staff_routes_week_wrapper");

        //         if (!$currentWrapper.hasClass("expanded")) {
        //             $(".staff_routes_week_wrapper").not($currentWrapper).hide();
        //             $currentWrapper.css("width", "100%").addClass("expanded");
        //             $currentWrapper.find('.collapse_btn .hoverable_txt span').text(
        //                 "UnCollapse For All Week View");
        //         } else {
        //             $(".staff_routes_week_wrapper").show().css("width", "25%").removeClass("expanded");
        //             $currentWrapper.find('.collapse_btn .hoverable_txt span').text(
        //                 "Collapsing Week For Full View");
        //         }
        //     });

        // });

        $(document).ready(function() {
            $(".exportExcel").click(function() {
                let exportData = [];
                let selectedMonth = $(".selected_month_ajax").attr("selected-month");
                let staffRouteId = $(".selected_month_ajax").attr("staff-route-id");

                Swal.fire({
                    title: 'Loading...',
                    html: 'Fetching data for export',
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: "{{ route('staffroute.export_schedule', ':id') }}".replace(':id', staffRouteId),
                    method: 'GET',
                    data: {
                        selectedMonth: selectedMonth,
                        staffRouteId: staffRouteId
                    },
                    success: function(response) {
                        exportData = response;
                        console.log("exportData" , exportData);
                        if (exportData.length === 0) {
                            Swal.fire({
                                icon: "warning",
                                title: "No Data Available!",
                                text: "There is no data to export. Please check again.",
                                confirmButtonColor: "#3085d6",
                                confirmButtonText: "OK"
                            });
                            return;
                        }

                        let formattedData = [];
                        let weekHeaderRows = [];
                        let columnHeaderRows = [];
                        let dataRowsRanges = [];
                        let totalRows = [];
                        let merges = []; // ✅ Merge track karo

                        exportData.data.forEach(monthData => {
                            monthData.forEach(weekData => {
                                if (!weekData.routes || weekData.routes.length === 0) {
                                    return;
                                }

                                let weeklyCashTotal = 0;
                                let weeklyInvoiceTotal = 0;
                                weekData.routes.forEach(route => {

                                    const payType = (route.Pay || route.Cash || '').toString().toLowerCase();

                                    if (payType === 'cash') {
                                        weeklyCashTotal += parseFloat(route.Amount || 0);
                                    } else if (payType === 'invoice') {
                                        weeklyInvoiceTotal += parseFloat(route.Amount || 0);
                                    }
                                });

                                let weeklyTotal = weeklyCashTotal + weeklyInvoiceTotal;

                                // ── Week Header Row (Merged & Centered) ──────
                                let headerRowIndex = formattedData.length;
                                weekHeaderRows.push(headerRowIndex);

                                // ✅ Sab ek cell mein — baaki empty
                                formattedData.push([
                                    `{{ $staffRoute->name ?? '' }}  |  Week ${weekData.week_number}  |  ${weekData.start_date} to ${weekData.end_date}`,
                                    "", "", "", "", "", ""
                                ]);

                                // ✅ A to G merge karo
                                merges.push({
                                    s: {
                                        r: headerRowIndex,
                                        c: 0
                                    },
                                    e: {
                                        r: headerRowIndex,
                                        c: 6
                                    }
                                });

                                // ── Column Header Row ────────────────────────
                                let columnHeaderIndex = formattedData.length;
                                columnHeaderRows.push(columnHeaderIndex);

                                formattedData.push([
                                    "Name",
                                    "Address",
                                    "City",
                                    "Cash",
                                    "Billed",
                                    "Scope",
                                    "Note / Time",
                                ]);

                                // ── Data Rows ────────────────────────────────
                                let dataRowStart = formattedData.length;

                                weekData.routes.forEach(route => {
                                    const payType = (route.Pay || route.Cash || '').toString().toLowerCase();

                                    formattedData.push([
                                        route["Client Name"],
                                        route.Address,
                                        route.City,
                                        payType === 'cash' ? `$${parseFloat(route.Amount).toFixed(2)}` : "$0.00",
                                        payType === 'invoice' ? `$${parseFloat(route.Amount).toFixed(2)}` : "$0.00",
                                        route.Service,
                                        [
                                            route.Note && `Note: ${route.Note}`,
                                            route.Time && `Best Time: ${route.Time}`,
                                            route["Close Days"] && `Close Days: ${route["Close Days"]}`,
                                        ]
                                            .filter(Boolean)
                                            .join(" / ")
                                    ]);
                                });

                                let dataRowEnd = formattedData.length - 1;
                                dataRowsRanges.push({
                                    start: dataRowStart,
                                    end: dataRowEnd
                                });

                                // ── Empty Row ────────────────────────────────
                                formattedData.push(["", "", "", "", "", "", ""]);

                                // ── Total Row ────────────────────────────────
                                let totalRowIndex = formattedData.length;
                                totalRows.push(totalRowIndex);

                                formattedData.push([
                                    "",
                                    "",
                                    "",
                                    `$${weeklyCashTotal.toFixed(2)}`, // C column
                                    `$${weeklyInvoiceTotal.toFixed(2)}`, // I column
                                    `Total Expected Sales:`, // Scope column
                                    `$${weeklyTotal.toFixed(2)}`, // Note/Time column
                                ]);

                                // ── Empty Row After Total ────────────────────
                                formattedData.push(["", "", "", "", "", "", ""]);
                            });
                        });

                        // ── Sheet Banao ──────────────────────────────────────
                        let fileName = `{{ $staffRoute->name ?? '' }}_Client_Schedules.xlsx`;
                        let ws = XLSX.utils.aoa_to_sheet(formattedData);

                        // ── Column Widths ────────────────────────────────────
                        // const colWidths = [20, 20, 16, 7, 7, 35, 35];
                        const colWidths = [
                            15,
                            14,
                            10,
                            6,
                            6,
                            15,
                            45
                        ];
                        ws['!cols'] = colWidths.map(width => ({
                            wch: width
                        }));

                        // ✅ Merges apply karo
                        ws['!merges'] = merges;

                        // ── Styles ───────────────────────────────────────────

                        const wrapStyle = {
                            alignment: {
                                wrapText: true,
                                vertical: "top",
                                horizontal: "left"
                            }
                        };

                        // ✅ Week header — green + center + merged
                        const headerStyle = {
                            font: {
                                bold: true,
                                sz: 9,
                                color: {
                                    rgb: "FFFFFF"
                                }
                            },
                            fill: {
                                fgColor: {
                                    rgb: "4CAF50"
                                }
                            },
                            alignment: {
                                vertical: "center",
                                horizontal: "center", // ✅ Center
                                wrapText: false
                            }
                        };

                        // Column header — light grey
                        const columnHeaderStyle = {
                            font: {
                                bold: true,
                                sz: 9
                            },
                            fill: {
                                fgColor: {
                                    rgb: "E8E8E8"
                                }
                            },
                            alignment: {
                                vertical: "center",
                                horizontal: "left",
                                wrapText: true
                            },
                            border: {
                                bottom: {
                                    style: "thin",
                                    color: {
                                        rgb: "000000"
                                    }
                                }
                            }
                        };

                        // Alternating — light grey
                        const lightGreyStyle = {
                            font: {
                                sz: 9
                            },
                            fill: {
                                fgColor: {
                                    rgb: "F5F5F5"
                                }
                            },
                            alignment: {
                                wrapText: true,
                                vertical: "top",
                                horizontal: "left"
                            }
                        };

                        // Alternating — white
                        const whiteStyle = {
                            font: {
                                sz: 9
                            },
                            alignment: {
                                wrapText: true,
                                vertical: "top",
                                horizontal: "left"
                            }
                        };

                        // ✅ Total row — light green + bold
                        const totalRowStyle = {
                            font: {
                                bold: true,
                                sz: 9
                            },
                            fill: {
                                fgColor: {
                                    rgb: "E8F5E9"
                                }
                            },
                            alignment: {
                                wrapText: true,
                                vertical: "top",
                                horizontal: "left"
                            },
                            border: {
                                top: {
                                    style: "thin",
                                    color: {
                                        rgb: "4CAF50"
                                    }
                                }
                            }
                        };

                        // ── Wrap apply karo sab cells pe ────────────────────
                        const fullRange = XLSX.utils.decode_range(ws['!ref']);
                        for (let row = fullRange.s.r; row <= fullRange.e.r; row++) {
                            for (let col = fullRange.s.c; col <= fullRange.e.c; col++) {
                                const cellRef = XLSX.utils.encode_cell({
                                    r: row,
                                    c: col
                                });
                                if (ws[cellRef]) {
                                    ws[cellRef].s = ws[cellRef].s ? {
                                            ...ws[cellRef].s,
                                            alignment: {
                                                ...ws[cellRef].s.alignment,
                                                wrapText: true,
                                                vertical: "top"
                                            }
                                        } :
                                        wrapStyle;
                                }
                            }
                        }

                        // ── Alternating colors — data rows ───────────────────
                        dataRowsRanges.forEach(range => {
                            for (let row = range.start; row <= range.end; row++) {
                                const isEvenRow = (row - range.start) % 2 === 0;
                                const style = isEvenRow ? whiteStyle : lightGreyStyle;
                                ['A', 'B', 'C', 'D', 'E', 'F', 'G'].forEach(col => {
                                    const cellRef = col + (row + 1);
                                    if (ws[cellRef]) ws[cellRef].s = style;
                                });
                            }
                        });

                        // ── Column header styles ─────────────────────────────
                        columnHeaderRows.forEach(rowIndex => {
                            const excelRow = rowIndex + 1;
                            ['A', 'B', 'C', 'D', 'E', 'F', 'G'].forEach(col => {
                                const cellRef = col + excelRow;
                                if (ws[cellRef]) ws[cellRef].s = columnHeaderStyle;
                            });
                        });

                        // ── Week header styles ───────────────────────────────
                        weekHeaderRows.forEach(rowIndex => {
                            const excelRow = rowIndex + 1;
                            ['A', 'B', 'C', 'D', 'E', 'F', 'G'].forEach(col => {
                                const cellRef = col + excelRow;
                                if (ws[cellRef]) ws[cellRef].s = headerStyle;
                            });
                        });

                        // ── Total row styles ─────────────────────────────────
                        totalRows.forEach(rowIndex => {
                            const excelRow = rowIndex + 1;
                            ['A', 'B', 'C', 'D', 'E', 'F', 'G'].forEach(col => {
                                const cellRef = col + excelRow;
                                if (!ws[cellRef]) ws[cellRef] = {
                                    v: "",
                                    t: "s"
                                };
                                ws[cellRef].s = totalRowStyle;
                            });
                        });

                        // ── Row Heights ──────────────────────────────────────
                        if (!ws['!rows']) ws['!rows'] = [];
                        weekHeaderRows.forEach(rowIndex => {
                            ws['!rows'][rowIndex] = {
                                hpt: 30
                            }; // ✅ Height badai
                        });

                        // ── Print Setup — Landscape ──────────────────────────
                        ws['!pageSetup'] = {
                            orientation: 'landscape',
                            fitToWidth: 1,
                            fitToHeight: 0
                        };
                        ws['!margins'] = {
                            left: 0.5,
                            right: 0.5,
                            top: 0.5,
                            bottom: 0.5,
                            header: 0.3,
                            footer: 0.3
                        };

                        // ── Export ───────────────────────────────────────────
                        let wb = XLSX.utils.book_new();
                        XLSX.utils.book_append_sheet(wb, ws, 'Staff Route Data');
                        XLSX.writeFile(wb, fileName);

                        Swal.fire({
                            icon: "success",
                            title: "Export Successful!",
                            text: "Your Excel file has been downloaded.",
                            confirmButtonColor: "#28a745",
                            confirmButtonText: "OK"
                        });
                    },
                    error: function(error) {
                        Swal.fire({
                            icon: "error",
                            title: "Error!",
                            text: "An error occurred while fetching the data. Please try again.",
                            confirmButtonColor: "#d33",
                            confirmButtonText: "OK"
                        });
                    }
                });
            });

            $(window).on("load", function() {
                $(".app-sidebar-toggle").click();
            });

            $(".collapse_btn .btn_global").click(function() {
                const $currentWrapper = $(this).closest(".staff_routes_week_wrapper");

                if (!$currentWrapper.hasClass("expanded")) {
                    $(".staff_routes_week_wrapper").not($currentWrapper).hide();
                    $currentWrapper.css("width", "100%").addClass("expanded");
                    $currentWrapper.find('.collapse_btn .hoverable_txt span').text("UnCollapse For All Week View");
                } else {
                    $(".staff_routes_week_wrapper").show().css("width", "25%").removeClass("expanded");
                    $currentWrapper.find('.collapse_btn .hoverable_txt span').text("Collapsing Week For Full View");
                }
            });
        });

        $(document).on('click', '.printBtn', function() {
            var selected_month = $('.selected_month_ajax').attr('selected-month');
            var staff_route_id = $('.selected_month_ajax').attr('staff-route-id');

            if (selected_month && staff_route_id) {
                var downloadUrl = '{{ url('route-details-pdf') }}?selected_month=' + encodeURIComponent(
                    selected_month) + '&id=' + encodeURIComponent(staff_route_id);
                window.location.href = downloadUrl; // Opens the generated PDF
            } else {
                alert("Please select a valid month and route.");
            }
        });

        $(document).ready(function() {

            $('#sortAtoZ').on('click', function() {
                $('.routes_wrapper').each(function() {
                    let routes = $(this).find('.muller_honda_wrapper_update').get();

                    routes.sort(function(a, b) {
                        let nameA = $(a).data('client-name').toUpperCase();
                        let nameB = $(b).data('client-name').toUpperCase();
                        return nameA < nameB ? -1 : nameA > nameB ? 1 : 0;
                    });

                    $(this).empty().append(routes);
                });
            });

            $('#sortMostRecent').on('click', function() {
                $('.routes_wrapper').each(function() {
                    let routes = $(this).find('.muller_honda_wrapper_update').get();

                    routes.sort(function(a, b) {
                        let dateA = new Date($(a).data('date'));
                        let dateB = new Date($(b).data('date'));
                        return dateB - dateA;
                    });

                    $(this).empty().append(routes);
                });
            });

        });

        document.addEventListener('DOMContentLoaded', function() {
            @foreach ($mergedSchedules as $schedule)
                new Sortable(document.getElementById('week-{{ $schedule['week_number'] }}'), {
                    group: 'week-{{ $schedule['week_number'] }}',
                    animation: 250,
                    onEnd: function(evt) {
                        // const sortedItems = Array.from(evt.from.children).map(item => item.getAttribute('data-id'));
                        const sortedItems = Array.from(evt.from.children).map(item => {
                            return item.getAttribute('data-schedule-id');
                        });

                        $.ajax({
                            url: '{{ route('sorted_schedule') }}',
                            type: 'POST',
                            data: {
                                sorted_items: sortedItems,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                console.log('Sorted positions updated successfully!');
                            },
                            error: function(xhr, status, error) {
                                console.error('Error updating positions:', error);
                            }
                        });

                    }
                });
            @endforeach
        });

        // Bulk Move Functionality
        $(document).ready(function() {
            console.log('Bulk Move Functionality initialized');

            // Initialize buttons for already checked checkboxes on page load
            $('.schedule-checkbox:checked').each(function() {
                const weekNumber = $(this).data('week');
                if (weekNumber) {
                    toggleBulkMoveButtons(weekNumber);
                }
            });

            // Function to show/hide bulk move buttons - define it first
            window.toggleBulkMoveButtons = function(weekNumber) {
                console.log('toggleBulkMoveButtons called for week:', weekNumber);
                const $checkedBoxes = $(`.schedule-checkbox[data-week="${weekNumber}"]:checked`);
                const checkedCount = $checkedBoxes.length;
                console.log('Checked count:', checkedCount);
                const $controlsContainer = $(`.move-controls-container[data-week="${weekNumber}"]`);
                const $nextBtn = $(`.bulk-move-btn[data-week="${weekNumber}"][data-direction="next"]`);
                console.log('Controls container found:', $controlsContainer.length);

                if (checkedCount > 0) {
                    // Check if any checked schedule has monthly/biMonthly service frequency
                    let hasMonthlyOrBiMonthly = false;
                    $checkedBoxes.each(function() {
                        const serviceFrequency = $(this).data('service-frequency');
                        if (serviceFrequency === 'monthly' || serviceFrequency === 'biMonthly') {
                            hasMonthlyOrBiMonthly = true;
                            return false; // break loop
                        }
                    });

                    if (hasMonthlyOrBiMonthly) {
                        // Hide buttons if monthly/biMonthly schedule is checked
                        $controlsContainer.css('display', 'none');
                        console.log('Hiding controls container - monthly/biMonthly schedule found');
                    } else {
                        // Show controls container if no monthly/biMonthly schedules
                        $controlsContainer.css('display', 'flex');
                        console.log('Showing controls container');
                        // Always show next button (only next week move available)
                        $nextBtn.css('display', 'inline-block');
                    }
                } else {
                    // No checkboxes selected - hide container
                    $controlsContainer.css('display', 'none');
                    console.log('Hiding controls container');
                }
            };

            // Select All checkbox functionality - use event delegation
            $(document).on('change', '.select-all-week', function() {
                console.log('Select All changed');
                const weekNumber = $(this).data('week');
                const isChecked = $(this).prop('checked');
                console.log('Week:', weekNumber, 'Checked:', isChecked);

                if (isChecked) {
                    // When checking all, trigger change event for each checkbox
                    // This will trigger AJAX validation for each one
                    $(`.schedule-checkbox[data-week="${weekNumber}"]`).each(function() {
                        if (!$(this).prop('checked')) {
                            $(this).prop('checked', true).trigger('change');
                        }
                    });
                } else {
                    // When unchecking all, just uncheck without validation
                    $(`.schedule-checkbox[data-week="${weekNumber}"]`).prop('checked', false);
                    toggleBulkMoveButtons(weekNumber);
                }
            });

            // Individual checkbox functionality - use event delegation
            $(document).on('change', '.schedule-checkbox', function() {
                console.log('Schedule checkbox changed');
                const $checkbox = $(this);
                const weekNumber = $checkbox.data('week');
                const scheduleId = $checkbox.data('schedule-id');
                const clientName = $checkbox.data('client-name');
                const $errorSpan = $(`.schedule-error-message[data-schedule-id="${scheduleId}"]`);
                console.log('Week:', weekNumber, 'Schedule ID:', scheduleId, 'Checked:', $checkbox.is(
                    ':checked'));

                // If checkbox is being checked, validate if it can be moved
                if ($checkbox.is(':checked')) {
                    // Show buttons immediately (will be updated after validation)
                    console.log('Calling toggleBulkMoveButtons');
                    toggleBulkMoveButtons(weekNumber);

                    // Check if schedule can be moved (AJAX validation)
                    $.ajax({
                        url: "{{ route('validate.schedule.move') }}",
                        type: 'POST',
                        data: {
                            schedule_id: scheduleId,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            // Check if monthly/biMonthly service frequency
                            if (response.error && (response.service_frequency == 'monthly' ||
                                    response.service_frequency == 'biMonthly')) {
                                let errorMsg = response.error;
                                $errorSpan.text(errorMsg).css('display', 'inline-block',
                                    'font-size: 10px;');

                                setTimeout(function() {
                                    $errorSpan.fadeOut(1000, function() {
                                        $checkbox.prop('checked', false);
                                        $checkbox.removeData('can-move-next');
                                        updateSelectAllState(weekNumber);
                                        toggleBulkMoveButtons(weekNumber);
                                    });
                                }, 2000);
                                return;
                            }

                            // Store validation data but always allow selection
                            // Warnings will be shown during move operation if needed
                            $checkbox.data('can-move-next', response.can_move_next !== false);

                            // Always allow selection - validation is just informational
                            updateSelectAllState(weekNumber);
                            toggleBulkMoveButtons(weekNumber);
                        },
                        error: function(xhr, status, error) {
                            $checkbox.prop('checked', false);
                            $errorSpan.text('❌ Validation failed').show();
                            setTimeout(function() {
                                $errorSpan.fadeOut(500);
                            }, 2000);
                            toggleBulkMoveButtons(weekNumber);
                            updateSelectAllState(weekNumber);
                        }
                    });
                } else {
                    // Clear error message
                    $errorSpan.hide().text('');

                    // Update "Select All" checkbox state
                    updateSelectAllState(weekNumber);

                    // Show/hide bulk move buttons
                    toggleBulkMoveButtons(weekNumber);
                }
            });

            // Function to update Select All state
            function updateSelectAllState(weekNumber) {
                const totalCheckboxes = $(`.schedule-checkbox[data-week="${weekNumber}"]`).length;
                const checkedCheckboxes = $(`.schedule-checkbox[data-week="${weekNumber}"]:checked`).length;

                $(`.select-all-week[data-week="${weekNumber}"]`).prop('checked', totalCheckboxes ===
                    checkedCheckboxes);
            }

            // Update button icon and text based on move type selection
            function updateButtonForMoveType(weekNumber) {
                const moveType = $(`.move-type-radio[data-week="${weekNumber}"]:checked`).val();
                const $btn = $(`.bulk-move-btn[data-week="${weekNumber}"]`);
                const $btnIcon = $btn.find('.btn-icon i');
                const $btnText = $btn.find('.btn-text');

                if (moveType === 'selective_permanent') {
                    $btnIcon.removeClass('fa-solid fa-arrow-right').addClass('fa-solid fa-check-double');
                    $btnText.text('Permanent Move');
                } else {
                    $btnIcon.removeClass('fa-solid fa-check-double').addClass('fa-solid fa-arrow-right');
                    $btnText.text('Temporary Move');
                }
            }

            // Handle move type radio button change - use event delegation
            $(document).on('change', '.move-type-radio', function() {
                const weekNumber = $(this).data('week');
                updateButtonForMoveType(weekNumber);
            });


            // Bulk Move Button Click - use event delegation
            $(document).on('click', '.bulk-move-btn', function() {
                const weekNumber = $(this).data('week');
                const buttonDirection = $(this).data('direction');

                // Get selected move type
                const moveType = $(`.move-type-radio[data-week="${weekNumber}"]:checked`).val();

                // If permanent move is selected
                if (moveType === 'selective_permanent') {
                    // Get all checked schedule IDs
                    const selectedSchedules = [];
                    $(`.schedule-checkbox[data-week="${weekNumber}"]:checked`).each(function() {
                        const scheduleId = $(this).data('schedule-id');
                        const clientName = $(this).data('client-name');
                        console.log('Selected Schedule ID:', scheduleId, 'Client:', clientName);
                        selectedSchedules.push({
                            schedule_id: scheduleId,
                            client_name: clientName,
                            status: $(this).data('status')
                        });
                    });

                    console.log('Total Selected Schedules:', selectedSchedules.length);
                    console.log('Selected Schedules Data:', selectedSchedules);

                    if (selectedSchedules.length === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'No Selection',
                            text: 'Please select at least one schedule to permanently move.'
                        });
                        return;
                    }

                    // Ask for direction: Next or Back
                    Swal.fire({
                        title: `Permanent Move ${selectedSchedules.length} Schedule(s)?`,
                        html: `<div style="text-align: left;">
                            <p>You are about to <strong>permanently move ${selectedSchedules.length} selected schedule(s)</strong>.</p>
                            <p><strong>What this does:</strong></p>
                            <ul>
                                <li>Moves dates by 7 days (forward or backward)</li>
                                <li>Updates week number</li>
                                <li>Updates client schedule and notes</li>
                            </ul>
                            <p style="color: #28a745;"><strong>Only the selected schedules will be moved.</strong></p>
                            <p><strong>Choose direction:</strong></p>
                        </div>`,
                        icon: 'question',
                        showDenyButton: true,
                        showCancelButton: true,
                        confirmButtonText: '<i class="fa-solid fa-arrow-right"></i> Next Week',
                        denyButtonText: '<i class="fa-solid fa-arrow-left"></i> Previous Week',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#28a745',
                        denyButtonColor: '#6c757d',
                        cancelButtonColor: '#d33'
                    }).then((result) => {
                        let direction = null;

                        if (result.isConfirmed) {
                            direction = 'next';
                        } else if (result.isDenied) {
                            direction = 'previous';
                        } else {
                            return; // Cancelled
                        }

                        // Show loading
                        Swal.fire({
                            title: 'Permanently Moving Schedules...',
                            text: 'Please wait...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Send AJAX request for selective permanent move
                        const selectAllChecked = $(`.select-all-week[data-week="${weekNumber}"]`).prop('checked');
                        $.ajax({
                            url: "{{ route('selective.permanent.move') }}",
                            type: 'POST',
                            data: {
                                schedules: selectedSchedules,
                                direction: direction,
                                select_all: selectAllChecked,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                console.log('Response:', response);
                                console.log('Response message:', response.message);

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: response.message ||
                                        'Selected schedules permanently moved successfully!',
                                    timer: 2000
                                }).then(() => {
                                    window.location.reload();
                                });
                            },
                            error: function(xhr) {
                                let errorMessage =
                                    'Failed to permanently move schedules. Please try again.';

                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                } else if (xhr.responseJSON && xhr.responseJSON
                                    .errors) {
                                    errorMessage = Object.values(xhr.responseJSON
                                        .errors).flat().join('\n');
                                }

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: errorMessage
                                });
                            }
                        });
                    });
                    return;
                }

                // Get all checked schedule IDs (for particular move)
                const selectedSchedules = [];
                $(`.schedule-checkbox[data-week="${weekNumber}"]:checked`).each(function() {
                    selectedSchedules.push({
                        schedule_id: $(this).data('schedule-id'),
                        client_name: $(this).data('client-name'),
                        status: $(this).data('status')
                    });
                });

                if (selectedSchedules.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Selection',
                        text: 'Please select at least one schedule to move.'
                    });
                    return;
                }

                // Show direction choice dialog
                Swal.fire({
                    title: `Move ${selectedSchedules.length} Schedule(s)?`,
                    text: `Choose the direction to move ${selectedSchedules.length} schedule(s):`,
                    icon: 'question',
                    showDenyButton: true,
                    showCancelButton: true,
                    confirmButtonText: '<i class="fa-solid fa-arrow-right"></i> Next Week',
                    denyButtonText: '<i class="fa-solid fa-arrow-left"></i> Previous Week',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#3085d6',
                    denyButtonColor: '#6c757d',
                    cancelButtonColor: '#d33'
                }).then((result) => {
                    let direction = null;

                    if (result.isConfirmed) {
                        direction = 'next';
                    } else if (result.isDenied) {
                        direction = 'previous';
                    } else {
                        return;
                    }

                    // Show loading
                    Swal.fire({
                        title: 'Moving Schedules...',
                        text: 'Please wait...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Send AJAX request
                    const selectAllChecked = $(`.select-all-week[data-week="${weekNumber}"]`).prop('checked');
                    $.ajax({
                        url: '{{ route('bulk.move.schedule') }}',
                        type: 'POST',
                        data: {
                            schedules: selectedSchedules,
                            direction: direction,
                            select_all: selectAllChecked,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message ||
                                    'Schedules moved successfully!',
                                timer: 2000
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            let errorMessage =
                                'Failed to move schedules. Please try again.';

                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.responseJSON && xhr.responseJSON
                                .errors) {
                                errorMessage = Object.values(xhr.responseJSON
                                    .errors).flat().join('\n');
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: errorMessage
                            });
                        }
                    });
                });
            });
        });

        // Edit Monthly/BiMonthly Schedule Date
        $(document).on('click', '.edit-monthly-schedule-btn', function(e) {
            e.stopPropagation(); // Prevent accordion toggle

            const scheduleId = $(this).data('schedule-id');
            const clientName = $(this).data('client-name');
            const startDate = $(this).data('start-date');
            const endDate = $(this).data('end-date');
            const serviceFrequency = $(this).data('service-frequency');

            // Populate modal
            $('#editMonthlyScheduleId').val(scheduleId);
            $('#editMonthlyClientName').text(clientName);
            $('#editMonthlyServiceFrequency').text(serviceFrequency === 'monthly' ? 'Monthly' : 'Bi-Monthly');
            $('#editMonthlyStartDate').val(startDate);

            // Show modal
            $('#editMonthlyScheduleModal').modal('show');
        });

        // Save Monthly/BiMonthly Schedule Date
        $('#saveMonthlyScheduleBtn').on('click', function() {
            const scheduleId = $('#editMonthlyScheduleId').val();
            const newStartDate = $('#editMonthlyStartDate').val();

            if (!newStartDate) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please select a start date'
                });
                return;
            }

            // Show loading
            Swal.fire({
                title: 'Updating...',
                text: 'Please wait while we update the schedule',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '{{ route('client-schedules.update-monthly-date') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    schedule_id: scheduleId,
                    start_date: newStartDate
                },
                success: function(response) {
                    $('#editMonthlyScheduleModal').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message || 'Schedule date updated successfully'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to update schedule. Please try again.';

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage
                    });
                }
            });
        });

        // Timer functionality - Wrap in document ready
        $(document).ready(function() {
            // Live timer function - shows running time next to stop icon
            function startLiveTimer($stopIcon) {
                const startTime = $stopIcon.data('start-time');
                const serviceDate = $stopIcon.data('service-date');

                if (!startTime || !serviceDate) return;

                // Create timer display element if not exists
                let $timerDisplay = $stopIcon.siblings('.timer-display');
                if (!$timerDisplay.length) {
                    $timerDisplay = $(
                        '<span class="timer-display" style="margin-left: 5px; font-size: 14px; color: #dc3545; font-weight: bold;"></span>'
                    );
                    $stopIcon.after($timerDisplay);
                }

                // Update timer every second
                const timerInterval = setInterval(function() {
                    const startDateTime = new Date(serviceDate + ' ' + startTime);
                    const now = new Date();
                    const diff = Math.floor((now - startDateTime) / 1000); // seconds

                    if (diff < 0) {
                        clearInterval(timerInterval);
                        return;
                    }

                    const hours = Math.floor(diff / 3600);
                    const minutes = Math.floor((diff % 3600) / 60);
                    const seconds = diff % 60;

                    const timeStr = String(hours).padStart(2, '0') + ':' +
                        String(minutes).padStart(2, '0') + ':' +
                        String(seconds).padStart(2, '0');

                    $timerDisplay.text(timeStr);
                }, 1000);

                // Store interval ID to clear later
                $stopIcon.data('timer-interval', timerInterval);
            }

            // Check for active timers on page load
            function checkActiveTimers() {
                $.ajax({
                    url: '{{ route('timelogs.active') }}',
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            const currentStaffId = response.current_staff_id;
                            const completedSchedules = response.completed_schedules || [];

                            // Hide icons for completed schedules
                            completedSchedules.forEach(function(scheduleId) {
                                const $startIcon = $(
                                    `.timer-start-icon[data-schedule-id="${scheduleId}"]`
                                );
                                const $stopIcon = $(
                                    `.timer-stop-icon[data-schedule-id="${scheduleId}"]`
                                );
                                $startIcon.hide();
                                $stopIcon.hide();
                            });

                            // Handle active timers
                            if (response.active_timers.length > 0) {
                                response.active_timers.forEach(function(timer) {
                                    // Find the timer icons for this schedule
                                    const $startIcon = $(
                                        `.timer-start-icon[data-schedule-id="${timer.schedule_id}"]`
                                    );
                                    const $stopIcon = $(
                                        `.timer-stop-icon[data-schedule-id="${timer.schedule_id}"]`
                                    );

                                    if ($startIcon.length && $stopIcon.length) {
                                        if (timer.staff_id === currentStaffId) {
                                            // Current staff's timer - show stop icon with live timer
                                            $startIcon.hide();
                                            $stopIcon.data('timelog-id', timer.id);
                                            $stopIcon.data('start-time', timer.start_time);
                                            $stopIcon.data('service-date', timer.service_date);
                                            $stopIcon.show();

                                            // Start live timer display
                                            startLiveTimer($stopIcon);
                                        } else {
                                            // Another staff's timer - hide both icons (can't start)
                                            $startIcon.hide();
                                            $stopIcon.hide();
                                        }
                                    }
                                });
                            }
                        }
                    },
                    error: function(xhr) {
                        console.error('Failed to check active timers:', xhr);
                    }
                });
            }

            // Call on page load
            checkActiveTimers();

            // Timer start functionality
            $(document).on('click', '.timer-start-icon', function() {
                console.log('Timer start icon clicked');
                const $icon = $(this);
                const scheduleId = $icon.data('schedule-id');
                const routeId = $icon.data('route-id');
                const $stopIcon = $icon.siblings('.timer-stop-icon');
                const $loadingIcon = $icon.siblings('.timer-loading-icon');

                console.log('Schedule ID:', scheduleId, 'Route ID:', routeId);

                // Show loading
                $icon.hide();
                $loadingIcon.show();

                $.ajax({
                    url: '{{ route('timelogs.start') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        schedule_id: scheduleId,
                        route_id: routeId
                    },
                    success: function(response) {
                        $loadingIcon.hide();

                        if (response.success) {
                            // Store timelog ID, start time, and service date
                            $stopIcon.data('timelog-id', response.timelog_id);
                            $stopIcon.data('start-time', response.start_time);
                            $stopIcon.data('service-date', response.service_date);
                            $stopIcon.show();

                            // Start live timer display
                            startLiveTimer($stopIcon);

                            Swal.fire({
                                icon: 'success',
                                title: 'Timer Started',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            $icon.show();
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        $loadingIcon.hide();
                        $icon.show();

                        let errorMessage = 'Failed to start timer';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage
                        });
                    }
                });
            });

            $(document).on('click', '.timer-stop-icon', function() {
                const $icon = $(this);
                const timelogId = $icon.data('timelog-id');
                const $startIcon = $icon.siblings('.timer-start-icon');
                const $loadingIcon = $icon.siblings('.timer-loading-icon');

                if (!timelogId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No active timer found'
                    });
                    return;
                }

                // Show loading
                $icon.hide();
                $loadingIcon.show();

                $.ajax({
                    url: '{{ route('timelogs.stop') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        timelog_id: timelogId
                    },
                    success: function(response) {
                        $loadingIcon.hide();

                        if (response.success) {
                            // Clear live timer interval
                            const timerInterval = $icon.data('timer-interval');
                            if (timerInterval) {
                                clearInterval(timerInterval);
                            }

                            // Hide timer display
                            $icon.siblings('.timer-display').remove();

                            // Hide both icons (schedule is complete, no need to show timer anymore)
                            $icon.hide();
                            $startIcon.hide();
                            $icon.data('timelog-id', '');

                            Swal.fire({
                                icon: 'success',
                                title: 'Timer Stopped',
                                html: `${response.message}<br><strong>Total Hours: ${response.total_hours}</strong>`,
                                timer: 3000,
                                showConfirmButton: false
                            });
                        } else {
                            $icon.show();
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        $loadingIcon.hide();
                        $icon.show();

                        let errorMessage = 'Failed to stop timer';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage
                        });
                    }
                });
            });
        }); // End of document.ready for timer functionality
    </script>

    @if (auth()->user()->hasRole('staff'))
        <script>
            $(document).ready(function() {
                const logHoursModal = $('#logHoursModal');
                const logHoursForm = $('#logHoursForm');
                const logHoursDay = $('#log_hours_day');
                const logHoursError = $('#logHoursFormError');
                const logHoursTableBody = $('#logHoursTableBody');
                const logHoursTotal = $('#logHoursTotal');

                function buildWeekDayOptions(weekStart, weekEnd) {
                    logHoursDay.empty().append('<option value="">Select Day</option>');

                    const start = new Date(weekStart + 'T00:00:00');
                    const end = new Date(weekEnd + 'T00:00:00');
                    const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

                    for (let current = new Date(start); current <= end; current.setDate(current.getDate() + 1)) {
                        const value = `${current.getFullYear()}-${String(current.getMonth() + 1).padStart(2, '0')}-${String(current.getDate()).padStart(2, '0')}`;
                        const label = `${dayNames[current.getDay()]}, ${monthNames[current.getMonth()]} ${current.getDate()}, ${current.getFullYear()}`;
                        logHoursDay.append(`<option value="${value}">${label}</option>`);
                    }
                }

                function renderLogHoursEntries(entries, totalHours) {
                    logHoursTableBody.empty();

                    if (!entries || entries.length === 0) {
                        logHoursTableBody.append(
                            '<tr id="logHoursEmptyRow"><td colspan="4" class="text-center text-muted">No log hours added yet.</td></tr>'
                        );
                    } else {
                        entries.forEach(function(entry) {
                            logHoursTableBody.append(`
                                <tr>
                                    <td>${entry.day}</td>
                                    <td>${entry.start_time}</td>
                                    <td>${entry.end_time}</td>
                                    <td>${entry.duration_label}</td>
                                </tr>
                            `);
                        });
                    }

                    logHoursTotal.text(parseFloat(totalHours || 0).toFixed(2));

                    const routeId = $('#log_hours_route_id').val();
                    const weekStart = $('#log_hours_week_start').val();
                    $(`.log-hours-btn-total[data-route-id="${routeId}"][data-week-start="${weekStart}"]`)
                        .text(parseFloat(totalHours || 0).toFixed(2));
                }

                function loadLogHoursEntries() {
                    const routeId = $('#log_hours_route_id').val();
                    const weekStart = $('#log_hours_week_start').val();

                    $.ajax({
                        url: "{{ route('staff-log-hours.index') }}",
                        type: 'GET',
                        data: {
                            route_id: routeId,
                            week_start_date: weekStart
                        },
                        success: function(response) {
                            if (response.success) {
                                renderLogHoursEntries(response.entries, response.total_hours);
                            }
                        },
                        error: function(xhr) {
                            const message = xhr.responseJSON?.message || 'Failed to load log hours.';
                            logHoursError.text(message).show();
                        }
                    });
                }

                logHoursModal.on('show.bs.modal', function(event) {
                    const button = $(event.relatedTarget);
                    const routeId = button.data('route-id');
                    const weekNumber = button.data('week-number');
                    const weekStart = button.data('week-start');
                    const weekEnd = button.data('week-end');
                    const weekLabel = button.data('week-label');

                    $('#log_hours_route_id').val(routeId);
                    $('#log_hours_week_number').val(weekNumber);
                    $('#log_hours_week_start').val(weekStart);
                    $('#log_hours_week_end').val(weekEnd);
                    $('#logHoursWeekLabel').text(weekLabel);

                    logHoursForm[0].reset();
                    $('#log_hours_route_id').val(routeId);
                    $('#log_hours_week_number').val(weekNumber);
                    $('#log_hours_week_start').val(weekStart);
                    $('#log_hours_week_end').val(weekEnd);
                    logHoursError.hide().text('');

                    buildWeekDayOptions(weekStart, weekEnd);
                    loadLogHoursEntries();
                });

                logHoursForm.on('submit', function(e) {
                    e.preventDefault();
                    logHoursError.hide().text('');

                    const startTime = $('#log_hours_start_time').val();
                    const endTime = $('#log_hours_end_time').val();

                    if (startTime && endTime && startTime >= endTime) {
                        logHoursError.text('End time must be after start time.').show();
                        return;
                    }

                    const submitBtn = $('#logHoursSubmitBtn');
                    submitBtn.prop('disabled', true);

                    $.ajax({
                        url: "{{ route('staff-log-hours.store') }}",
                        type: 'POST',
                        data: logHoursForm.serialize(),
                        success: function(response) {
                            if (response.success) {
                                renderLogHoursEntries(response.entries, response.total_hours);
                                $('#log_hours_start_time').val('');
                                $('#log_hours_end_time').val('');
                                logHoursDay.val('');

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Added',
                                    text: response.message,
                                    timer: 1800,
                                    showConfirmButton: false
                                });
                            }
                        },
                        error: function(xhr) {
                            let message = 'Failed to add log hours.';
                            if (xhr.responseJSON?.message) {
                                message = xhr.responseJSON.message;
                            } else if (xhr.responseJSON?.errors) {
                                message = Object.values(xhr.responseJSON.errors).flat().join(' ');
                            }
                            logHoursError.text(message).show();
                        },
                        complete: function() {
                            submitBtn.prop('disabled', false);
                        }
                    });
                });
            });
        </script>
    @endif
@endpush
