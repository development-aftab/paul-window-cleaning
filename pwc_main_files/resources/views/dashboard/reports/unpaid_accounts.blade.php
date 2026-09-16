@extends('theme.layout.master')

@push('css')
<style>
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
        color: #00ADEE;
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

    .payment-date-input {
        min-width: 150px;
    }

    .mark-paid-btn {
        white-space: nowrap;
    }

    .pay-zelle-btn {
        white-space: nowrap;
    }
</style>
@endpush

@section('navbar-title')
    <div class="custom_justify_between">
        <h2 class="navbar_PageTitle">Unpaid Accounts</h2>
    </div>
@endsection

@section('content')
<section class="create_clients_sec">
    <div class="container-fluid custom_container">

{{--        <div class="row mb-5">--}}
{{--            <div class="col-md-12">--}}
{{--                <div class="months-pagination filter_download_dropdown_wrapper" style="display: flex; align-items: center; gap: 10px;">--}}
{{--                    <a href="{{ request()->fullUrlWithQuery(['month' => $previousMonth]) }}" class="btn btn-sm btn-outline-secondary prevMonthBtn">--}}
{{--                        <i class="fas fa-arrow-left"></i>--}}
{{--                    </a>--}}

{{--                    <div class="dropdown dropdown_months_wrapper">--}}
{{--                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">--}}
{{--                            <i class="fa-regular fa-calendar"></i>--}}
{{--                            <span class="selected_month_text">{{ $selectedMonth }}</span>--}}
{{--                        </button>--}}
{{--                        <ul class="dropdown-menu">--}}
{{--                            @foreach ($months as $month)--}}
{{--                                <li>--}}
{{--                                    <a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['month' => $month]) }}">--}}
{{--                                        {{ $month }}--}}
{{--                                    </a>--}}
{{--                                </li>--}}
{{--                            @endforeach--}}
{{--                        </ul>--}}
{{--                    </div>--}}

{{--                    <a href="{{ request()->fullUrlWithQuery(['month' => $nextMonth]) }}" class="btn btn-sm btn-outline-secondary nextMonthBtn">--}}
{{--                        <i class="fas fa-arrow-right"></i>--}}
{{--                    </a>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}

        <div class="row">
            <div class="col-md-12">
                @forelse($groupedData as $staffName => $schedules)
                    <div class="card card-flush shadow-sm mb-5">
                        <div class="card-header pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bolder fs-3 mb-1">{{ $staffName }}</span>
                            </h3>
                        </div>
                        <div class="card-body py-3">
                            <div class="table-responsive">
                                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                    <thead>
                                        <tr class="fw-bolder text-muted">
                                            <th class="min-w-150px">Client</th>
                                            <th class="min-w-100px text-end">Amount</th>
{{--                                            <th class="min-w-150px">Schedule Date</th>--}}
                                            <th class="min-w-150px">Date Serviced</th>
                                            <th class="min-w-150px">Route</th>
                                            @if(auth()->user()->hasRole('staff'))
                                                <th class="min-w-175px">Payment Date</th>
                                                <th class="min-w-100px text-end">Action</th>
                                            @elseif(auth()->user()->hasRole('admin'))
                                                <th class="min-w-150px text-end">Action</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($schedules as $schedule)
                                        <tr>
                                            <td>
                                                <span class="text-dark fw-bold d-block fs-6">{{ $schedule->clientName->name ?? 'N/A' }}</span>
                                            </td>
                                            <td class="text-end">
                                                <span class="text-dark fw-bold d-block fs-6">${{ number_format(optional($schedule->clientSchedulePayment)->final_price ?? 0, 2) }}</span>
                                            </td>
{{--                                            <td>--}}
{{--                                                <span class="text-dark fw-bold d-block fs-6">{{ \Carbon\Carbon::parse($schedule->start_date)->format('m/d/y') }}</span>--}}
{{--                                            </td>--}}
                                            <td>
                                                <span class="text-dark fw-bold d-block fs-6">{{ \Carbon\Carbon::parse($schedule->service_date)->format('m-d-Y') }}</span>
                                            </td>
                                            <td>
                                                <span class="text-dark fw-bold d-block fs-6">{{ $schedule->route_name }}</span>
                                            </td>
                                            @if(auth()->user()->hasRole('staff'))
                                                <td>
                                                    @if(optional($schedule->clientSchedulePayment)->id)
                                                        <input
                                                            type="date"
                                                            class="form-control form-control-sm payment-date-input"
                                                            value="{{ now()->format('Y-m-d') }}"
                                                            data-payment-id="{{ $schedule->clientSchedulePayment->id }}"
                                                        >
                                                    @else
                                                        <span class="text-muted fs-7">No payment record</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    @if(optional($schedule->clientSchedulePayment)->id)
                                                        <button type="button" class="btn btn-sm btn-primary mark-paid-btn"  data-payment-id="{{ $schedule->clientSchedulePayment->id }}" >
                                                            Save
                                                        </button>
                                                    @endif
                                                </td>
                                            @elseif(auth()->user()->hasRole('admin'))
                                                <td class="text-end">
                                                    @if(optional($schedule->clientSchedulePayment)->id)
                                                        <button type="button" class="btn btn-sm btn-primary pay-zelle-btn" data-payment-id="{{ $schedule->clientSchedulePayment->id }}" data-client-name="{{ $schedule->clientName->name ?? 'this client' }}" data-amount="{{ number_format(optional($schedule->clientSchedulePayment)->final_price ?? 0, 2) }}">
                                                            <i class="fa-solid fa-money-bill-transfer"></i> Pay with Zelle
                                                        </button>
                                                    @else
                                                        <span class="text-muted fs-7">No payment record</span>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                        @endforeach
                                        <tr class="bg-light">
                                            <td class="fw-bolder">Total</td>
                                            <td class="text-end fw-bolder fs-5 text-danger">
                                                ${{ number_format($schedules->sum(fn($s) => optional($s->clientSchedulePayment)->final_price ?? 0), 2) }}
                                            </td>
                                            <td colspan="{{ auth()->user()->hasRole('staff') ? 4 : (auth()->user()->hasRole('admin') ? 3 : 2) }}"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="card card-flush shadow-sm">
                        <div class="card-body text-center p-15">
                            <i class="fa-solid fa-check-circle fa-4x text-success mb-4"></i>
                            <h3 class="fs-4 fw-bolder text-dark">No unpaid accounts found.</h3>
                            <p class="text-muted fs-6">All schedules for the selected period are fully paid!</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        $(document).on('click', '.pay-zelle-btn', function() {
            const button = $(this);
            const paymentId = button.data('payment-id');

            Swal.fire({
                title: 'Pay with Zelle?',
                text: 'Mark ' + button.data('client-name') + ' ($' + button.data('amount') + ') as paid via Zelle?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, mark as paid',
                cancelButtonText: 'Cancel',
            }).then(function(result) {
                if (!result.isConfirmed) {
                    return;
                }

                button.prop('disabled', true);

                $.ajax({
                    url: '{{ route('reports.unpaid.pay-zelle') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        payment_id: paymentId,
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Success!',
                            text: response.message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false,
                        }).then(function() {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || 'Failed to update payment. Please try again.';
                        Swal.fire({
                            title: 'Error!',
                            text: message,
                            icon: 'error',
                        });
                        button.prop('disabled', false);
                    },
                });
            });
        });

        $(document).on('click', '.mark-paid-btn', function() {
            const button = $(this);
            const paymentId = button.data('payment-id');
            const row = button.closest('tr');
            const dateInput = row.find('.payment-date-input[data-payment-id="' + paymentId + '"]');
            const paymentDate = dateInput.val();

            if (!paymentDate) {
                Swal.fire({
                    title: 'Payment date required',
                    text: 'Please select the date you received payment.',
                    icon: 'warning',
                });
                return;
            }

            button.prop('disabled', true);

            $.ajax({
                url: '{{ route('reports.unpaid.mark-paid') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    payment_id: paymentId,
                    payment_date: paymentDate,
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                    }).then(function() {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Failed to update payment. Please try again.';
                    Swal.fire({
                        title: 'Error!',
                        text: message,
                        icon: 'error',
                    });
                    button.prop('disabled', false);
                },
            });
        });
    });
</script>
@endpush
