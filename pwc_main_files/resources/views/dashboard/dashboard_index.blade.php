@extends('theme.layout.master')

@push('css')
    {{-- <link href="{{ asset('plugins/components/morrisjs/morris.css') }}" rel="stylesheet"> --}}
    <style>
        .staff_routes_group {
            display: flex;
            flex-direction: column;
            gap: 10px;
            background: #fff;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 15px;
        }

        .staff_routes_group_name {
            color: var(--dark_blue);
            font-family: 'Hellix-Bold';
            font-size: 16px;
        }

        .staff_routes_group_scroll {
            display: flex;
            flex-wrap: nowrap;
            gap: 12px;
            overflow-x: auto;
            width: 100%;
            min-width: 0;
            padding-bottom: 8px;
        }

        .new_yorks-cards_wrapper_compact {
            flex: 0 0 150px;
            width: 150px;
            padding: 6px;
            border-radius: 12px;
            row-gap: 4px;
        }

        .new_yorks-cards_wrapper_compact div h2 {
            font-size: 14px;
        }

        .new_yorks-cards_wrapper_compact div:has(.jobs_icon_wrapper) {
            padding: 8px;
            row-gap: 8px;
        }

        .new_yorks-cards_wrapper_compact .jobs_icon_wrapper div label,
        .new_yorks-cards_wrapper_compact .jobs_icon_wrapper div span {
            font-size: 11px;
        }

        .new_yorks-cards_wrapper_compact .jobs_icon_wrapper div:has(img) {
            width: 24px;
            height: 24px;
        }

        .new_yorks-cards_wrapper_compact .jobs_icon_wrapper div:has(img) img {
            width: 12px;
        }
    </style>
@endpush
@section('navbar-title')
    <h2 class="navbar_PageTitle">
        Hello {{ Auth()->check() ? (auth()->user()->hasRole('admin') ? 'Paul' : Auth()->user()->name) : '' }}
    </h2>
