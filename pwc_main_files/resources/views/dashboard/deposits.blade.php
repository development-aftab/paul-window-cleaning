@extends('theme.layout.master')

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
                                <label for="filter_date">Date</label>
                                <input class="form-control" type="date" name="date" id="filter_date" value="{{ request('date') }}">
                            </div>
                            <button type="submit" class="btn_global btn_dark_blue">Filter</button>
                            @if (request()->hasAny(['staff_id', 'route_id', 'week', 'date']))
                                <a href="{{ route('deposits.index') }}" class="btn_global btn_dark_blue" style="background:#fff;color:#32346A;border:1px solid #ddd;">Clear</a>
                            @endif
                        </form>

                        <div class="custom_table">
                            <div class="table-responsive">
                                <table class="table">
                                    <tbody>
                                        @forelse ($sections as $section)
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
                                                <th>Date</th>
                                                <th>Route</th>
                                                <th>Week</th>
                                                <th>Amount</th>
                                                <th>Date Deposited</th>
                                            </tr>

                                            @foreach ($section['rows'] as $row)
                                                <tr>
                                                    <td>{{ $row['date_label'] }}</td>
                                                    <td>{{ $row['route_name'] }}</td>
                                                    <td>{{ $row['week_number'] }}</td>
                                                    <td>${{ number_format($row['amount'], 2) }}</td>
                                                    <td style="display: flex; justify-content: center">
                                                        <input
                                                            type="date"
                                                            class="form-control deposit-date-input"
                                                            value="{{ $row['deposit_date'] }}"
                                                            data-deposit-id="{{ $row['deposit_id'] }}"
                                                            data-payment-ids="{{ $row['payment_ids'] ? implode(',', $row['payment_ids']) : '' }}"
                                                        >
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">All caught up! No undeposited cash to show right now.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
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
    <script>
        $(document).ready(function() {
            $(".selectRoute").select2({
                allowClear: true,
                placeholder: 'All Routes'
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
        });
    </script>
@endpush
