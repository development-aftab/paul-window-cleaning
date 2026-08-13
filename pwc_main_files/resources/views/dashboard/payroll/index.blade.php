@extends('theme.layout.master')

@section('navbar-title')
    <div class="custom_justify_between create_clients_navbar">
        <h2 class="navbar_PageTitle">Payroll</h2>
    </div>
@endsection

@section('content')
<section class="create_clients_sec">
    <div class="container-fluid custom_container">

        <div class="row mb-5">
            <div class="col-md-12">
                <div class="months-pagination filter_download_dropdown_wrapper" style="display: flex; align-items: center; gap: 10px;">
                    <a href="{{ request()->fullUrlWithQuery(['period' => $previousPeriod]) }}" type="button" class="btn btn-sm btn-outline-secondary prevMonthBtn">
                        <i class="fas fa-arrow-left"></i>
                    </a>

                    <div class="dropdown dropdown_months_wrapper">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-regular fa-calendar"></i>
                            <span class="selected_month_text">{{ $selectedPeriodLabel }}</span>
                        </button>
                        <ul class="dropdown-menu">
                            @foreach ($periods as $period)
                                <li>
                                    <a class="dropdown-item {{ $period['value'] === $selectedPeriod ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['period' => $period['value']]) }}">
                                        {{ $period['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <a href="{{ request()->fullUrlWithQuery(['period' => $nextPeriod]) }}" class="btn btn-sm btn-outline-secondary nextMonthBtn" type="button">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="shadow_box_wrapper">
                    <div class="table-responsive">
                        <table class="table align-middle gs-0 gy-4 myTable">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-4 min-w-150px rounded-start">Staff Name</th>
                                    <th class="min-w-100px">Total Gross Sales</th>
                                    <th class="min-w-100px">Commission</th>
                                    <th class="min-w-100px">Bonus</th>
                                    <th class="min-w-100px">Hourly Pay</th>
                                    <th class="min-w-100px text-end rounded-end">Total Gross Pay</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($staffData as $staff)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="d-flex justify-content-start flex-column">
                                                <a href="{{ route('payroll.show', $staff->id) }}?period={{ urlencode($selectedPeriod) }}" class="text-dark fw-bold text-hover-primary mb-1 fs-6">
                                                    {{ $staff->name }}
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted fw-semibold text-muted d-block fs-7">${{ number_format($staff->gross_sales, 2) }}</span>
                                    </td>
                                    <td>
                                        <span class="text-muted fw-semibold text-muted d-block fs-7">${{ number_format($staff->commission, 2) }}</span>
                                    </td>
                                    <td>
                                        <span class="text-muted fw-semibold text-muted d-block fs-7">${{ number_format($staff->bonus, 2) }}</span>
                                    </td>
                                    <td>
                                        <span class="text-muted fw-semibold text-muted d-block fs-7">${{ number_format($staff->extra_hours_admin_amount, 2) }}</span>
                                    </td>
                                    <td>
                                        <span class="text-dark fw-bold d-block fs-7 text-end">${{ number_format($staff->total_gross, 2) }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('payroll.show', $staff->id) }}?period={{ urlencode($selectedPeriod) }}" class="btn btn-sm btn-primary">View Details</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