@endsection
@section('content')
    @if (auth()->user()->hasRole('admin'))
        <section class="homePage_section">
            <div class="container-fluid custom_container">
                <div class="row custom_row">
                    <div class="col-md-8">
                        <div class="cards_dashboard_index_wrapper shadow_box_wrapper">
                            <div>
                                <h4>This is Week {{ $weekNumber ?? '' }} :</h4>
                                <h4>{{ $currentMonth ?? '' }} {{ $startOfWeek ?? '' }} - {{ $endOfWeek ?? '' }} ,  {{ $currentYear ?? '' }}</h4>
                            </div>

                            <div>
                                @forelse($staffRoute->groupBy('staff_name') as $staffName => $routesForStaff)
                                    <div class="staff_routes_group">
                                        <div class="staff_routes_group_name">{{ $staffName }}</div>
                                        <div class="staff_routes_group_scroll">
                                            @foreach ($routesForStaff as $route)
                                                <div class="new_yorks-cards_wrapper new_yorks-cards_wrapper_compact">
                                                    <div>
                                                        <h2>{{ $route->name ?? '' }}</h2>
                                                        <div class="jobs_icon_wrapper">
                                                            <div>
                                                                <div>
                                                                    <label>Scheduled:</label>
                                                                    <span>{{ $route->jobs_total ?? 0 }}</span>
                                                                </div>
                                                                <div>
                                                                    <label>Completed:</label>
                                                                    <span>{{ $route->jobs_completed ?? 0 }}</span>
                                                                </div>
                                                                <div>
                                                                    <label>Pending</label>
                                                                    <span>{{ $route->jobs_pending ?? 0 }}</span>
                                                                </div>
                                                            </div>
                                                            <a href="{{ route('staffroutes.show', [$route->id]) }}">
                                                                <div>
                                                                    <img src="{{ asset('website') }}/assets/images/Arrow-up-right_white.svg">
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    @php
                                                            $totalJobs = ($route->jobs_pending ?? 0) + ($route->jobs_completed ?? 0);
                                                            $completedPercentage = $totalJobs > 0 ? round(($route->jobs_completed / $totalJobs) * 100) : 0;

                                                            // Determine color, icon and background based on percentage
                                                            if ($totalJobs === 0) {
                                                                $progressColor = '#9e9e9e';
                                                                $progressBg = '#eeeeee';
                                                                $progressIcon = 'fa-ban';
                                                                $progressText = 'Inactive';
                                                            } elseif ($completedPercentage <= 20) {
                                                                $progressColor = '#ff5500';
                                                                $progressBg = '#fbf2ec';
                                                                $progressIcon = 'fa-hourglass-start';
                                                                $progressText = 'Completed';
                                                            } elseif ($completedPercentage <= 50) {
                                                                $progressColor = '#ff9800';
                                                                $progressBg = '#FFF3E0';
                                                                $progressIcon = 'fa-spinner';
                                                                $progressText = 'In Progress';
                                                            } elseif ($completedPercentage <= 80) {
                                                                $progressColor = '#ff9800';
                                                                $progressBg = '#FFF3E0';
                                                                $progressIcon = 'fa-hourglass-half';
                                                                $progressText = 'Nearly';
                                                            } elseif ($completedPercentage < 100) {
                                                                $progressColor = '#ff9800';
                                                                $progressBg = '#FFF3E0';
                                                                $progressIcon = 'fa-check-circle';
                                                                $progressText = 'Almost Done';
                                                            } else {
                                                                $progressColor = '#4caf50';
                                                                $progressBg = '#b5fbd0';
                                                                $progressIcon = 'fa-check-circle';
                                                                $progressText = 'Completed';
                                                            }
                                                    @endphp
                                                    <div style="background: {{ $progressBg }}; border-radius: 8px; padding: 4px 5px;">
                                                        <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 3px;">
                                                            <i class="fa-solid {{ $progressIcon }}" style="color: {{ $progressColor }}; font-size: 11px;"></i>
                                                            <h5 style="color: {{ $progressColor }}; margin: 0; font-size: 11px;">{{ $totalJobs === 0 ? $progressText : $completedPercentage . '% ' . $progressText }}</h5>
                                                        </div>
                                                        <div style="width: 100%; background-color: #e0e0e0; border-radius: 10px; height: 6px; overflow: hidden; margin-bottom: 3px;">
                                                            <div style="width: {{ $totalJobs === 0 ? 100 : $completedPercentage }}%; background-color: {{ $progressColor }}; height: 100%; transition: width 0.3s ease, background-color 0.3s ease;">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @empty
                                    <div>There no Completed Routes Available.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
{{--                    <div class="col-md-8">--}}
{{--                        <div class="chart_wrapper_sec shadow_box_wrapper">--}}
{{--                            <div class="statistics_wrapper">--}}
{{--                                <h3>Gross Commercial Sales</h3>--}}
{{--                                <div class="date_range_picker_wrapper">--}}
{{--                                    <label class="form-label"><i class="fa-regular fa-calendar"></i></label>--}}
{{--                                    <input class="form-control form-control-solid" placeholder="Pick date rage" id="kt_daterangepicker_1" />--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="chart_wrapper">--}}
{{--                                <canvas id="line-chart"></canvas>--}}
{{--                                <svg style="display: none;">--}}
{{--                                    <defs>--}}
{{--                                        <linearGradient id="gradient1" x1="0%" y1="0%" x2="0%" y2="100%">--}}
{{--                                            <stop offset="20%" style="stop-color:#2280C2;stop-opacity:1" />--}}
{{--                                            <stop offset="0%" style="stop-color:#FFFFFF;stop-opacity:1" />--}}
{{--                                        </linearGradient>--}}
{{--                                    </defs>--}}
{{--                                </svg>--}}
{{--                                <svg style="display: none;">--}}
{{--                                    <defs>--}}
{{--                                        <linearGradient id="gradient2" x1="0%" y1="0%" x2="0%" y2="100%">--}}
{{--                                            <stop offset="10%" style="stop-color:#2010801A;stop-opacity:1" />--}}
{{--                                            <stop offset="0%" style="stop-color:#20108000;stop-opacity:1" />--}}
{{--                                        </linearGradient>--}}
{{--                                    </defs>--}}
{{--                                </svg>--}}
{{--                            </div>--}}

