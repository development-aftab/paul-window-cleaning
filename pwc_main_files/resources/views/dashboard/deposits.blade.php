@extends('theme.layout.master')

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet" />
    <style>
        html, body {
            color-scheme: light only;
        }

        .route_report_filters_wrapper {
            display: flex;
            justify-content: flex-start;
            align-items: flex-end;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .route_report_filters_wrapper .txt_field {
            min-width: 180px;
        }

        .route_report_filters_wrapper .txt_field label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #32346A;
            font-size: 14px;
        }

        .route_report_filters_wrapper .form-select,
        .route_report_filters_wrapper .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            color: #32346A;
            background-color: #fff;
        }

        .route_report_filters_wrapper .form-select:focus,
        .route_report_filters_wrapper .form-control:focus {
            border-color: #00ADEE;
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 173, 238, 0.1);
        }

        /* Staff section rows — same visual language as the route report's week grouping */
        .staff-header-row {
            background-color: #f0f4f8 !important;
            border-top: 4px solid #32346A !important;
            border-bottom: 2px solid #32346A !important;
        }

        .staff-header-row h3 {
            color: #32346A !important;
            margin: 0;
            font-size: 1.2rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .staff-header-row .staff-total {
            font-size: 14px;
            font-weight: 600;
            color: #5a6a7a;
            text-transform: none;
            letter-spacing: normal;
        }

        .staff-columns-row th {
            text-align: left;
            padding: 12px 12px 10px;
            font-size: 13px;
            font-weight: 700;
            color: #32346A;
            text-decoration: underline;
            text-underline-offset: 4px;
            white-space: nowrap;
        }

        .staff-spacer-row {
            height: 30px !important;
            background-color: #fff !important;
            border: none !important;
        }

        .staff-spacer-row td {
            border: none !important;
            padding: 0 !important;
            background-color: #fff !important;
        }

        .deposit-date-input {
            max-width: 170px;
        }

        .deposit-date-input.udc-saving {
            opacity: 0.6;
            pointer-events: none;
        }

        .udc-grand-total {
            margin-top: 20px;
            padding: 15px 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
            text-align: end;
        }

        .sortable-header {
            cursor: pointer;
            position: relative;
            padding-right: 20px !important;
        }

        .sortable-header::after {
            content: '\2195'; /* Up/down arrow */
            position: absolute;
            right: 5px;
            opacity: 0.3;
        }

        .sortable-header.asc::after {
            content: '\2191'; /* Up arrow */
            opacity: 1;
        }

        .sortable-header.desc::after {
            content: '\2193'; /* Down arrow */
            opacity: 1;
        }

        .staff-data-row.deposited-row td {
            background: linear-gradient(90deg, rgba(232, 245, 233, 0.7) 0%, rgba(241, 248, 233, 0.7) 100%) !important;
            border-bottom: 1px solid #C8E6C9 !important;
            color: #2E7D32 !important;
        }
        
        .staff-data-row.deposited-row td:first-child {
            border-left: 3px solid #4CAF50 !important;
        }
        
        .staff-data-row.deposited-row input.deposit-date-input,
        .staff-data-row.deposited-row input.deposit-date-input:disabled {
            background-color: rgba(255, 255, 255, 0.6);
            border-color: #A5D6A7;
            color: #1B5E20;
            cursor: not-allowed;
            opacity: 1;
        }
    </style>
@endpush

@section('navbar-title')
    <div class="custom_justify_between">
        <h2 class="navbar_PageTitle">Total Undeposited Cash</h2>
    </div>
@endsection

@section('content')
    <section class="client_management staff_manag route_report_section">
        <div class="container-fluid custom_container">
            <div class="row">
                <div class="col-md-12">
                    <div class="custom_div">

                        {{-- Filters --}}
                        <form method="GET" action="{{ route('deposits.index') }}" class="route_report_filters_wrapper">
                            @if ($isAdmin)
                                {{-- Staff filter is admin-only — staff role only ever sees their own records --}}
                                <div class="txt_field custom_select_route">
                                    <label for="filter_staff">Staff</label>
                                    <select name="staff_id" id="filter_staff" class="form-select selectRoute" data-placeholder="All Staff">
                                        <option value="">All Staff</option>
                                        @foreach ($staffs ?? [] as $staff)
                                            <option value="{{ $staff->id }}" {{ request('staff_id') == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="txt_field custom_select_route">
                                <label for="filter_route">Route</label>
                                <select name="route_id" id="filter_route" class="form-select selectRoute" data-placeholder="All Routes">
                                    <option value="">All Routes</option>
                                    @foreach ($routes ?? [] as $route)
                                        <option value="{{ $route->id }}" {{ request('route_id') == $route->id ? 'selected' : '' }}>{{ $route->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="txt_field">
                                <label for="filter_week">Week</label>
                                <select name="week" id="filter_week" class="form-select">
                                    <option value="">All Weeks</option>
                                    @foreach (['week0' => 'Week 1', 'week1' => 'Week 2', 'week2' => 'Week 3', 'week3' => 'Week 4'] as $value => $label)
                                        <option value="{{ $value }}" {{ request('week') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="txt_field">
                                <label for="filter_date_range">Date Range</label>
                                <input class="form-control" type="text" id="filter_date_range" placeholder="Select date range" autocomplete="off">
                                <input type="hidden" name="date_from" id="filter_date_from" value="{{ request('date_from') }}">
                                <input type="hidden" name="date_to" id="filter_date_to" value="{{ request('date_to') }}">
                            </div>
                            <button type="submit" class="btn_global btn_dark_blue">Filter</button>
                            @if (request()->hasAny(['staff_id', 'route_id', 'week', 'date_from', 'date_to']))
                                <a href="{{ route('deposits.index') }}" class="btn_global btn_dark_blue" style="background:#fff;color:#32346A;border:1px solid #ddd;">Clear</a>
                            @endif
                        </form>

                        @if ($sections->isNotEmpty())
                            <div class="d-flex justify-content-end mb-3 align-items-center gap-2">
                                <span style="font-weight: 600; color: #32346A; font-size: 14px;">Sort Staff Groups:</span>
                                <button type="button" class="btn btn-sm btn-outline-secondary btn-sort-staff" data-sort="name">Name ↕</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary btn-sort-staff" data-sort="total">Total Amount ↕</button>
                            </div>
                        @endif

                        <div class="custom_table">
                            <div class="table-responsive">
                                <table class="table staff-groups-table">
                                        @forelse ($sections as $section)
                                            <tbody class="staff-group-tbody" data-staff-name="{{ $section['staff_name'] }}" data-staff-total="{{ $section['total'] }}">
                                            @if (!$loop->first)
                                                <tr class="staff-spacer-row">
                                                    <td colspan="5"></td>
                                                </tr>
                                            @endif

                                            <tr class="staff-header-row">
                                                <td colspan="5" style="text-align: left; padding-left: 20px;">
                                                    <h3>
                                                        {{ $section['staff_name'] }}
                                                        <span class="staff-total">${{ number_format($section['total'], 2) }}</span>
                                                    </h3>
                                                </td>
                                            </tr>

                                            <tr class="staff-columns-row">
                                                <th class="sortable-header" data-sort="date" data-col-index="0">Date</th>
                                                <th class="sortable-header" data-sort="string" data-col-index="1">Route</th>
                                                <th class="sortable-header" data-sort="number" data-col-index="2">Week</th>
                                                <th class="sortable-header" data-sort="number" data-col-index="3">Amount</th>
                                                <th>Date Deposited</th>
                                            </tr>

                                            @foreach ($section['rows'] as $row)
                                                <tr class="staff-data-row {{ !empty($row['is_deposit']) ? 'deposited-row' : '' }}" data-sort-date="{{ $row['sort_date'] ? \Carbon\Carbon::parse($row['sort_date'])->format('Y-m-d') : '' }}">
                                                    <td>{{ $row['date_label'] }}</td>
                                                    <td>{{ $row['route_name'] }}</td>
                                                    <td>{{ $row['week_number'] }}</td>
                                                    <td>${{ number_format($row['amount'], 2) }}</td>
                                                    <td style="display: flex; justify-content: center">
                                                        @if($isAdmin)
                                                            <span style="font-weight: 500; color: #2E7D32; margin-top: 6px;">{{ $row['deposit_date'] ? \Carbon\Carbon::parse($row['deposit_date'])->format('m/d/Y') : '-' }}</span>
                                                        @else
                                                            @php
                                                                $depositDateValue = $row['deposit_date']
                                                                    ? \Carbon\Carbon::parse($row['deposit_date'])->format('Y-m-d')
                                                                    : '';
                                                            @endphp
                                                            <input
                                                                type="date"
                                                                class="form-control deposit-date-input"
                                                                value="{{ $depositDateValue }}"
                                                                data-deposit-id="{{ $row['deposit_id'] }}"
                                                                data-payment-ids="{{ $row['payment_ids'] ? implode(',', $row['payment_ids']) : '' }}"
                                                                @if(!empty($row['is_deposit'])) disabled @endif
                                                            >
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        @empty
                                            <tbody>
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted">All caught up! No undeposited cash to show right now.</td>
                                                </tr>
                                            </tbody>
                                        @endforelse
                                </table>
                            </div>
                        </div>

                        @if ($sections->isNotEmpty())
                            <div class="udc-grand-total">
                                <strong>Total Undeposited: ${{ number_format($grandTotal, 2) }}</strong>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        $(document).ready(function() {
            $(".selectRoute").select2({
                allowClear: true,
                placeholder: 'All Routes'
            });

            var initialDateFrom = @json(request('date_from'));
            var initialDateTo = @json(request('date_to'));

            flatpickr("#filter_date_range", {
                mode: "range",
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "m-d-Y",
                defaultDate: (initialDateFrom && initialDateTo) ? [initialDateFrom, initialDateTo] : null,
                onChange: function(selectedDates) {
                    var toStr = function(d) {
                        return d.getFullYear() + '-' +
                            ('0' + (d.getMonth() + 1)).slice(-2) + '-' +
                            ('0' + d.getDate()).slice(-2);
                    };

                    if (selectedDates.length === 2) {
                        $('#filter_date_from').val(toStr(selectedDates[0]));
                        $('#filter_date_to').val(toStr(selectedDates[1]));
                    } else {
                        $('#filter_date_from').val('');
                        $('#filter_date_to').val('');
                    }
                }
            });

            // Inline-editable "Date Deposited" — confirm, then save on select, reusing the same
            // endpoints as "Mark All as Deposited" on the route detail page and the admin
            // deposited checkbox.
            $(document).on('change', '.deposit-date-input', function() {
                const $input = $(this);
                const depositDate = $input.val();

                if (!depositDate) {
                    return;
                }

                const depositId = $input.data('deposit-id');
                const paymentIdsRaw = $input.data('payment-ids');
                const $row = $input.closest('tr');
                const route = $row.find('td').eq(1).text().trim();
                const amount = $row.find('td').eq(3).text().trim();
                const formattedDate = new Date(depositDate + 'T00:00:00').toLocaleDateString('en-US', {
                    year: 'numeric', month: 'long', day: 'numeric'
                });

                Swal.fire({
                    title: 'Confirm Deposit Date',
                    html: `Mark <strong>${amount}</strong> for <strong>${route}</strong> as deposited on <strong>${formattedDate}</strong>?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, save it',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#00ADEE',
                    cancelButtonColor: '#6c757d',
                    reverseButtons: false,
                }).then(function(result) {
                    if (!result.isConfirmed) {
                        $input.val('');
                        return;
                    }

                    let url, payload;

                    if (depositId) {
                        // Row already backed by a Deposit record — update it in place.
                        url = '{{ route('deposits.update-status', ':id') }}'.replace(':id', depositId);
                        payload = {
                            _token: '{{ csrf_token() }}',
                            is_deposit: 1,
                            deposit_date: depositDate,
                        };
                    } else if (paymentIdsRaw) {
                        // Row still backed by raw cash payments — convert them into Deposit record(s) now.
                        url = '{{ route('deposits.mark-deposited') }}';
                        payload = {
                            _token: '{{ csrf_token() }}',
                            payment_ids: String(paymentIdsRaw).split(','),
                            deposit_date: depositDate,
                        };
                    } else {
                        return;
                    }

                    $input.addClass('udc-saving');

                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: payload,
                        success: function(response) {
                            Swal.fire({
                                title: 'Saved!',
                                text: response.message || 'Deposit date saved successfully.',
                                icon: 'success',
                                timer: 1800,
                                showConfirmButton: false
                            }).then(function() {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            $input.removeClass('udc-saving');
                            $input.val('');
                            const message = xhr.responseJSON?.message || 'Failed to save deposit date.';
                            Swal.fire({
                                title: 'Error',
                                text: message,
                                icon: 'error',
                                confirmButtonColor: '#00ADEE',
                            });
                        }
                    });
                });
            });

            // Frontend sorting within each staff section
            $(document).on('click', '.sortable-header', function() {
                const $th = $(this);
                const colIndex = $th.data('col-index');
                const sortType = $th.data('sort');
                const isAsc = $th.hasClass('asc');

                // Toggle sort direction
                $th.closest('tr').find('th').removeClass('asc desc');
                $th.addClass(isAsc ? 'desc' : 'asc');
                const sortDir = isAsc ? -1 : 1;

                // Find the data rows belonging to this section
                const $headerRow = $th.closest('tr');
                let $currentTr = $headerRow.next();
                const rows = [];

                while ($currentTr.length && $currentTr.hasClass('staff-data-row')) {
                    rows.push($currentTr);
                    $currentTr = $currentTr.next();
                }

                // Sort the rows
                rows.sort((a, b) => {
                    let valA, valB;

                    if (sortType === 'date') {
                        valA = a.data('sort-date') || '';
                        valB = b.data('sort-date') || '';
                    } else {
                        valA = a.find('td').eq(colIndex).text().trim();
                        valB = b.find('td').eq(colIndex).text().trim();
                    }

                    if (sortType === 'number') {
                        // Extract numbers (remove $ and commas, handle week numbers)
                        valA = parseFloat(valA.replace(/[^0-9.-]+/g, "")) || 0;
                        valB = parseFloat(valB.replace(/[^0-9.-]+/g, "")) || 0;
                        return (valA - valB) * sortDir;
                    }

                    // String or date sort
                    return valA.localeCompare(valB) * sortDir;
                });

                // Proper insertion in place:
                // We need to re-insert them after the header row in the new order
                let $insertAfter = $headerRow;
                $.each(rows, function(index, $row) {
                    $row.insertAfter($insertAfter);
                    $insertAfter = $row;
                });
            });

            // Global Staff Group Sorting
            $('.btn-sort-staff').on('click', function() {
                const $btn = $(this);
                const sortType = $btn.data('sort');
                const isAsc = $btn.hasClass('asc');
                
                // Reset both buttons
                $('.btn-sort-staff').removeClass('asc desc btn-primary').addClass('btn-outline-secondary')
                    .each(function() {
                        $(this).text($(this).text().replace(/↑|↓/, '↕'));
                    });
                
                // Toggle current button
                $btn.removeClass('btn-outline-secondary').addClass('btn-primary');
                $btn.addClass(isAsc ? 'desc' : 'asc');
                const sortDir = isAsc ? -1 : 1;
                
                // Update button text icon
                const baseText = sortType === 'name' ? 'Name ' : 'Total Amount ';
                $btn.text(baseText + (isAsc ? '↓' : '↑'));

                const tbodies = $('.staff-groups-table .staff-group-tbody').get();
                
                tbodies.sort(function(a, b) {
                    const $a = $(a);
                    const $b = $(b);
                    
                    if (sortType === 'name') {
                        const nameA = $a.data('staff-name').toLowerCase();
                        const nameB = $b.data('staff-name').toLowerCase();
                        return nameA.localeCompare(nameB) * sortDir;
                    } else {
                        const totalA = parseFloat($a.data('staff-total')) || 0;
                        const totalB = parseFloat($b.data('staff-total')) || 0;
                        return (totalA - totalB) * sortDir;
                    }
                });
                
                // Re-append sorted tbodies and fix spacer rows
                const $table = $('.staff-groups-table');
                $.each(tbodies, function(index, tbody) {
                    const $tbody = $(tbody);
                    
                    // The spacer row logic: only non-first groups should have the spacer visible.
                    // We can just find the .staff-spacer-row inside this tbody.
                    let $spacer = $tbody.find('.staff-spacer-row');
                    
                    if (index === 0) {
                        $spacer.hide(); // Hide spacer for first group
                    } else {
                        if ($spacer.length === 0) {
                            // If it doesn't have a spacer (was originally first), add it
                            $tbody.prepend('<tr class="staff-spacer-row"><td colspan="5"></td></tr>');
                        } else {
                            $spacer.show();
                        }
                    }
                    
                    $table.append($tbody);
                });
            });
        });
    </script>
@endpush
