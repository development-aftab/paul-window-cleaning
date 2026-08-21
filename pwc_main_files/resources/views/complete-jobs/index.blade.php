@extends('theme.layout.master')

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet" />
@endpush

@section('navbar-title')
    <div class="custom_justify_between">
        <h2 class="navbar_PageTitle">Complete Jobs</h2>
    </div>
    <div class="txt_field custom_search">
        <input type="search" placeholder="Search" class="search_input custom_search_box">
        <i class="fa-solid fa-magnifying-glass search_icon"></i>
    </div>
@endsection

@section('content')
    <section class="client_management staff_manag complete_jobs_section">
        <div class="container-fluid custom_container">
            <div class="row">
                <div class="col-md-12">
                    <div class="custom_div">
                        <!-- Filters -->
                        <div class="row row_gap">
                            <div class="col-md-4">
                                <div class="txt_field">
                                    <label for="filter_date_range">Filter by Date</label>
                                    <input class="form-control" type="text" id="filter_date_range" placeholder="Select date range" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="txt_field custom_select_route">
                                    <label for="filter_route">Filter by Route</label>
                                    <select class="form-select selectRoute" id="filter_route">
                                        <option value="">All Routes</option>
                                        @foreach ($routes ?? [] as $route)
                                            <option value="{{ $route->id }}">{{ $route->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="txt_field">
                                    <label for="filter_week">Filter by Week</label>
                                    <select class="form-select" id="filter_week">
                                        <option value="">All Weeks</option>
                                        <option value="week0">Week 1</option>
                                        <option value="week1">Week 2</option>
                                        <option value="week2">Week 3</option>
                                        <option value="week3">Week 4</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="custom_table">
                                    <div class="table-responsive">
                                        <table id="jobs_table" class="table jobs_table datatable">
                                            <thead>
                                            <tr>
                                                <th>Client Name</th>
                                                <th>Payment Type</th>
                                                <th>Amount</th>
                                                <th>Service Date</th>
                                                <th>Service Day</th>
                                                <th>Route</th>
                                                <th>Week</th>
                                                <th>Staff Name</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse ($completeJobs->filter(function($job) { return $job->clientSchedulePayment; }) as $job)
                                                @php $paymentType = $job->clientSchedulePayment->payment_type; @endphp
                                                <tr data-route-id="{{ $job->clientName->clientRouteStaff->first()->route_id ?? '' }}"
                                                    data-week="{{ $job->week }}"
                                                    data-date="{{ $job->service_date }}">
                                                    <td>{{ $job->clientName->name ?? 'N/A' }}</td>
                                                    <td>
                                                        <span class="badge bg-secondary">{{ ucfirst($paymentType) }}</span>
                                                    </td>
                                                    <td>${{ $job->clientSchedulePayment->final_price ?? 'N/A' }}</td>
                                                    <td>
                                                        {{ $job->service_date ? \Carbon\Carbon::parse($job->service_date)->format('m-d-Y') : 'N/A' }}
                                                    </td>
                                                    <td>
                                                        {{ $job->service_date ? \Carbon\Carbon::parse($job->service_date)->format('l') : 'N/A' }}
                                                    </td>
                                                    <td>{{ $job->clientName->clientRouteStaff->first()->route->name ?? 'N/A' }}
                                                    </td>
                                                    <td>Week {{ (int) str_replace('week', '', $job->week) + 1 }}</td>
                                                    <td>
                                                        @if ($job->staff_id)
                                                            {{ \App\Models\User::find($job->staff_id)->name ?? 'N/A' }}
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                    <td>
                                                    <span class="badge bg-success">
                                                        {{ ucfirst($job->status) }}
                                                    </span>
                                                    </td>
                                                    <td>
                                                        <div class="dropdown">
                                                            <button class="dropdown-toggle"
                                                                    type="button" data-bs-toggle="dropdown"
                                                                    aria-expanded="false">
                                                                <i class="fa-solid fa-ellipsis"></i>
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                @if ($paymentType === 'invoice')
                                                                    <li>
                                                                        <a class="dropdown-item"
                                                                           href="{{ route('clients.show', $job->client_id) }}">
                                                                            <i class="fa-solid fa-user me-2"></i>View
                                                                            Client
                                                                        </a>
                                                                    </li>
                                                                @endif
                                                                <li>
                                                                    <a class="dropdown-item view-report-btn"
                                                                       href="javascript:void(0)"
                                                                       data-job-id="{{ $job->id }}"
                                                                       data-payment-type="{{ $paymentType }}"
                                                                       data-client-name="{{ $job->clientName->name ?? 'N/A' }}"
                                                                       data-service-date="{{ $job->service_date }}"
                                                                       data-option="{{ $job->clientSchedulePayment->option ?? '' }}"
                                                                       data-option-two="{{ $job->clientSchedulePayment->option_two ?? '' }}"
                                                                       data-option-three="{{ $job->clientSchedulePayment->option_three ?? '' }}"
                                                                       data-option-four="{{ $job->clientSchedulePayment->option_four ?? '' }}"
                                                                       data-option-five="{{ $job->clientSchedulePayment->option_five ?? '' }}"
                                                                       data-reason="{{ $job->clientSchedulePayment->reason ?? '' }}"
                                                                       data-scope="{{ $job->clientSchedulePayment->scope ?? '' }}"
                                                                       data-partial-scope="{{ $job->clientSchedulePayment->partial_completed_scope ?? '' }}"
                                                                       data-price-one="{{ $job->clientSchedulePayment->price_charge_one ?? '' }}"
                                                                       data-price-two="{{ $job->clientSchedulePayment->price_charge_two ?? '' }}"
                                                                       data-amount="{{ $job->clientSchedulePayment->amount ?? '' }}"
                                                                       data-day-number="{{ $job->clientSchedulePayment->day_number ?? '' }}"
                                                                       data-start-time="{{ $job->clientSchedulePayment->start_time ?? '' }}"
                                                                       data-end-time="{{ $job->clientSchedulePayment->end_time ?? '' }}"
                                                                       data-final-price="{{ $job->clientSchedulePayment->final_price ?? '' }}">
                                                                        <i class="fa-solid fa-file-lines me-2"></i>View
                                                                        Report
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- View Report Modal - CASH -->
    <div class="modal fade" id="viewReportModalCash" tabindex="-1" aria-labelledby="viewReportModalCashLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewReportModalCashLabel">Job Completion Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="create_clients_wrapper_staff shadow_box_wrapper">
                        <div class="custom_justify_between">
                            <div class="custom_radio_mullerHonda">
                                <label class="form-check-label" id="modal_cash_client_name">Client Name</label>
                                <span>(Cash)</span>
                            </div>
                            <h3 class="pricePlus" id="modal_cash_final_price">$0.00</h3>
                        </div>

                        <!-- Service Date -->
                        <div class="row custom_row mt-3">
                            <div class="col-md-6">
                                <div class="txt_field">
                                    <label for="modal_cash_service_date">Service Date</label>
                                    <input class="form-control" type="text" id="modal_cash_service_date" disabled>
                                </div>
                            </div>
                        </div>

                        <div class="custom_partially_changed">
                            <div class="row custom_row">
                                <!-- Completed no Change -->
                                <div class="col-md-12 custom_no_change">
                                    <div class="custom_radio">
                                        <input class="form-check-input" type="checkbox" id="modal_cash_completed"
                                               disabled>
                                        <label class="form-check-label" for="modal_cash_completed">Completed no
                                            Change</label>
                                    </div>
                                </div>

                                <!-- Completed but did not receive payment -->
                                <div class="col-md-12 partially_completed_wrapper">
                                    <div class="custom_radio">
                                        <input class="form-check-input" type="checkbox" id="modal_cash_noPayment"
                                               disabled>
                                        <label class="form-check-label" for="modal_cash_noPayment">Completed but did not
                                            receive payment</label>
                                    </div>
                                    <div class="row reason_input_fileds_wrapper" id="modal_cash_noPayment_reason"
                                         style="display: none;">
                                        <div class="col-md-12">
                                            <div class="txt_field">
                                                <input class="form-control" type="text"
                                                       id="modal_cash_reason_noPayment" placeholder="Reason" disabled>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Partially Completed -->
                                <div class="col-md-12 partially_completed_wrapper">
                                    <div class="custom_radio">
                                        <input class="form-check-input" type="checkbox" id="modal_cash_partially"
                                               disabled>
                                        <label class="form-check-label" for="modal_cash_partially">Partially
                                            Completed</label>
                                    </div>
                                    <div class="row reason_input_fileds_wrapper" id="modal_cash_partially_fields"
                                         style="display: none;">
                                        <div class="col-md-4">
                                            <div class="txt_field">
                                                <input class="form-control" type="text"
                                                       id="modal_cash_reason_partially" placeholder="Reason" disabled>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="txt_field">
                                                <input class="form-control" type="text"
                                                       id="modal_cash_scope_partially" placeholder="Scope Of Work Completed"
                                                       disabled>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="txt_field">
                                                <input class="form-control" type="text"
                                                       id="modal_cash_price_charged_one" placeholder="Price Charged"
                                                       disabled>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Paid on prior date of service -->
                                <div class="col-md-12">
                                    <div class="custom_radio">
                                        <input class="form-check-input" type="checkbox" id="modal_cash_option_two"
                                               disabled>
                                        <label class="form-check-label" for="modal_cash_option_two">Paid on prior date of
                                            service</label>
                                    </div>
                                </div>

                                <!-- Paid extra for dates -->
                                <div class="col-md-12 partially_completed_wrapper">
                                    <div class="custom_radio">
                                        <input class="form-check-input" type="checkbox" id="modal_cash_option_three"
                                               disabled>
                                        <label class="form-check-label" for="modal_cash_option_three">Paid extra for
                                            <span id="modal_cash_day_number">0</span> dates</label>
                                    </div>
                                    <div class="row reason_input_fileds_wrapper" id="modal_cash_extra_paid_fields"
                                         style="display: none;">
                                        <div class="col-md-6">
                                            <div class="txt_field">
                                                <input class="form-control" type="text"
                                                       id="modal_cash_amount" placeholder="Amount" disabled>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Extra Work Completed -->
                                <div class="col-md-12 partially_completed_wrapper">
                                    <div class="custom_radio">
                                        <input class="form-check-input" type="checkbox" id="modal_cash_option_four"
                                               disabled>
                                        <label class="form-check-label" for="modal_cash_option_four">Extra Work
                                            Completed</label>
                                    </div>
                                    <div class="row reason_input_fileds_wrapper" id="modal_cash_extra_work_fields"
                                         style="display: none;">
                                        <div class="col-md-6">
                                            <div class="txt_field">
                                                <input class="form-control" type="text"
                                                       id="modal_cash_scope_extra_work"
                                                       placeholder="Scope Of Additional Work Completed" disabled>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="txt_field">
                                                <input class="form-control" type="text"
                                                       id="modal_cash_price_charged_two" placeholder="Price Charged"
                                                       disabled>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Log time -->
                                <div class="col-md-12 partially_completed_wrapper">
                                    <div class="custom_radio">
                                        <input class="form-check-input" type="checkbox" id="modal_cash_logTime"
                                               disabled>
                                        <label class="form-check-label" for="modal_cash_logTime">Log time</label>
                                    </div>
                                    <div class="row reason_input_fileds_wrapper" id="modal_cash_log_time_fields"
                                         style="display: none;">
                                        <div class="col-md-6">
                                            <div class="txt_field">
                                                <label>Start Time</label>
                                                <input class="form-control" type="time" id="modal_cash_start_time"
                                                       disabled>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="txt_field">
                                                <label>End Time</label>
                                                <input class="form-control" type="time" id="modal_cash_end_time"
                                                       disabled>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Omit -->
                                <div class="col-md-12 partially_completed_wrapper">
                                    <div class="custom_radio">
                                        <input class="form-check-input" type="checkbox" id="modal_cash_omit" disabled>
                                        <label class="form-check-label" for="modal_cash_omit">Omit</label>
                                    </div>
                                    <div class="row reason_input_fileds_wrapper" id="modal_cash_omit_reason"
                                         style="display: none;">
                                        <div class="col-md-12">
                                            <div class="txt_field">
                                                <input class="form-control" type="text"
                                                       id="modal_cash_reason_omit" placeholder="Reason" disabled>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Report Modal - INVOICE -->
    <div class="modal fade" id="viewReportModalInvoice" tabindex="-1" aria-labelledby="viewReportModalInvoiceLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewReportModalInvoiceLabel">Job Completion Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="create_clients_wrapper_staff shadow_box_wrapper">
                        <div class="custom_justify_between">
                            <div class="custom_radio_mullerHonda">
                                <label class="form-check-label" id="modal_invoice_client_name">Client Name</label>
                                <span>(Invoice)</span>
                            </div>
                            <h3 class="pricePlus" id="modal_invoice_final_price">$0.00</h3>
                        </div>

                        <!-- Service Date -->
                        <div class="row custom_row mt-3">
                            <div class="col-md-6">
                                <div class="txt_field">
                                    <label for="modal_invoice_service_date">Service Date</label>
                                    <input class="form-control" type="text" id="modal_invoice_service_date" disabled>
                                </div>
                            </div>
                        </div>

                        <div class="custom_partially_changed">
                            <div class="row custom_row">
                                <!-- Completed no Change -->
                                <div class="col-md-12 custom_no_change">
                                    <div class="custom_radio">
                                        <input class="form-check-input" type="checkbox" id="modal_invoice_completed"
                                               disabled>
                                        <label class="form-check-label" for="modal_invoice_completed">Completed no
                                            Change</label>
                                    </div>
                                </div>

                                <!-- Partially Completed -->
                                <div class="col-md-12 partially_completed_wrapper">
                                    <div class="custom_radio">
                                        <input class="form-check-input" type="checkbox" id="modal_invoice_partially"
                                               disabled>
                                        <label class="form-check-label" for="modal_invoice_partially">Partially
                                            Completed</label>
                                    </div>
                                    <div class="row reason_input_fileds_wrapper" id="modal_invoice_partially_fields"
                                         style="display: none;">
                                        <div class="col-md-4">
                                            <div class="txt_field">
                                                <input class="form-control" type="text"
                                                       id="modal_invoice_reason_partially" placeholder="Reason" disabled>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="txt_field">
                                                <input class="form-control" type="text"
                                                       id="modal_invoice_scope_partially"
                                                       placeholder="Scope Of Work Completed" disabled>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="txt_field">
                                                <input class="form-control" type="text"
                                                       id="modal_invoice_price_charged_one" placeholder="Price Charged"
                                                       disabled>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Extra Work Completed (option_two for invoice) -->
                                <div class="col-md-12 partially_completed_wrapper">
                                    <div class="custom_radio">
                                        <input class="form-check-input" type="checkbox" id="modal_invoice_option_two"
                                               disabled>
                                        <label class="form-check-label" for="modal_invoice_option_two">Extra Work
                                            Completed</label>
                                    </div>
                                    <div class="row reason_input_fileds_wrapper" id="modal_invoice_extra_work_fields"
                                         style="display: none;">
                                        <div class="col-md-6">
                                            <div class="txt_field">
                                                <input class="form-control" type="text"
                                                       id="modal_invoice_scope_extra_work"
                                                       placeholder="Scope Of Additional Work Completed" disabled>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="txt_field">
                                                <input class="form-control" type="text"
                                                       id="modal_invoice_price_charged_two" placeholder="Price Charged"
                                                       disabled>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Log time (option_three for invoice) -->
                                <div class="col-md-12 partially_completed_wrapper">
                                    <div class="custom_radio">
                                        <input class="form-check-input" type="checkbox" id="modal_invoice_option_three"
                                               disabled>
                                        <label class="form-check-label" for="modal_invoice_option_three">Log time</label>
                                    </div>
                                    <div class="row reason_input_fileds_wrapper" id="modal_invoice_log_time_fields"
                                         style="display: none;">
                                        <div class="col-md-6">
                                            <div class="txt_field">
                                                <label>Start Time</label>
                                                <input class="form-control" type="time" id="modal_invoice_start_time"
                                                       placeholder="Start Time" disabled>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="txt_field">
                                                <label>End Time</label>
                                                <input class="form-control" type="time" id="modal_invoice_end_time"
                                                       placeholder="End Time" disabled>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Omit -->
                                <div class="col-md-12 partially_completed_wrapper">
                                    <div class="custom_radio">
                                        <input class="form-check-input" type="checkbox" id="modal_invoice_omit" disabled>
                                        <label class="form-check-label" for="modal_invoice_omit">Omit</label>
                                    </div>
                                    <div class="row reason_input_fileds_wrapper" id="modal_invoice_omit_reason"
                                         style="display: none;">
                                        <div class="col-md-12">
                                            <div class="txt_field">
                                                <input class="form-control" type="text"
                                                       id="modal_invoice_reason_omit" placeholder="Reason" disabled>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        $(document).ready(function() {

            $(".selectRoute").select2({
                placeholder: "Select a Route",
                allowClear: true
            });

            var filterStartDate = '';
            var filterEndDate = '';

            flatpickr("#filter_date_range", {
                mode: "range",
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "m-d-Y",
                onChange: function(selectedDates) {
                    if (selectedDates.length === 2) {
                        var toStr = function(d) {
                            return d.getFullYear() + '-' +
                                ('0' + (d.getMonth() + 1)).slice(-2) + '-' +
                                ('0' + d.getDate()).slice(-2);
                        };
                        filterStartDate = toStr(selectedDates[0]);
                        filterEndDate = toStr(selectedDates[1]);
                    } else {
                        filterStartDate = '';
                        filterEndDate = '';
                    }
                    applyFilters();
                }
            });

            // Initialize DataTable for the combined jobs list
            var jobsTable = $('.jobs_table').DataTable({
                "searching": true,
                "bLengthChange": true,
                "paging": true,
                "info": true,
                "ordering": true,
                "order": [
                    [3, "desc"]
                ], // Sort by service date descending
                "pageLength": 10,
                "lengthMenu": [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"]
                ]
            });

            // Custom search box
            $(document).on("input", '.custom_search_box', function() {
                var searchValue = $(this).val();
                jobsTable.search(searchValue).draw();
            });

            // Filters
            function applyFilters() {
                var filterRoute = $('#filter_route').val();
                var filterWeek = $('#filter_week').val();

                // Use DataTables custom search
                $.fn.dataTable.ext.search.push(
                    function(settings, data, dataIndex) {
                        if (settings.nTable.id !== 'jobs_table') {
                            return true;
                        }

                        var row = jobsTable.row(dataIndex).node();
                        var routeId = $(row).data('route-id') || '';
                        var week = $(row).data('week') || '';
                        var date = $(row).data('date') || '';

                        if (filterRoute && routeId != filterRoute) {
                            return false;
                        }

                        if (filterWeek && week != filterWeek) {
                            return false;
                        }

                        if (filterStartDate && date && date < filterStartDate) {
                            return false;
                        }

                        if (filterEndDate && date && date > filterEndDate) {
                            return false;
                        }

                        return true;
                    }
                );

                jobsTable.draw();
                $.fn.dataTable.ext.search.pop();
            }

            $('#filter_route, #filter_week').on('change', applyFilters);

            // Handle View Report button click
            $(document).on('click', '.view-report-btn', function() {
                var serviceDate = $(this).data('service-date');
                var option = $(this).data('option');
                var optionTwo = $(this).data('option-two');
                var optionThree = $(this).data('option-three');
                var optionFour = $(this).data('option-four');
                var optionFive = $(this).data('option-five');
                var reason = $(this).data('reason');
                var scope = $(this).data('scope');
                var partialScope = $(this).data('partial-scope');
                var priceOne = $(this).data('price-one');
                var priceTwo = $(this).data('price-two');
                var amount = $(this).data('amount');
                var dayNumber = $(this).data('day-number');
                var startTime = $(this).data('start-time');
                var endTime = $(this).data('end-time');
                var finalPrice = $(this).data('final-price');
                var clientName = $(this).data('client-name');
                var paymentType = $(this).data('payment-type'); // 'cash' or 'invoice'

                // Format service date
                var formattedDate = 'N/A';
                if (serviceDate) {
                    var date = new Date(serviceDate);
                    formattedDate = ('0' + (date.getMonth() + 1)).slice(-2) + '-' +
                        ('0' + date.getDate()).slice(-2) + '-' +
                        date.getFullYear();
                }

                if (paymentType === 'cash') {
                    // CASH MODAL
                    // Reset all checkboxes and fields
                    $('#modal_cash_completed, #modal_cash_noPayment, #modal_cash_partially, #modal_cash_option_two, #modal_cash_option_three, #modal_cash_option_four, #modal_cash_logTime, #modal_cash_omit')
                        .prop('checked', false);
                    $('#modal_cash_noPayment_reason, #modal_cash_partially_fields, #modal_cash_extra_paid_fields, #modal_cash_extra_work_fields, #modal_cash_log_time_fields, #modal_cash_omit_reason')
                        .hide();
                    $('#modal_cash_reason_noPayment, #modal_cash_reason_partially, #modal_cash_scope_partially, #modal_cash_price_charged_one, #modal_cash_amount, #modal_cash_scope_extra_work, #modal_cash_price_charged_two, #modal_cash_start_time, #modal_cash_end_time, #modal_cash_reason_omit')
                        .val('');
                    $('#modal_cash_day_number').text('0');

                    // Set client name and final price
                    $('#modal_cash_client_name').text(clientName);
                    $('#modal_cash_final_price').text(finalPrice ? '$' + parseFloat(finalPrice).toFixed(2) :
                        '$0.00');
                    $('#modal_cash_service_date').val(formattedDate);

                    // Set completion status checkboxes
                    if (option === 'completed') {
                        $('#modal_cash_completed').prop('checked', true);
                    } else if (option === 'no_payment') {
                        $('#modal_cash_noPayment').prop('checked', true);
                        if (reason) {
                            $('#modal_cash_noPayment_reason').show();
                            $('#modal_cash_reason_noPayment').val(reason);
                        }
                    } else if (option === 'omit') {
                        $('#modal_cash_omit').prop('checked', true);
                        if (reason) {
                            $('#modal_cash_omit_reason').show();
                            $('#modal_cash_reason_omit').val(reason);
                        }
                    }

                    // Partially Completed
                    if (optionFive === 'partially') {
                        $('#modal_cash_partially').prop('checked', true);
                        $('#modal_cash_partially_fields').show();
                        if (reason) {
                            $('#modal_cash_reason_partially').val(reason);
                        }
                        if (partialScope) {
                            $('#modal_cash_scope_partially').val(partialScope);
                        }
                        if (priceOne) {
                            $('#modal_cash_price_charged_one').val(priceOne);
                        }
                    }

                    // Paid on prior date of service
                    if (optionTwo === 'paid_on_prior') {
                        $('#modal_cash_option_two').prop('checked', true);
                    }

                    // option_three holds either 'extra_paid_for_date' or 'logTime'
                    if (optionThree === 'extra_paid_for_date') {
                        $('#modal_cash_option_three').prop('checked', true);
                        $('#modal_cash_extra_paid_fields').show();
                        $('#modal_cash_day_number').text(dayNumber || '0');
                        if (amount) {
                            $('#modal_cash_amount').val(amount);
                        }
                    } else if (optionThree === 'logTime') {
                        $('#modal_cash_logTime').prop('checked', true);
                        $('#modal_cash_log_time_fields').show();
                        if (startTime) {
                            $('#modal_cash_start_time').val(startTime);
                        }
                        if (endTime) {
                            $('#modal_cash_end_time').val(endTime);
                        }
                    }

                    // Extra Work Completed
                    if (optionFour === 'extra_work') {
                        $('#modal_cash_option_four').prop('checked', true);
                        $('#modal_cash_extra_work_fields').show();
                        if (scope) {
                            $('#modal_cash_scope_extra_work').val(scope);
                        }
                        if (priceTwo) {
                            $('#modal_cash_price_charged_two').val(priceTwo);
                        }
                    }

                    // Show CASH modal
                    $('#viewReportModalCash').modal('show');

                } else if (paymentType === 'invoice') {
                    // INVOICE MODAL
                    // Reset all checkboxes and fields
                    $('#modal_invoice_completed, #modal_invoice_partially, #modal_invoice_option_two, #modal_invoice_option_three, #modal_invoice_omit')
                        .prop('checked', false);
                    $('#modal_invoice_partially_fields, #modal_invoice_extra_work_fields, #modal_invoice_log_time_fields, #modal_invoice_omit_reason')
                        .hide();
                    $('#modal_invoice_reason_partially, #modal_invoice_scope_partially, #modal_invoice_price_charged_one')
                        .val('');
                    $('#modal_invoice_scope_extra_work, #modal_invoice_price_charged_two').val('');
                    $('#modal_invoice_start_time, #modal_invoice_end_time').val('');
                    $('#modal_invoice_reason_omit').val('');

                    // Set client name and final price
                    $('#modal_invoice_client_name').text(clientName);
                    $('#modal_invoice_final_price').text(finalPrice ? '$' + parseFloat(finalPrice).toFixed(
                        2) : '$0.00');
                    $('#modal_invoice_service_date').val(formattedDate);

                    // Set completion status checkboxes
                    if (option === 'completed') {
                        $('#modal_invoice_completed').prop('checked', true);
                    } else if (option === 'omit') {
                        $('#modal_invoice_omit').prop('checked', true);
                        if (reason) {
                            $('#modal_invoice_omit_reason').show();
                            $('#modal_invoice_reason_omit').val(reason);
                        }
                    }

                    // Partially Completed
                    if (optionFive === 'partially') {
                        $('#modal_invoice_partially').prop('checked', true);
                        $('#modal_invoice_partially_fields').show();
                        if (reason) {
                            $('#modal_invoice_reason_partially').val(reason);
                        }
                        if (partialScope) {
                            $('#modal_invoice_scope_partially').val(partialScope);
                        }
                        if (priceOne) {
                            $('#modal_invoice_price_charged_one').val(priceOne);
                        }
                    }

                    // Set additional options for INVOICE
                    if (optionTwo === 'extraWork') {
                        $('#modal_invoice_option_two').prop('checked', true);
                        $('#modal_invoice_extra_work_fields').show();
                        if (scope) {
                            $('#modal_invoice_scope_extra_work').val(scope);
                        }
                        if (priceTwo) {
                            $('#modal_invoice_price_charged_two').val(priceTwo);
                        }
                    }
                    if (optionThree === 'logTime') {
                        $('#modal_invoice_option_three').prop('checked', true);
                        $('#modal_invoice_log_time_fields').show();
                        if (startTime) {
                            $('#modal_invoice_start_time').val(startTime);
                        }
                        if (endTime) {
                            $('#modal_invoice_end_time').val(endTime);
                        }
                    }

                    // Show INVOICE modal
                    $('#viewReportModalInvoice').modal('show');
                }
            });
        });
    </script>
@endpush