{{--                        </div>--}}
{{--                    </div>--}}
                    <div class="col-md-4">
                        <div class="notification_dashboard_wrapper shadow_box_wrapper">
                            <h3>Coming Soon</h3>
                            <div class="service_complete_wrapper">
                                <ul class="notification_ul-wrapper">
                                    @forelse($prioritySchedules as $schedule)
                                        <li>
                                            <div>
                                                <h5>
                                                    {{ optional($schedule->clientName)->clientRoute->first()->name ?? '' }}
                                                    -
                                                    {{ optional($schedule->clientName)->name ?? '' }}
                                                </h5>
                                            </div>
                                            <div>
                                                <p>{{ $schedule->note ?? '' }}</p>
                                                <span>{{ $schedule->start_date ?? '' }}</span>
                                            </div>
                                        </li>
                                    @empty
                                            <div>
                                                <h5>No Coming Schedules Found!</h5>
                                            </div>
                                    @endforelse
                                </ul>
                            </div>

                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="custom_div">
                            <div class="custom_justify_between">
                                <h3>Past Due Invoices</h3>
                            </div>
                            <div class="custom_table custom_table_dashboard">
                                <div class="">
                                    <table class="table myTable datatable">
                                        <thead>
                                        <tr>
                                            <th>Account Name</th>
                                            <th>Created Date</th>
                                            <th>Total Price</th>
                                            <th>Status</th>
                                            {{-- <th>View</th> --}}
                                        </tr>
                                        </thead>
                                        <tbody>
                                        {{--
                                                                                    @forelse ($last45DaysInvoices as $invoice)
                                                                                        <tr>
                                                                                            <td>{{ Str::limit($invoice->client->name ?? 'N/A', 15) }}</td>
                                                                                            <td>{{ $invoice->created_at->format('m-d-Y') }}</td>
                                                                                            <td>${{ $invoice->final_price }}</td>
                                                                                            <td>
                                                                                                @if ($invoice->payment_status == 'paid')
                                                                                                    <span class="badge badge-paid">Paid</span>
                                                                                                @else
                                                                                                    <span class="badge badge-unpaid">Unpaid</span>
                                                                                                @endif
                                                                                            </td>
                                                                                        </tr>
                                                                                    @empty
                                                                                        <tr>
                                                                                            <td colspan="6">No Invoices Found</td>
                                                                                        </tr>
                                                                                    @endforelse --}}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="notification_dashboard_wrapper shadow_box_wrapper">
                            <h3>Potential Accounts</h3>

                            <div class="service_complete_wrapper">
                                <ul class="notification_ul-wrapper">
                                    @forelse ($MyPotentialClients as $client)
                                        <li>
                                            <div>
                                                <h5>{{ $client->name }}</h5>
                                                <div class="dropdown potientail_clients_dropdown">
                                                    <button class="dropdown-toggle" type="button" id="dropdownMenuButton11" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fa-solid fa-ellipsis"></i>
                                                    </button>
                                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                        <li><a class="dropdown-item" href="{{ route('clients.show', [$client->id]) }}">View</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div>
                                                <p>{{ $client->created_at->diffForHumans() }}</p>
                                                <span>{{ $client->payment_type }}</span>
                                            </div>
                                        </li>
                                    @empty
                                        <li>
                                            <div>
                                                <h5>No Potential Clients Found</h5>
                                            </div>
                                        </li>
                                    @endforelse
                                </ul>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>
    @elseif(auth()->user()->hasRole('staff'))
        <section class="homePage_section">
            <div class="container-fluid custom_container">
                <div class="row">
                    <div class="col-md-8">
                        <div class="row  custom_row height_100_percent">
                            <div class="col-md-12">
                                <div class="cards_dashboard_index_wrapper shadow_box_wrapper">
                                    <div>
                                        <h4>This is Week {{ $weekNumber ?? '' }} :</h4>
                                        <h4>{{ $currentMonth ?? '' }} {{ $startOfWeek ?? '' }} - {{ $endOfWeek ?? '' }} ,  {{ $currentYear ?? '' }}</h4>
                                    </div>
                                    <div class="staff_routes_group_scroll">
                                        @forelse($staffRoute as $route)
                                            <div class="new_yorks-cards_wrapper new_yorks-cards_wrapper_compact">
                                                <div>
                                                    <h2>{{ $route->name ?? '' }}</h2>
                                                    <div class="jobs_icon_wrapper">
                                                        <div>
                                                            <div>
                                                                <label>Scheduled:</label>
                                                                <span>{{ $route->jobs_total ?? 0 }}</span>
                                                            </div>
                                                            <div>
                                                                <label>Completed:</label>
                                                                <span>{{ $route->jobs_completed ?? 0 }}</span>
                                                            </div>
                                                            <div>
                                                                <label>Pending</label>
                                                                <span>{{ $route->jobs_pending ?? 0 }}</span>
                                                            </div>
                                                        </div>
                                                        <a href="{{ route('staffroutes.show', [$route->id]) }}">
                                                            <div>
                                                                <img src="{{ asset('website') }}/assets/images/Arrow-up-right_white.svg">
                                                            </div>
                                                        </a>
                                                    </div>
                                                </div>
                                                @php
                                                    $totalJobs = ($route->jobs_pending ?? 0) + ($route->jobs_completed ?? 0);
                                                    $completedPercentage = $totalJobs > 0 ? round(($route->jobs_completed / $totalJobs) * 100) : 0;

                                                    // Determine color, icon and background based on percentage
                                                    if ($totalJobs === 0) {
                                                        $progressColor = '#9e9e9e';
                                                        $progressBg = '#eeeeee';
                                                        $progressIcon = 'fa-ban';
                                                        $progressText = 'Inactive';
                                                    } elseif ($completedPercentage <= 20) {
                                                        $progressColor = '#ff5500';
                                                        $progressBg = '#fbf2ec';
                                                        $progressIcon = 'fa-hourglass-start';
                                                        $progressText = 'Completed';
                                                    } elseif ($completedPercentage <= 50) {
                                                        $progressColor = '#ff9800';
                                                        $progressBg = '#FFF3E0';
                                                        $progressIcon = 'fa-spinner';
                                                        $progressText = 'In Progress';
                                                    } elseif ($completedPercentage <= 80) {
                                                        $progressColor = '#ff9800';
                                                        $progressBg = '#FFF3E0';
                                                        $progressIcon = 'fa-hourglass-half';
                                                        $progressText = 'Nearly';
                                                    } elseif ($completedPercentage < 100) {
                                                        $progressColor = '#ff9800';
                                                        $progressBg = '#FFF3E0';
                                                        $progressIcon = 'fa-check-circle';
                                                        $progressText = 'Almost Done';
                                                    } else {
                                                        $progressColor = '#4caf50';
                                                        $progressBg = '#b5fbd0';
                                                        $progressIcon = 'fa-check-circle';
                                                        $progressText = 'Completed';
                                                    }
                                                @endphp
                                                <div style="background: {{ $progressBg }}; border-radius: 8px; padding: 4px 5px;">
                                                    <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 3px;">
                                                        <i class="fa-solid {{ $progressIcon }}" style="color: {{ $progressColor }}; font-size: 11px;"></i>
                                                        <h5 style="color: {{ $progressColor }}; margin: 0; font-size: 11px;">{{ $totalJobs === 0 ? $progressText : $completedPercentage . '% ' . $progressText }}</h5>
                                                    </div>
                                                    <div style="width: 100%; background-color: #e0e0e0; border-radius: 10px; height: 6px; overflow: hidden; margin-bottom: 3px;">
                                                        <div style="width: {{ $totalJobs === 0 ? 100 : $completedPercentage }}%; background-color: {{ $progressColor }}; height: 100%; transition: width 0.3s ease, background-color 0.3s ease;">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div>
                                                There's no Completed Routes Available.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                            <!-- <div class="col-md-6">
                                                                    <div class="total_undeposited_cash_wrap shadow_box_wrapper">
                                                                        <h3>Total Undeposited Cash</h3>
                                                                        <span>$2,433.24</span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="my_payroll_worksheet shadow_box_wrapper">
                                                                        <a href="#">
                                                                            <h4>My Payroll Worksheet</h4>
                                                                        </a>
                                                                    </div>
                                                                </div> -->
                            <div class="col-md-12">
                                <div class="customer_payroll_sheets shadow_box_wrapper">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="payroll_cards shadow_box_wrapper">
                                                <h3>Total Undeposited Cash</h3>
                                                <h4>5,000</h4>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="payroll_cards shadow_box_wrapper">
                                                <h3>{{ auth()->user()->hasRole('staff') ? 'Unpaid Accounts' : 'Unpaid Customers' }}</h3>
                                                <h4>0 - 69</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="custom_div custom_div_deposit">
                                    <div class="custom_justify_between">
                                        <h3>Deposits</h3>
                                    </div>
                                    <div class="custom_table custom_table_dashboard">
                                        <div class="table-responsive">
                                            <table class="table deposits-table">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Route</th>
                                                        <th>Week</th>
                                                        <th>Amount</th>
                                                        <th>Deposited</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($deposits as $deposit)
                                                        <tr>
                                                            <td>{{ $deposit->deposit_date ? $deposit->deposit_date->format('m-d-Y') : 'N/A' }}</td>
                                                            <td>{{ $deposit->route->name ?? 'N/A' }}</td>
                                                            <td>Week {{ (int) str_replace('week', '', $deposit->week) + 1 }}</td>
                                                            <td>${{ number_format($deposit->total_amount, 2) }}</td>
                                                            <td>
                                                                <input type="checkbox" disabled {{ $deposit->deposit_amount > 0 ? 'checked' : '' }}>
                                                                ${{ number_format($deposit->deposit_amount, 2) }}
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="5" class="text-center">No Deposits Found</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="row  custom_row height_100_percent">
                            <div class="col-md-12">
                                <div class="notification_dashboard_wrapper shadow_box_wrapper">
                                    <h3>Notifications</h3>

                                    <div class="service_complete_wrapper service_complete_wrapper_staff">
                                        <ul class="notification_ul-wrapper">
                                            @forelse ($notifications as $notification)
                                                <li data-notification-id="{{ $notification->id }}">
                                                    <div>
                                                        <h5>{{ $notification->title }}</h5>
                                                        <a href="javascript:void(0)" class="delete_btn" data-notification-id="{{ $notification->id }}">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </a>
                                                    </div>
                                                    <div>
                                                        <p>{{ $notification->created_at->diffForHumans() }}</p>
                                                        <span>{{ $notification->message }}</span>
                                                    </div>
                                                </li>
                                            @empty
                                                <li>
                                                    <div>
                                                        <h5>No Notifications Found</h5>
                                                    </div>
                                                </li>
                                            @endforelse
                                        </ul>
                                    </div>

                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="notification_dashboard_wrapper shadow_box_wrapper">
                                    <div class="potientails_icon_wrap">
                                        <h3>My Potential Clients</h3>
                                        <img src="https://cleaning.thebackendprojects.com/website/assets/images/Arrow-up-right_dashboard.svg">
                                    </div>
                                    <div class="service_complete_wrapper service_complete_wrapper_staff">
                                        <ul class="notification_ul-wrapper">
                                            @forelse ($MyPotentialClients as $client)
                                                <li>
                                                    <div>
                                                        <h5>{{ $client->name }}</h5>
                                                        <div class="dropdown potientail_clients_dropdown">
                                                            <button class="dropdown-toggle" type="button" id="dropdownMenuButton11" data-bs-toggle="dropdown" aria-expanded="false">
                                                                <i class="fa-solid fa-ellipsis"></i>
                                                            </button>
                                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                <li><a class="dropdown-item" href="{{ route('clients.show', [$client->id]) }}">View</a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <p>{{ $client->created_at->diffForHumans() }}</p>
                                                        <span>{{ $client->payment_type }}</span>
                                                    </div>
                                                </li>
                                            @empty
                                                <li>
                                                    <div>
                                                        <h5>No Potential Clients Found</h5>
                                                    </div>
                                                </li>
                                            @endforelse
                                        </ul>
                                    </div>

                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="notification_dashboard_wrapper shadow_box_wrapper">
                                    <h3>I Need</h3>
                                    <form method="post" action="{{ route('staffrequirements.store') }}" class="form-horizontal" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="timestamp" value="{{ now() }}">
                                        <div class="notification_dashboard_wrapper shadow_box_wrapper">
                                            <h3>I Need</h3>
                                            <div class="i_need_wrapper">
                                                <div class="i_need_wrapper_inner">
                                                    <div>
                                                        <input id="soap" name="items[soap][name]" type="checkbox" value="soap">
                                                        <label for="soap">Soap</label>
                                                    </div>
                                                </div>
                                                <div class="i_need_wrapper_inner">
                                                    <div>
                                                        <input id="business_cards" name="items[business_card][name]" type="checkbox" value="business_card">
                                                        <label for="business_cards">Business Cards</label>
                                                    </div>
                                                </div>
                                                <div class="i_need_wrapper_inner">
                                                    <div>
                                                        <input id="blades" name="items[blade][name]" type="checkbox" value="blade">
                                                        <label for="blades">Blades</label>
                                                    </div>
                                                </div>
                                                <div class="i_need_wrapper_inner">
                                                    <div>
                                                        <input id="others" name="items[other][name]" type="checkbox" value="other">
                                                        <label for="others">Other</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="others_requirments">
                                                <input type="text" name="items[other][description]" placeholder="Other Instructions">
                                                <label>Please Enter Any Other Requirement</label>
                                            </div>
                                            <button type="submit" class="btn_global btn_blue">Submit<i class="fa-solid fa-check"></i></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="modal fade" id="add_deposit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <form method="" action="" id="" class="form-horizontal" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <div>
                                <h2 class="modal-title" id="exampleModalLabel1">Add Deposits</h2>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="txt_field">
                                <label for="depositDate">Enter Date</label>
                                <input class="form-control" type="date" value="" id="depositDate">
                            </div>
                            <div class="txt_field">
                                <label for="depositWeek">Enter Week</label>
                                <input class="form-control" type="number" value="" placeholder="02" id="depositWeek">
                            </div>
                            <div class="txt_field">
                                <label for="depositAmount">Enter Amount</label>
                                <input class="form-control" type="number" value="" placeholder="12345" id="depositAmount">
                            </div>
                            <div class="txt_field">
                                <label for="routeSelect">Select Route</label>
                                <select class="form-select" name="routeSelect">
                                    <option value="" selected disabled>Select Route</option>
                                    <option value="Karachi">Karachi</option>
                                    <option value="Lahore">Lahore</option>
                                    <option value="Multan">Multan</option>
                                    <option value="Quetta">Quetta</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer custom_justify_between">
                            <button type="button" class="btn_global btn_grey" data-bs-dismiss="modal" aria-label="Close">Cancel <i class="fa-solid fa-x"></i></button>
                            <button type="submit" class="btn_global btn_blue">Assign Deposit<i class="fa-solid fa-check"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection
