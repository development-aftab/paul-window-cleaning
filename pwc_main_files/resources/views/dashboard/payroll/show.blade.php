@extends('theme.layout.master')

@section('navbar-title')
    <div class="custom_justify_between create_clients_navbar">
        <a href="{{ route('payroll.index') }}" class="back_btn_navbar">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h2 class="navbar_PageTitle">Payroll For {{ $staff->name }}</h2>
    </div>
@endsection

@section('content')
<section class="create_clients_sec">
    <div class="container-fluid custom_container">

        <div class="row mb-4 custom_justify_between align-items-center">
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
                <div class="shadow_box_wrapper p-4">
                    <div class="table-responsive">
                        <table class="table align-middle gs-0 gy-4 myTable">
                            <thead>
                                <tr class="fw-bold">
                                    <th class="ps-4 min-w-150px rounded-start">Route</th>
                                    <th class="min-w-150px">Period</th>
                                    <th class="min-w-100px text-end">Gross Sales</th>
                                    <th class="min-w-100px text-end">Commission</th>
                                    <th class="min-w-150px text-end">Bonus</th>
                                    <th class="min-w-150px text-end">Hourly Pay</th>
                                    <th class="min-w-100px text-end rounded-end">Total Gross Pay</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($routePayrollData as $routeData)
                                <tr>
                                    <td class="ps-4">
                                        <span class="text-dark fw-bold d-block fs-6">{{ $routeData['route_name'] }}</span>
                                    </td>
                                    <td>
                                        <span class="text-muted fw-semibold d-block fs-7">
                                            {{ $routeData['start']->format('M j') }} – {{ $routeData['end']->format('j, Y') }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-muted fw-semibold text-muted d-block fs-7">${{ number_format($routeData['gross_sales'], 2) }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-muted fw-semibold text-muted d-block fs-7">${{ number_format($routeData['commission'], 2) }}</span>
                                    </td>
                                    <td class="text-end">
                                        @if(auth()->user()->hasRole('admin'))
                                        <form action="{{ route('payroll.bonus.save', $staff->id) }}" method="POST" class="d-flex align-items-center justify-content-end">
                                            @csrf
                                            <input type="hidden" name="route_id" value="{{ $routeData['route_id'] }}">
                                            <input type="hidden" name="half" value="{{ $half }}">
                                            <input type="hidden" name="month" value="{{ $monthName }}">
                                            <input type="hidden" name="year" value="{{ $year }}">
                                            <div class="input-group input-group-sm" style="width: 130px;">
                                                <span class="input-group-text">$</span>
                                                <input type="number" step="0.01" name="amount" class="form-control text-end" value="{{ $routeData['bonus'] }}">
                                                <button type="submit" class="btn btn-sm btn-success px-2" title="Save Bonus"><i class="fa-solid fa-check text-white m-0"></i></button>
                                            </div>
                                        </form>
                                        @else
                                        <span class="text-muted fw-semibold d-block fs-7">${{ number_format($routeData['bonus'], 2) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if(auth()->user()->hasRole('admin'))
                                        <button type="button"
                                            class="btn btn-sm btn-outline-primary addExtraHoursBtn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#extraHoursModal"
                                            data-route-id="{{ $routeData['route_id'] }}"
                                            data-route-name="{{ $routeData['route_name'] }}"
                                            data-per-hour-amount="{{ $routeData['extra_hours_admin_per_hour'] }}"
                                            data-total-extra-hours="{{ $routeData['extra_hours_admin_hours'] }}">
                                            ${{ number_format($routeData['extra_hours_admin_amount'], 2) }}
                                            <i class="fa-solid fa-pen ms-1"></i>
                                        </button>
                                        @else
                                        <span class="text-muted fw-semibold d-block fs-7">${{ number_format($routeData['extra_hours_admin_amount'], 2) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <span class="text-dark fw-bold d-block fs-7">${{ number_format($routeData['total_gross_pay'], 2) }}</span>
                                    </td>
                                </tr>
                                @empty
                                @endforelse
                            </tbody>
                            @if($routePayrollData->isNotEmpty())
                            <tfoot>
                                <tr>
                                    <td colspan="6" class="ps-4 text-end fw-bold">Grand Total Payroll</td>
                                    <td class="text-end pe-4 fw-bold">${{ number_format($grandTotalPay, 2) }}</td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

@if(auth()->user()->hasRole('admin'))
<div class="modal fade" id="extraHoursModal" tabindex="-1" aria-labelledby="extraHoursModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('payroll.extra-hours.save', $staff->id) }}" method="POST" id="extraHoursForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="extraHoursModalLabel">Add Extra Hours <span class="extraHoursRouteName text-muted"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="route_id" id="extraHoursRouteId">
                    <input type="hidden" name="period_start" value="{{ $start_date->format('Y-m-d') }}">
                    <input type="hidden" name="period_end" value="{{ $end_date->format('Y-m-d') }}">

                    <div class="mb-3">
                        <label for="extraHoursPerHourAmount" class="form-label">Per Hour Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" min="0" class="form-control" name="per_hour_amount" id="extraHoursPerHourAmount" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="extraHoursTotalHours" class="form-label">Total Extra Hours</label>
                        <input type="number" step="0.01" min="0" class="form-control" name="total_extra_hours" id="extraHoursTotalHours" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var extraHoursModal = document.getElementById('extraHoursModal');
        if (!extraHoursModal) return;

        extraHoursModal.addEventListener('show.bs.modal', function (event) {
            var btn = event.relatedTarget;

            document.getElementById('extraHoursRouteId').value = btn.getAttribute('data-route-id');
            document.getElementById('extraHoursPerHourAmount').value = btn.getAttribute('data-per-hour-amount');
            document.getElementById('extraHoursTotalHours').value = btn.getAttribute('data-total-extra-hours');
            extraHoursModal.querySelector('.extraHoursRouteName').textContent = '— ' + btn.getAttribute('data-route-name');
        });
    });
</script>
@endpush
@endif
@endsection
