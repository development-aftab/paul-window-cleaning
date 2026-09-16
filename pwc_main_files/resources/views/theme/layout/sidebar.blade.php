<!--begin::Sidebar-->
<div id="kt_app_sidebar" class="app-sidebar sidebar_wrapper flex-column custom_sidebar" data-kt-drawer="true"
    data-kt-drawer-name="app-sidebar" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true"
    data-kt-drawer-width="225px" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
    <!--begin::Logo-->
    <div class="app-sidebar-logo" id="kt_app_sidebar_logo">
        <!--begin::Logo image-->
        <a href="{{ url('home') }}">
            <img alt="Logo" src="{{ asset('') }}{{ App\Models\Setting::first()->logo ?? '' }}"
                class="app-sidebar-logo-default" />
            <img alt="Logo" src="{{ asset('website') }}/assets/images/Frame.svg"
                class="app-sidebar-logo-minimize" />
        </a>
        <!--end::Logo image-->
        <!--begin::Sidebar toggle-->
        <!--begin::Minimized sidebar setup:
     if (isset($_COOKIE["sidebar_minimize_state"]) && $_COOKIE["sidebar_minimize_state"] === "on") {
     1. "src/js/layout/sidebar.js" adds "sidebar_minimize_state" cookie value to save the sidebar minimize state.
     2. Set data-kt-app-sidebar-minimize="on" attribute for body tag.
     3. Set data-kt-toggle-state="active" attribute to the toggle element with "kt_app_sidebar_toggle" id.
     4. Add "active" class to to sidebar toggle element with "kt_app_sidebar_toggle" id.
     }
     -->
        <div id="kt_app_sidebar_toggle"
            class="app-sidebar-toggle btn btn-icon btn-shadow btn-sm btn-color-muted btn-active-color-primary h-30px w-30px position-absolute top-50 start-100 translate-middle rotate"
            data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body"
            data-kt-toggle-name="app-sidebar-minimize">
            <i class="ki-duotone ki-black-left-line fs-3 rotate-180">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </div>
        <!--end::Sidebar toggle-->
    </div>
    <!--end::Logo-->

    <!--begin::sidebar menu-->
    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
        <!--begin::Menu wrapper-->
        <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper">
            <!--begin::Scroll wrapper-->
            <div id="kt_app_sidebar_menu_scroll" class="scroll-y " data-kt-scroll="true" data-kt-scroll-activate="true"
                data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
                data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px"
                data-kt-scroll-save-state="true">
                <!--begin::Menu-->
                <div class="menu menu-column custom_sidebar_menu menu-rounded menu-sub-indention fw-semibold fs-6"
                    id="#kt_app_sidebar_menu" data-kt-menu="true" data-kt-menu-expand="false">
                    <!--begin:Menu item-->
                    <!--begin:Menu item-->

                    <div class="menu-item ">
                        <div class="menu-content ">
                            <a href="{{ url('dashboard_index') }}"
                                class="nav_list @if (request()->is('dashboard_index*')) active @endif" aria-current="page">
                                <div class="sidebar_icon"><i class="fa-solid fa-house"></i></div>
                                Dashboard
                            </a>
                        </div>
                    </div>
                    @if (auth()->user()->hasRole('developer'))
                        <div class="menu-item pt-5">
                            <!--begin:Menu content-->
                            <div class="menu-content">
                                <span class="menu-heading fw-bold text-uppercase fs-7">Developer</span>
                            </div>
                            <!--end:Menu content-->
                        </div>
                        <!--end:Menu item-->
                        <!--begin:Menu item-->
                        <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                            <!--begin:Menu link-->
                            <span class="menu-link">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-abstract-28 fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </span>
                                <span class="menu-title">User Management</span>
                                <span class="menu-arrow"></span>
                            </span>
                            <!--end:Menu link-->
                            <!--begin:Menu sub-->
                            <div class="menu-sub menu-sub-accordion">
                                <!--begin:Menu item-->

                                {{-- UL --}}
                                <div data-kt-menu-trigger="click" class="menu-item menu-accordion mb-1">
                                    <!--begin:Menu link-->

                                    {{-- LI --}}
                                    @can('crud-list')
                                        <span class="menu-link">
                                            <span class="menu-bullet">
                                                <span class="bullet bullet-dot"></span>
                                            </span>
                                            <span class="menu-title">CRUD</span>
                                            <span class="menu-arrow"></span>
                                        </span>
                                    @endcan
                                    <!--end:Menu link-->
                                    <!--begin:Menu sub-->
                                    <div class="menu-sub menu-sub-accordion">
                                        <!--begin:Menu item-->
                                        <div class="menu-item">
                                            <!--begin:Menu link-->
                                            <a class="menu-link" href="{{ url('crud_generator') }}">
                                                <span class="menu-bullet">
                                                    <span class="bullet bullet-dot"></span>
                                                </span>
                                                <span class="menu-title">CRUD Generator</span>
                                            </a>
                                            <!--end:Menu link-->
                                        </div>
                                        <!--end:Menu item-->
                                    </div>
                                    <!--end:Menu sub-->
                                </div>
                                <div data-kt-menu-trigger="click" class="menu-item menu-accordion mb-1">
                                    <!--begin:Menu link-->
                                    @can('user-list')
                                        <span class="menu-link">
                                            <span class="menu-bullet">
                                                <span class="bullet bullet-dot"></span>
                                            </span>
                                            <span class="menu-title">Users</span>
                                            <span class="menu-arrow"></span>
                                        </span>
                                    @endcan
                                    <!--end:Menu link-->
                                    <!--begin:Menu sub-->
                                    <div class="menu-sub menu-sub-accordion">
                                        <!--begin:Menu item-->
                                        <div class="menu-item">
                                            <!--begin:Menu link-->
                                            <a class="menu-link" href="{{ url('users') }}">
                                                <span class="menu-bullet">
                                                    <span class="bullet bullet-dot"></span>
                                                </span>
                                                <span class="menu-title">Users List</span>
                                            </a>
                                            <!--end:Menu link-->
                                        </div>
                                        <!--end:Menu item-->
                                    </div>
                                    <!--end:Menu sub-->
                                </div>
                                <!--end:Menu item-->
                                <!--begin:Menu item-->
                                <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                                    <!--begin:Menu link-->
                                    @can('role-list')
                                        <span class="menu-link">
                                            <span class="menu-bullet">
                                                <span class="bullet bullet-dot"></span>
                                            </span>
                                            <span class="menu-title">Roles</span>
                                            <span class="menu-arrow"></span>
                                        </span>
                                    @endcan
                                    <!--end:Menu link-->
                                    <!--begin:Menu sub-->
                                    <div class="menu-sub menu-sub-accordion">
                                        <!--begin:Menu item-->
                                        <div class="menu-item">
                                            <!--begin:Menu link-->
                                            <a class="menu-link" href="{{ url('roles') }}">
                                                <span class="menu-bullet">
                                                    <span class="bullet bullet-dot"></span>
                                                </span>
                                                <span class="menu-title">Roles List</span>
                                            </a>
                                            <!--end:Menu link-->
                                        </div>
                                        <!--end:Menu item-->
                                    </div>
                                    <!--end:Menu sub-->
                                </div>
                                <!--end:Menu item-->
                                <!--begin:Menu item-->
                                <div class="menu-item">
                                    <!--begin:Menu link-->
                                    <a class="menu-link" href="javascript:void(0);">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Permissions</span>
                                    </a>
                                    <!--end:Menu link-->
                                </div>
                                <!--end:Menu item-->
                                @can('settings-list')
                                    <div class="menu-item">
                                        <!--begin:Menu link-->
                                        <a class="menu-link" href="{{ url('settings') }}">
                                            <span class="menu-bullet">
                                                <span class="bullet bullet-dot"></span>
                                            </span>
                                            <span class="menu-title">Settings</span>
                                        </a>
                                        <!--end:Menu link-->
                                    </div>
                                @endcan
                            </div>
                            <!--end:Menu sub-->
                        </div>
                        <hr>
                        <div class="menu-item pt-5">
                            <div class="menu-content">
                                <span class="menu-heading fw-bold text-uppercase fs-7">Menu</span>
                            </div>
                        </div>
                        @foreach ($crud as $item)
                            @can($item->url . '-list')
                                {{-- @can(\Illuminate\Support\Str::slug($item->name) . '-list') --}}
                                <div class="menu-item">
                                    <!--begin:Menu link-->
                                    <a class="menu-link {{ request()->is($item->url) ? 'active' : '' }}"
                                        href="{{ url($item->url ?? 'home') }}">
                                        <span class="menu-icon">
                                            <i class="ki-duotone ki-abstract-28 fs-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </span>
                                        <span
                                            class="menu-title">{{ preg_replace('/(?<=[a-z])[A-Z]|[A-Z](?=[a-z])/', ' $0', $item->name) }}</span>
                                    </a>
                                    <!--end:Menu link-->
                                </div>
                            @endcan
                        @endforeach
                    @elseif(auth()->user()->hasRole('staff'))
                        <div class="menu-item ">
{{--                            <div class="menu-content">--}}
{{--                                <a href="{{ url('staffroutes') }}"--}}
{{--                                    class="nav_list @if (request()->is('staffroutes*') || request()->is('client_invoice*') || request()->is('client_cash*')) active @endif"--}}
{{--                                    aria-current="page">--}}
{{--                                    <div class="sidebar_icon"><i class="fa-solid fa-location-dot"></i></div>--}}
{{--                                    Assigned Routes--}}
{{--                                </a>--}}
{{--                            </div>--}}
                            <div class="menu-content">
                                {{--                                <a href="{{url('client_management')}}" class="nav_list @if (request()->route()->getName() == 'client_management' || request()->route()->getName() == 'create_client' || request()->route()->getName() == 'client-details') active @endif" aria-current="page"> --}}
                                <a href="{{ url('clients') }}"
                                    class="nav_list @if (request()->is('clients*') || request()->is('client-schedule*') || request()->is('branch/*')) active @endif"
                                    aria-current="page">
                                    <div class="sidebar_icon"><i class="fa-solid fa-users-viewfinder"></i></div>
                                    Clients
                                </a>
                            </div>
{{--                            <div class="menu-content">--}}
{{--                                <a href="{{ url('timelogs') }}"--}}
{{--                                    class="nav_list @if (request()->is('timelogs*')) active @endif"--}}
{{--                                    aria-current="page">--}}
{{--                                    <div class="sidebar_icon"><i class="fa-solid fa-clock"></i></div>--}}
{{--                                    Time Log--}}
{{--                                </a>--}}
{{--                            </div>--}}
                            <div class="menu-item ">
                                <div class="menu-content">
                                    <a href="{{ url('deposits') }}"
                                        class="nav_list @if (request()->is('deposits') || request()->is('deposits/route*')) active @endif"
                                        aria-current="page">
                                        <div class="sidebar_icon"><i class="fa-solid fa-wallet"></i></div>
                                        Total Undeposited Cash
                                    </a>
                                </div>
                            </div>
                            <div class="menu-item ">
                                <div class="menu-content">
                                    <a href="{{ route('payroll.index') }}"
                                        class="nav_list @if (request()->is('payroll*')) active @endif"
                                        aria-current="page">
                                        <div class="sidebar_icon"><i class="fa-solid fa-money-bill-wave"></i></div>
                                        My Payroll Worksheet
                                    </a>
                                </div>
                            </div>
                            <div class="menu-item ">
                                <div class="menu-content">
                                    <a href="{{ route('reports.unpaid') }}"
                                        class="nav_list @if (request()->is('reports/unpaid*')) active @endif"
                                        aria-current="page">
                                        <div class="sidebar_icon"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                                        Unpaid Accounts
                                    </a>
                                </div>
                            </div>
                            <div class="menu-item">
                                <div class="menu-content">
                                    <a href="{{ url('route_report') }}"
                                        class="nav_list @if (request()->is('route_report*')) active @endif"
                                        aria-current="page">
                                        <div class="sidebar_icon"><i class="fa-solid fa-chart-line"></i></div>
                                        Route Reports
                                    </a>
                                </div>
                            </div>
                        </div>
                    @elseif(auth()->user()->hasRole('admin'))
                        @php
                            // Active state per menu link (URL patterns), used for the link highlight
                            // AND to keep its dropdown group open on page load.
                            $isStaffManagement = request()->is('staffmembers*');
                            $isPayroll = request()->is('payroll*');
                            $isStaffRequests = request()->is('staff-request*');
                            $isInvoices = request()->is('invoice*') || request()->is('schedule/route/invoices*') || request()->is('client/invoice*');
                            $isClients = request()->is('clients*') || request()->is('client-schedule*') || request()->is('branch/*');
                            $isRoutes = request()->is('staffroutes*') || request()->is('client_cash*') || request()->is('view_client_cash*') || request()->is('client_invoice*') || request()->is('view_client_invoice*');
                            $isCompleteJobs = request()->is('complete-jobs*');
                            $isRouteReports = request()->is('route_report*');
                            $isDeposits = request()->is('deposits*');
                            $isUnpaid = request()->is('reports/unpaid*');
                            $isCms = request()->is('cms');
                            $isTestimonials = request()->is('testimonials*');
                            $isQuotes = request()->is('contacts*');

                            $staffOpen = $isStaffManagement || $isPayroll || $isStaffRequests;
                            $clientsOpen = $isInvoices || $isClients;
                            $reportsOpen = $isCompleteJobs || $isRouteReports || $isDeposits || $isUnpaid;
                            $frontPageOpen = $isCms || $isTestimonials || $isQuotes;
                        @endphp
                        <div class="menu-item ">
                            <div class="menu-content">
                                <button class="collapse_btn {{ $staffOpen ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseStaff" aria-expanded="{{ $staffOpen ? 'true' : 'false' }}" aria-controls="collapseStaff">
                                    <div class="sidebar_icon"><i class="fa-solid fa-user-group"></i></div> Staff <div class="sidebar_icon"><i class="fa-solid fa-chevron-down"></i></div>
                                </button>
                                <div class="collapse {{ $staffOpen ? 'show' : '' }}" id="collapseStaff">
                                    <ul>
                                        {{--                                        <li>--}}
                                        {{--                                            <a href="{{ url('staffmembers') }}"--}}
                                        {{--                                               class="nav_list @if (request()->is('staffmembers*')) active @endif"--}}
                                        {{--                                               aria-current="page">--}}
                                        {{--                                                <div class="sidebar_icon"><i class="fa-solid fa-money-bill-wave"></i></div> Bonus Tracker--}}
                                        {{--                                            </a>--}}
                                        {{--                                        </li>--}}
                                        <li>
                                            <a href="{{ url('staffmembers') }}"
                                               class="nav_list @if ($isStaffManagement) active @endif"
                                               aria-current="page">
                                                <div class="sidebar_icon"><i class="fa-solid fa-money-bill-wave"></i></div>
                                                Management
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('payroll.index') }}"
                                               class="nav_list @if ($isPayroll) active @endif"
                                               aria-current="page">
                                                <div class="sidebar_icon"><i class="fa-solid fa-money-bill-wave"></i></div>
                                                Payroll
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ url('staff-request') }}"
                                               class="nav_list @if ($isStaffRequests) active @endif"
                                               aria-current="page">
                                                <div class="sidebar_icon"><i class="fa-solid fa-user-plus"></i></div>
                                                Requests
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="menu-item ">
                            <div class="menu-content">
                                <button class="collapse_btn {{ $clientsOpen ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseClients" aria-expanded="{{ $clientsOpen ? 'true' : 'false' }}" aria-controls="collapseClients">
                                    <div class="sidebar_icon"><i class="fa-solid fa-user-group"></i></div> Clients <div class="sidebar_icon"><i class="fa-solid fa-chevron-down"></i></div>
                                </button>
                                <div class="collapse {{ $clientsOpen ? 'show' : '' }}" id="collapseClients">
                                    <ul>
                                        <li>
                                            <a href="{{ route('invoices') }}"
                                               class="nav_list @if ($isInvoices) active @endif"
                                               aria-current="page">
                                                <div class="sidebar_icon"><i class="fa-solid fa-wallet"></i></div>
                                                Invoice
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ url('clients') }}"
                                               class="nav_list @if ($isClients) active @endif"
                                               aria-current="page">
                                                <div class="sidebar_icon"><i class="fa-solid fa-users"></i></div>
                                                Manage
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="menu-item">
                            <div class="menu-content">
                                <a href="{{ url('staffroutes') }}"
                                    class="nav_list @if ($isRoutes) active @endif"
                                    aria-current="page">
                                    <div class="sidebar_icon"><i class="fa-solid fa-map-location-dot"></i></div>
                                    Routes
                                </a>
                            </div>
                        </div>
                        <div class="menu-item ">
                            <div class="menu-content">
                                <button class="collapse_btn {{ $reportsOpen ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReports" aria-expanded="{{ $reportsOpen ? 'true' : 'false' }}" aria-controls="collapseReports">
                                    <div class="sidebar_icon"><i class="fa-solid fa-user-group"></i></div> Reports <div class="sidebar_icon"><i class="fa-solid fa-chevron-down"></i></div>
                                </button>
                                <div class="collapse {{ $reportsOpen ? 'show' : '' }}" id="collapseReports">
                                    <ul>
{{--                                        <li>--}}
{{--                                            <a href="{{ url('clients') }}"--}}
{{--                                               class="nav_list @if (request()->is('clients*') || request()->is('client-schedule*')) active @endif"--}}
{{--                                               aria-current="page">--}}
{{--                                                <div class="sidebar_icon"><i class="fa-solid fa-users"></i></div>--}}
{{--                                                Analytics--}}
{{--                                            </a>--}}
{{--                                        </li>--}}
                                        <li>
                                            <a href="{{ url('complete-jobs') }}"
                                               class="nav_list @if ($isCompleteJobs) active @endif"
                                               aria-current="page">
                                                <div class="sidebar_icon"><i class="fa-solid fa-check-circle"></i></div>
                                                Complete Jobs
                                            </a>
                                        </li>
{{--                                        <li>--}}
{{--                                            <a href="{{ url('clients') }}"--}}
{{--                                               class="nav_list @if (request()->is('clients*') || request()->is('client-schedule*')) active @endif"--}}
{{--                                               aria-current="page">--}}
{{--                                                <div class="sidebar_icon"><i class="fa-solid fa-users"></i></div>--}}
{{--                                                Deposit Made--}}
{{--                                            </a>--}}
{{--                                        </li>--}}
                                        <li>
                                            <a href="{{ url('route_report') }}"
                                               class="nav_list @if ($isRouteReports) active @endif"
                                               aria-current="page">
                                                <div class="sidebar_icon"><i class="fa-solid fa-chart-line"></i></div>
                                                Route Reports
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ url('deposits') }}"
                                               class="nav_list @if ($isDeposits) active @endif"
                                               aria-current="page">
                                                <div class="sidebar_icon"><i class="fa-solid fa-wallet"></i></div>
                                                Total Undeposited Cash
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('reports.unpaid') }}"
                                               class="nav_list @if ($isUnpaid) active @endif"
                                               aria-current="page">
                                                <div class="sidebar_icon"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                                                Unpaid Accounts
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="menu-item ">
                            <div class="menu-content">
                                <button class="collapse_btn {{ $frontPageOpen ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFrontPage" aria-expanded="{{ $frontPageOpen ? 'true' : 'false' }}" aria-controls="collapseFrontPage">
                                    <div class="sidebar_icon"><i class="fa-solid fa-user-group"></i></div> Front Page <div class="sidebar_icon"><i class="fa-solid fa-chevron-down"></i></div>
                                </button>
                                <div class="collapse {{ $frontPageOpen ? 'show' : '' }}" id="collapseFrontPage">
                                    <ul>
                                        <li>
                                            <a href="{{ url('cms') }}"
                                               class="nav_list @if ($isCms) active @endif"
                                               aria-current="page">
                                                <div class="sidebar_icon"><i class="fa-solid fa-bars-progress"></i></div>
                                                CMS
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ url('testimonials') }}"
                                               class="nav_list @if ($isTestimonials) active @endif"
                                               aria-current="page">
                                                <div class="sidebar_icon"><i class="fa-solid fa-comment"></i></div>
                                                Testimonials
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ url('contacts') }}"
                                               class="nav_list @if ($isQuotes) active @endif"
                                               aria-current="page">
                                                <div class="sidebar_icon"><i class="fa-solid fa-envelope-open-text"></i></div>
                                                Quotes
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>