@push('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            if ($('.deposits-table tbody tr').length > 0 && !$('.deposits-table tbody tr td[colspan]').length) {
                $('.deposits-table').DataTable({
                    "pageLength": 10,
                    "order": [
                        [0, "desc"]
                    ], // Sort by date
                    "columnDefs": [{
                            "orderable": false,
                            "targets": 4
                        } // Deposited column not sortable
                    ]
                });
            }
        });
    </script>
    <script>
        var lineChart = document.getElementById("line-chart").getContext('2d');

        // Gradients
        var gradientOne = lineChart.createLinearGradient(0, 0, 0, 600);
        gradientOne.addColorStop(0, 'rgba(0, 173, 238, 0.5)');
        gradientOne.addColorStop(1, 'rgba(0, 173, 238, 0.00)');

        var gradientTwo = lineChart.createLinearGradient(0, 0, 0, 600);
        gradientTwo.addColorStop(0, 'rgba(50, 52, 106, 0.6)');
        gradientTwo.addColorStop(1, 'rgba(50, 52, 106, 0.00)');

        var monthlyCashData = {!! json_encode($monthlyCashData ?? [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]) !!};
        var monthlyInvoiceData = {!! json_encode($monthlyInvoiceData ?? [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]) !!};

        var datasets = [{
            label: 'Cash',
            data: monthlyCashData,
            borderColor: '#00ADEE',
            fill: 'start',
            backgroundColor: gradientOne,
            tension: 0.4,
            pointBackgroundColor: '#1B1732',
            pointRadius: 0,
            pointHoverRadius: 5
        }, {
            label: 'Invoice',
            data: monthlyInvoiceData,
            borderColor: '#32346A',
            fill: 'start',
            backgroundColor: gradientTwo,
            tension: 0.4,
            pointBackgroundColor: '#1B1732',
            pointRadius: 0,
            pointHoverRadius: 5
        }];

        var options = {
            borderWidth: 1,
            cubicInterpolationMode: 'monotone',
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderWidth: 4,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        color: "#4A4A4A",
                        font: {
                            size: 14
                        },
                        usePointStyle: true,
                        padding: 15
                    }
                },
                tooltip: {
                    backgroundColor: '#4a4a4a',
                    fontColor: 'red', // Make sure this is intended
                    usePointStyle: false,
                    boxPadding: 20,
                    intersect: false,
                    mode: 'index',
                    padding: 10,
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false // Hide x-axis grid lines
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(e) {
                            return "$" + e.toLocaleString();
                        }
                    },
                    grid: {
                        display: true // Show y-axis grid lines
                    }
                }
            },
            hover: {
                mode: 'index',
                intersect: false
            }
        };

        lineChartInstance = new Chart(lineChart, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'July', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec'],
                datasets: datasets
            },
            options: options
        });
    </script>


    {{-- date range picker --}}
    <script>
        var lineChartInstance; // Store chart instance globally

        $("#kt_daterangepicker_1").daterangepicker({
            startDate: moment().startOf('year'),
            endDate: moment().endOf('year'),
            locale: {
                format: 'MM/DD/YYYY'
            }
        }, function(start, end, label) {
            // When date range changes, fetch new data
            $.ajax({
                url: "{{ route('dashboard.sales_data') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    start_date: start.format('YYYY-MM-DD'),
                    end_date: end.format('YYYY-MM-DD')
                },
                success: function(response) {
                    if (response.success) {
                        // Update chart data
                        lineChartInstance.data.datasets[0].data = response.cashData;
                        lineChartInstance.data.datasets[1].data = response.invoiceData;
                        lineChartInstance.update();
                    }
                },
                error: function(xhr) {
                    console.error('Error fetching sales data:', xhr);
                }
            });
        });
    </script>
    {{-- delete functionality for notification sec --}}
    <script>
        $(document).ready(function() {
            // Event listener for clicks on any element with the class 'delete_btn'
            $('.delete_btn').click(function(e) {
                e.preventDefault();

                const notificationId = $(this).data('notification-id');
                const $listItem = $(this).closest('li');

                // Confirm before deleting using SweetAlert
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you want to delete this notification?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Send AJAX request to delete notification
                        $.ajax({
                            url: '{{ route('notifications.delete', ':id') }}'.replace(
                                ':id',
                                notificationId),
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    // Show success message
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Deleted!',
                                        text: response.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    });

                                    // Remove the notification from DOM with animation
                                    $listItem.fadeOut(300, function() {
                                        $(this).remove();

                                        // Check if there are no more notifications
                                        if ($('.notification_ul-wrapper li')
                                            .length === 0) {
                                            $('.notification_ul-wrapper').html(
                                                '<li><div><h5>No Notifications Found</h5></div></li>'
                                            );
                                        }
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: 'Failed to delete notification. Please try again.',
                                });
                                console.error('Error:', xhr);
                            }
                        });
                    }
                });
            });
        });
    </script>

    {{-- //For Staff Requirements and Validations --}}
    <script>
        $(document).ready(function() {
            $('#others').on('change', function() {
                if ($(this).is(':checked')) {
                    $('input[name="items[other][description]"]').prop('disabled', false)
                } else {
                    $('input[name="items[other][description]"]').prop('disabled', true)
                }
            }).trigger('change');

            $('form').on('submit', function(e) {
                const isAnyChecked = $('.i_need_wrapper_inner input[type="checkbox"]:checked').length > 0;
                if (!isAnyChecked) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning!',
                        text: 'Please check at least one item.',
                    });
                    e.preventDefault();
                    return false;
                }

                if ($('#others').is(':checked') && $('input[name="items[other][description]"]').val()
                    .trim() === '') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning!',
                        text: 'Please enter a description for other requirements.',
                    });
                    e.preventDefault();
                    return false;
                }

                Swal.fire({
                    title: 'Please wait',
                    text: 'Processing request, this may take a few seconds...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                return true;
            });
        });
    </script>
@endpush