{{--                        <div class="menu-item ">--}}
{{--                            <div class="menu-content">--}}
{{--                                <a href="{{ url('timelogs') }}"--}}
{{--                                    class="nav_list @if (request()->is('timelogs')) active @endif"--}}
{{--                                    aria-current="page">--}}
{{--                                    <div class="sidebar_icon"><i class="fa-solid fa-clock"></i></div>--}}
{{--                                    Time Log--}}
{{--                                </a>--}}
{{--                            </div>--}}
{{--                        </div>--}}

                    @endif

                    <div class="menu-item  logout">
                        <div class="menu-content logout active">
                            <a href="{{ url('logout') }}"
                                class="nav_list {{ request()->is('logout') ? 'active' : '' }}" aria-current="page">
                                <div class="sidebar_icon"><i class="fa-solid fa-right-from-bracket"></i></div>
                                Logout
                            </a>
                        </div>
                    </div>
                </div>
                <!--end::Menu-->
            </div>
            <!--end::Scroll wrapper-->
        </div>
        <!--end::Menu wrapper-->
    </div>
    <!--end::sidebar menu-->
    <!--begin::Footer-->
    @if (auth()->user()->hasRole('developer'))
        <div class="app-sidebar-footer flex-column-auto pt-2 pb-6 px-6" id="kt_app_sidebar_footer">
            <a href="{{ url('html/demo1/dist') }}"
                class="btn btn-flex flex-center btn-custom btn-primary overflow-hidden text-nowrap px-0 h-40px w-100"
                data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-dismiss-="click"
                title="200+ in-house components and 3rd-party plugins">
                <span class="btn-label">Docs & Components</span>
                <i class="ki-duotone ki-document btn-icon fs-2 m-0">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
            </a>
        </div>
    @endif
    <!--end::Footer-->
</div>
<!--end::Sidebar-->
