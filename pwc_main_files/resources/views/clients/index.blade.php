@extends('theme.layout.master')

@push('css')
    <style>
        /* Child Row Styling */
        .child-row {
            background-color: #e8f4fd !important;
        }

        .child-row td {
            font-size: 0.85em !important;
            color: #555 !important;
            padding-top: 6px !important;
            padding-bottom: 6px !important;
            position: relative;
        }

        .child-row td:nth-child(2) {
            padding-left: 25px !important;
        }

        .child-row td:nth-child(2)::before {
            content: "↳";
            position: absolute;
            left: 8px;
            color: #007bff;
            font-weight: bold;
        }

        /* Left border indicator for child rows */
        .child-row td:first-child {
            border-left: 4px solid #007bff !important;
            background-color: #e8f4fd !important;
        }

        /* Expand button styling - Blue box design */
        .expand-btn {
            background-color: #00ADEE;
            border: none;
            cursor: pointer;
            padding: 8px 12px;
            color: #fff;
            font-size: 14px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .expand-btn:hover {
            background-color: #0095d0;
            color: #fff;
        }

        .expand-btn .fa-minus {
            color: #fff;
        }

        /* Parent row with children */
        .parent-row.has-children {
            font-weight: 500;
        }
    </style>
@endpush
@section('navbar-title')
    <div class="custom_justify_between">
        <h2 class="navbar_PageTitle">Client Management</h2>
    </div>
    <div class="custom_search txt_field custom_search">
        <input type="search" placeholder="Search" class="search_input custom_search_box">
        <i class="fa-solid fa-magnifying-glass search_icon"></i>
    </div>
@endsection
@section('content')
    @if (auth()->user()->hasRole('admin'))
        <section class="client_management staff_manag">
            <div class="container-fluid custom_container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="custom_div">
                            <div class="clients_tab custom_justify_between">
                                <ul class="nav nav-pills" id="pills-tab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="pills-clients-tab" data-bs-toggle="pill"
                                            data-bs-target="#pills-clients" type="button" role="tab"
                                            aria-controls="pills-clients" aria-selected="true">Clients</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="pills-potential_clients-tab" data-bs-toggle="pill"
                                            data-bs-target="#pills-potential_clients" type="button" role="tab"
                                            aria-controls="pills-potential_clients" aria-selected="false">Potential
                                            Clients</button>
                                    </li>
                                </ul>
                                <div class="create_btn custom_flex">
                                    <button type="button" id="exportExcel"
                                        class="btn_global btn_dark_blue exportBtn exportExcel"> Export Excel <i
                                            class="fa-solid fa-file-excel"></i>
                                    </button>

                                    <div class="sorting_filtering_wrapper">
                                        <select class="form-select sortFilter" id="sortByName"
                                            aria-label="Default select example">
                                            <option value="recent">Most Recent</option>
                                            <option value="az">A To Z</option>
                                        </select>
                                    </div>

                                    <div class="sorting_filtering_wrapper">
                                        <select class="form-select routeFilter" id="sortByRoute"
                                            aria-label="Default select example">
                                            <option value="all">Routes</option>
                                            @foreach ($routes as $route)
                                                <option value="{{ $route->id }}">{{ $route->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="sorting_filtering_wrapper">
                                        <select class="form-select" id="filterByClientType"
                                            aria-label="Filter by Client Type">
                                            <option value="">Client Types</option>
                                            <option value="residential">Residential</option>
                                            <option value="commercial">Commercial</option>
                                        </select>
                                    </div>

                                    <div class="sorting_filtering_wrapper">
                                        <select class="form-select" id="filterByPaymentType"
                                            aria-label="Filter by Payment Type">
                                            <option value="">Payment Types</option>
                                            <option value="cash">Cash</option>
                                            <option value="invoice">Invoice</option>
                                        </select>
                                    </div>

                                    @can('clients-create')
                                        <a class="btn btn-primary" href="{{ route('clients.create') }}">Create Client</a>
                                    @endcan

                                </div>
                            </div>
                            <div class="tab-content" id="pills-tabContent">
                                <div class="tab-pane fade show active" id="pills-clients" role="tabpanel"
                                    aria-labelledby="pills-clients-tab" tabindex="0">
                                    <div class="custom_table">
                                        <div class="table-responsive">
                                            <table class="table clients_table datatable">
                                                <thead>
                                                    <tr>
                                                        <th></th>
                                                        <th style="width: 18%;">Client Name</th>
                                                        <th class="d-none">most_recent</th>
                                                        <th style="width: 20%;">Phone</th>
                                                        <th style="width: 45%;">Address</th>
                                                        <th>City</th>
                                                        <th>Assigned Route</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($clients->where('is_child', 0) as $index => $client)
                                                        @php
                                                            $parentRouteName =
                                                                optional($client->clientRoute->first())->name ?? '';
                                                            $childSearchBlob = $client->childClients
                                                                ->map(function ($c) {
                                                                    $parts = array_filter([
                                                                        $c->name ?? '',
                                                                        $c->formatted_phone ?? '',
                                                                        $c->address ?? '',
                                                                        $c->city ?? '',
                                                                        optional($c->clientRoute->first())->name ?? '',
                                                                        optional($c->clientRoute->last())->name ?? '',
                                                                    ]);
                                                                    return \Illuminate\Support\Str::lower(
                                                                        trim(implode(' ', $parts)),
                                                                    );
                                                                })
                                                                ->filter()
                                                                ->implode(' ');
                                                        @endphp
                                                        <tr class="parent-row {{ count($client->childClients) > 0 ? 'has-children' : '' }}"
                                                            data-client-id="{{ $client->id }}"
                                                            data-client-type="{{ $client->client_type ?? '' }}"
                                                            data-payment-type="{{ $client->payment_type ?? '' }}"
                                                            data-parent-route="{{ $parentRouteName }}"
                                                            data-child-search="{{ e($childSearchBlob) }}"
                                                            data-child-routes="{{ $client->childClients->map(fn($c) => optional($c->clientRoute->first())->name)->filter()->implode('||') }}">
                                                            <td>
                                                                @if (count($client->childClients) > 0)
                                                                    <button class="expand-btn" type="button"
                                                                        data-parent-id="{{ $client->id }}"
                                                                        aria-expanded="false">
                                                                        <i class="fa-solid fa-plus expand-icon"></i>
                                                                    </button>
                                                                @endif
                                                            </td>
                                                            <td>{{ $client->name ?? '' }}</td>
                                                            @php
                                                                $clientUpdated =
                                                                    $client->updated_at ?? $client->created_at;
                                                                $scheduleUpdated = $client->clientSchedule->max(
                                                                    'updated_at',
                                                                );
                                                                $branchUpdated = $client->childClients->max(
                                                                    'updated_at',
                                                                );
                                                                $mostRecent = collect([
                                                                    $clientUpdated,
                                                                    $scheduleUpdated,
                                                                    $branchUpdated,
                                                                ])
                                                                    ->filter()
                                                                    ->max();
                                                            @endphp
                                                            <td class="d-none">{{ $mostRecent }}</td>
                                                            <td>
                                                                {{ $client->formatted_phone ?? '--' }}
                                                            </td>
                                                            <td>{{ $client->house_no ?? '' }}
                                                                {{ $client->address ?? '--' }}
                                                            </td>
                                                            <td>{{ $client->profile->city ?? '--' }}
                                                            </td>
                                                            <td>{{ optional($client->clientRoute->first())->name ?? '--' }}
                                                            </td>
                                                            <td>
                                                                <span
                                                                    class="badge {{ $client->status ? 'bg-success' : 'bg-danger' }}">
                                                                    {{ $client->status ? 'Active' : 'Inactive' }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <div class="dropdown">
                                                                    <button class="dropdown-toggle" type="button"
                                                                        id="dropdownMenuButton11" data-bs-toggle="dropdown"
                                                                        aria-expanded="false">
                                                                        <i class="fa-solid fa-ellipsis"></i>
                                                                    </button>
                                                                    <ul class="dropdown-menu"
                                                                        aria-labelledby="dropdownMenuButton11">
                                                                        @can('clients-list')
                                                                            <li>
                                                                                <a class="dropdown-item"
                                                                                    href="{{ route('clients.show', [$client->id]) }}">
                                                                                    View
                                                                                </a>
                                                                            </li>
                                                                        @endcan

                                                                        <li>
                                                                            <a class="dropdown-item"
                                                                                href="{{ route('clients.edit', $client->id) }}">
                                                                                Edit
                                                                            </a>
                                                                        </li>

                                                                        {{-- ✅ Schedule button sirf status = 1 hone pe dikhega --}}
                                                                        @if ($client->status == 1)
                                                                            @if ($client->clientRouteStaff && $client->clientRouteStaff->count() > 0)
                                                                                @if ($client->service_frequency)
                                                                                    <li>
                                                                                        <a class="dropdown-item"
                                                                                            href="{{ route('client-schedule', [$client->id]) }}">
                                                                                            Schedule
                                                                                        </a>
                                                                                    </li>
                                                                                @else
                                                                                    <li>
                                                                                        <a class="dropdown-item"
                                                                                            href="{{ route('clients.edit', $client->id) }}">
                                                                                            Assign Frequency
                                                                                        </a>
                                                                                    </li>
                                                                                @endif
                                                                            @else
                                                                                <li>
                                                                                    <a class="dropdown-item"
                                                                                        href="{{ route('clients.edit', $client->id) }}">
                                                                                        Assign Route
                                                                                    </a>
                                                                                </li>
                                                                            @endif
                                                                        @endif

                                                                        <li>
                                                                            <form
                                                                                action="{{ route('clients.toggle-status', $client->id) }}"
                                                                                method="POST" style="display: inline;">
                                                                                @csrf
                                                                                @method('PATCH')
                                                                                <button type="submit"
                                                                                    class="dropdown-item">
                                                                                    {{ $client->status == 1 ? 'Deactivate' : 'Activate' }}
                                                                                </button>
                                                                            </form>
                                                                        </li>

                                                                        @can('clients-delete')
                                                                            <li>
                                                                                {!! Form::open(['method' => 'DELETE', 'route' => ['clients.destroy', $client->id], 'class' => 'delete-form']) !!}
                                                                                <a class="dropdown-item"
                                                                                    href="javascript:void(0)"
                                                                                    onclick="showDeleteConfirmation(this)">
                                                                                    Delete
                                                                                </a>
                                                                                {!! Form::close() !!}
                                                                            </li>
                                                                        @endcan
                                                                    </ul>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>

                                            {{-- Hidden container for child rows (outside DataTables) --}}
                                            <div id="childRowsContainer" style="display: none;">
                                                <table class="clients_child_table">
                                                    <tbody>
                                                        @foreach ($clients->where('status', 1)->where('is_child', 0) as $client)
                                                            @foreach ($client->childClients as $child)
                                                                <tr class="child-row"
                                                                    data-parent-id="{{ $client->id }}"
                                                                    data-client-type="{{ $child->client_type ?? '' }}"
                                                                    data-payment-type="{{ $child->payment_type ?? '' }}"
                                                                    data-route-name="{{ optional($child->clientRoute->first())->name ?? '' }}">
                                                                    <td></td>
                                                                    <td>{{ $child->name ?? '' }}</td>
                                                                    <td class="d-none">{{ $child->created_at }}</td>
                                                                    <td>
                                                                        {{ $child->formatted_phone ?? '--' }}
                                                                    </td>
                                                                    <td>{{ $child->address ?? '-' }}
                                                                    </td>
                                                                    <td>{{ $child->city ?? '--' }}
                                                                    </td>
                                                                    <td>{{ optional($child->clientRoute->last())->name ?? '0' }}</td>
                                                                    <td>
                                                                        <span
                                                                            class="badge {{ $child->status ? 'bg-success' : 'bg-danger' }}">
                                                                            {{ $child->status ? 'Active' : 'Inactive' }}
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        <div class="dropdown">
                                                                            <button class="dropdown-toggle" type="button"
                                                                                id="dropdownMenuButton-child-{{ $child->id }}"
                                                                                data-bs-toggle="dropdown"
                                                                                aria-expanded="false">
                                                                                <i class="fa-solid fa-ellipsis"></i>
                                                                            </button>
                                                                            <ul class="dropdown-menu"
                                                                                aria-labelledby="dropdownMenuButton-child-{{ $child->id }}">
                                                                                @can('clients-list')
                                                                                    <li><a class="dropdown-item"
                                                                                            href="{{ route('clients.show', [$child->id]) }}">View</a>
                                                                                    </li>
                                                                                @endcan
                                                                                <li><a class="dropdown-item"
                                                                                        href="{{ route('branch.edit', $child->id) }}">Edit</a>
                                                                                </li>
                                                                                @if ($child->clientRouteStaff && $child->clientRouteStaff->count() > 0)
                                                                                    @if ($child->service_frequency)
                                                                                        <li><a class="dropdown-item"
                                                                                                href="{{ route('client-schedule', [$child->id]) }}">Schedule</a>
                                                                                        </li>
                                                                                    @else
                                                                                        <li><a class="dropdown-item"
                                                                                                href="{{ route('clients.edit', $child->id) }}">Assign
                                                                                                Frequency</a>
                                                                                        </li>
                                                                                    @endif
                                                                                @else
                                                                                    <li><a class="dropdown-item"
                                                                                            href="{{ route('branch.edit', $child->id) }}">Assign
                                                                                            Route </a>
                                                                                    </li>
                                                                                @endif
                                                                                <li>
                                                                                    <form
                                                                                        action="{{ route('clients.toggle-status', $child->id) }}"
                                                                                        method="POST"
                                                                                        style="display: inline;">
                                                                                        @csrf
                                                                                        @method('PATCH')
                                                                                        <button type="submit"
                                                                                            class="dropdown-item">
                                                                                            {{ $child->status == 1 ? 'Deactivate' : 'Activate' }}
                                                                                        </button>
                                                                                    </form>
                                                                                </li>
                                                                                @can('clients-delete')
                                                                                    <li>
                                                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['clients.destroy', $child->id], 'class' => 'delete-form']) !!}
                                                                                        <a class="dropdown-item"
                                                                                            href="javascript:void(0)"
                                                                                            onclick="showDeleteConfirmation(this)">Delete</a>
                                                                                        {!! Form::close() !!}
                                                                                    </li>
                                                                                @endcan
                                                                            </ul>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Potential Clients Tab with + icon for branches --}}
                                <div class="tab-pane fade" id="pills-potential_clients" role="tabpanel"
                                    aria-labelledby="pills-potential_clients-tab" tabindex="0">
                                    <div class="custom_table">
                                        <div class="table-responsive">
                                            <table class="table potential_clients_table datatable">
                                                <thead>
                                                    <tr>
                                                        <th></th> {{-- Expand icon column --}}
                                                        <th>Staff Name</th>
                                                        <th>Client Name</th>
                                                        <th>Parent Company</th>
                                                        <th class="d-none">created_at</th>
                                                        <th>Phone</th>
                                                        <th>Schedule</th>
                                                        <th>Assigned Route</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($clients->where('status', 0)->where('is_child', 0)->whereNotNull('staff_id') as $client)
                                                        @php
                                                            $parentRouteNamePot =
                                                                optional($client->clientRoute->first())->name ?? '';
                                                            $childSearchBlobPot = $client->childClients
                                                                ->map(function ($c) {
                                                                    $parts = array_filter([
                                                                        $c->name ?? '',
                                                                        $c->formatted_phone ?? '',
                                                                        $c->address ?? '',
                                                                        $c->city ?? '',
                                                                        optional($c->staff)->name ?? '',
                                                                        optional($c->clientRoute->first())->name ?? '',
                                                                    ]);
                                                                    return \Illuminate\Support\Str::lower(
                                                                        trim(implode(' ', $parts)),
                                                                    );
                                                                })
                                                                ->filter()
                                                                ->implode(' ');
                                                        @endphp
                                                        <tr class="parent-row {{ count($client->childClients) > 0 ? 'has-children' : '' }}"
                                                            data-client-id="{{ $client->id }}"
                                                            data-client-type="{{ $client->client_type ?? '' }}"
                                                            data-payment-type="{{ $client->payment_type ?? '' }}"
                                                            data-parent-route="{{ $parentRouteNamePot }}"
                                                            data-child-search="{{ e($childSearchBlobPot) }}"
                                                            data-child-routes="{{ $client->childClients->map(fn($c) => optional($c->clientRoute->first())->name)->filter()->implode('||') }}">
                                                            <td>
                                                                @if (count($client->childClients) > 0)
                                                                    <button class="expand-btn" type="button"
                                                                        data-parent-id="{{ $client->id }}"
                                                                        aria-expanded="false">
                                                                        <i class="fa-solid fa-plus expand-icon"></i>
                                                                    </button>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <div class="td_img_wrapper">
                                                                    <img src="{{ asset('website') }}/{{ $client->staff->profile->pic ?? 'users/no_avatar.jpg' }}"
                                                                        alt="No Image">
                                                                </div>
                                                                {{ $client->staff->name ?? '' }}
                                                            </td>
                                                            <td>{{ $client->name ?? '' }}</td>
                                                            <td>{{ $client->parentClient->name ?? '-' }}</td>
                                                            <td class="d-none">{{ $client->created_at }}</td>
                                                            <td>
                                                                {{ $client->formatted_phone ?? '--' }}
                                                            </td>
                                                            <td><span
                                                                    class="blue_color_td_span">{{ ucfirst($client->schedule ?? '') }}</span>
                                                            </td>
                                                            <td>{{ isset($client->clientRoute) ? $client->clientRoute->count() : '0' }}
                                                            </td>
                                                            <td>
                                                                <div class="dropdown">
                                                                    <button class="dropdown-toggle" type="button"
                                                                        id="dropdownMenuButton12"
                                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                                        <i class="fa-solid fa-ellipsis"></i>
                                                                    </button>
                                                                    <ul class="dropdown-menu"
                                                                        aria-labelledby="dropdownMenuButton12">
                                                                        @can('clients-list')
                                                                            <li><a class="dropdown-item"
                                                                                    href="{{ route('clients.show', [$client->id]) }}">View</a>
                                                                            </li>
                                                                        @endcan
                                                                    </ul>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>

                                            {{-- Hidden container for potential clients child rows --}}
                                            <div id="childRowsContainerPotential" style="display: none;">
                                                <table class="potential_clients_child_table">
                                                    <tbody>
                                                        @foreach ($clients->where('status', 0)->where('is_child', 0) as $client)
                                                            @foreach ($client->childClients as $child)
                                                                <tr class="child-row"
                                                                    data-parent-id="{{ $client->id }}"
                                                                    data-client-type="{{ $child->client_type ?? '' }}"
                                                                    data-payment-type="{{ $child->payment_type ?? '' }}"
                                                                    data-route-name="{{ optional($child->clientRoute->first())->name ?? '' }}">
                                                                    <td></td>
                                                                    <td>
                                                                        <div class="td_img_wrapper">
                                                                            <img src="{{ asset('website') }}/{{ $child->staff->profile->pic ?? 'users/no_avatar.jpg' }}"
                                                                                alt="No Image">
                                                                        </div>
                                                                        {{ $child->staff->name ?? '' }}
                                                                    </td>
                                                                    <td>{{ $child->name ?? '' }}</td>
                                                                    <td>{{ $child->parentClient->name ?? '-' }}</td>
                                                                    <td class="d-none">{{ $child->created_at }}</td>
                                                                    <td>
                                                                        {{ $child->formatted_phone ?? '--' }}
                                                                    </td>
                                                                    <td><span
                                                                            class="blue_color_td_span">{{ ucfirst($child->schedule ?? '') }}</span>
                                                                    </td>
                                                                    <td>{{ isset($child->clientRoute) ? $child->clientRoute->count() : '0' }}
                                                                    </td>
                                                                    <td>
                                                                        <div class="dropdown">
                                                                            <button class="dropdown-toggle" type="button"
                                                                                id="dropdownMenuButton-potential-{{ $child->id }}"
                                                                                data-bs-toggle="dropdown"
                                                                                aria-expanded="false">
                                                                                <i class="fa-solid fa-ellipsis"></i>
                                                                            </button>
                                                                            <ul class="dropdown-menu"
                                                                                aria-labelledby="dropdownMenuButton-potential-{{ $child->id }}">
                                                                                @can('clients-list')
                                                                                    <li><a class="dropdown-item"
                                                                                            href="{{ route('clients.show', [$child->id]) }}">View</a>
                                                                                    </li>
                                                                                @endcan
                                                                            </ul>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        @endforeach
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
            </div>
        </section>
    @elseif(auth()->user()->hasRole('staff'))
        <section class="client_management staff_manag ">
            <div class="container-fluid custom_container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="custom_div">
                            <div class="clients_tab custom_justify_between">
                                <ul class="nav nav-pills" id="pills-tab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="pills-clients-tab" data-bs-toggle="pill"
                                            data-bs-target="#pills-clients" type="button" role="tab"
                                            aria-controls="pills-clients" aria-selected="true">Clients</button>
                                    </li>
                                </ul>
                                <div class="create_btn custom_flex">
                                    <div class="sorting_filtering_wrapper">
                                        <select class="form-select sortFilter" id="sortByNamePotential"
                                            aria-label="Default select example">
                                            <option value="recent">Most Recent</option>
                                            <option value="az">A To Z</option>
                                        </select>
                                    </div>

                                    <div class="sorting_filtering_wrapper">
                                        <select class="form-select routeFilter" id="sortByRouteStaff"
                                            aria-label="Default select example">
                                            <option value="all">Routes</option>
                                            @foreach ($routes as $route)
                                                <option value="{{ $route->id }}">{{ $route->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>


                                    @can('clients-create')
                                        <a class="btn btn-primary" href="{{ route('clients.create') }}">Create Client</a>
                                    @endcan
                                </div>
                            </div>
                            <div class="tab-content" id="pills-tabContent">
                                <div class="tab-pane fade show active" id="pills-clients" role="tabpanel"
                                    aria-labelledby="pills-clients-tab" tabindex="0">
                                    <div class="custom_table">
                                        <div class="table-responsive">
                                            <table class="table clients_table datatable">
                                                <thead>
                                                    <tr>
                                                        <th></th>
                                                        <th style="width: 18%;">Client Name</th>
                                                        <th class="d-none">most_recent</th>
                                                        <th style="width: 20%;">Phone</th>
                                                        <th>Address</th>
                                                        <th>City</th>
                                                        <th>Assigned Route</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($clients->where('staff_id', auth()->id())->where('is_child', 0) as $client)
                                                        @php
                                                            $parentRouteNameStaff =
                                                                optional($client->clientRoute->first())->name ?? '';
                                                            $childSearchBlobStaff = $client->childClients
                                                                ->map(function ($c) {
                                                                    $parts = array_filter([
                                                                        $c->name ?? '',
                                                                        $c->formatted_phone ?? '',
                                                                        $c->address ?? '',
                                                                        $c->city ??
                                                                            ($c->user?->profile?->city ?? ''),
                                                                        optional($c->clientRoute->first())->name ?? '',
                                                                    ]);
                                                                    return \Illuminate\Support\Str::lower(
                                                                        trim(implode(' ', $parts)),
                                                                    );
                                                                })
                                                                ->filter()
                                                                ->implode(' ');
                                                        @endphp
                                                        <tr class="parent-row {{ $client->childClients && $client->childClients->count() > 0 ? 'has-children' : '' }}"
                                                            data-client-id="{{ $client->id }}"
                                                            data-client-type="{{ $client->client_type ?? '' }}"
                                                            data-payment-type="{{ $client->payment_type ?? '' }}"
                                                            data-parent-route="{{ $parentRouteNameStaff }}"
                                                            data-child-search="{{ e($childSearchBlobStaff) }}"
                                                            data-child-routes="{{ $client->childClients->map(fn($c) => optional($c->clientRoute->first())->name)->filter()->implode('||') }}">
                                                            <td>
                                                                @if ($client->childClients && $client->childClients->count() > 0)
                                                                    <button class="expand-btn" type="button"
                                                                        data-parent-id="{{ $client->id }}">
                                                                        <i class="fa-solid fa-plus expand-icon"></i>
                                                                    </button>
                                                                @endif
                                                            </td>
                                                            <td>{{ $client->name ?? '' }}</td>
                                                            @php
                                                                $clientUpdated =
                                                                    $client->updated_at ?? $client->created_at;
                                                                $scheduleUpdated = $client->clientSchedule->max(
                                                                    'updated_at',
                                                                );
                                                                $branchUpdated = $client->childClients->max(
                                                                    'updated_at',
                                                                );
                                                                $mostRecent = collect([
                                                                    $clientUpdated,
                                                                    $scheduleUpdated,
                                                                    $branchUpdated,
                                                                ])
                                                                    ->filter()
                                                                    ->max();
                                                            @endphp
                                                            <td class="d-none">{{ $mostRecent }}</td>
                                                            <td>
                                                                {{ $client->formatted_phone ?? '--' }}
                                                            </td>
                                                            <td>{{ $client->address ?? '-' }}
                                                            </td>
                                                            <td>{{ $client->city ?? '-' }}
                                                            </td>
                                                            <td>{{ optional($client->clientRoute->first())->name ?? '0' }}
                                                            </td>
                                                            <td>
                                                                <span
                                                                    class="badge {{ $client->status ? 'bg-success' : 'bg-danger' }}">
                                                                    {{ $client->status ? 'Active' : 'Inactive' }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <div class="dropdown">
                                                                    <button class="dropdown-toggle" type="button"
                                                                        id="dropdownMenuButton11"
                                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                                        <i class="fa-solid fa-ellipsis"></i>
                                                                    </button>
                                                                    <ul class="dropdown-menu"
                                                                        aria-labelledby="dropdownMenuButton11">
                                                                        <li><a class="dropdown-item"
                                                                                href="{{ route('clients.show', [$client->id]) }}">View</a>
                                                                        </li>
                                                                        <li><a class="dropdown-item"
                                                                                href="{{ route('clients.edit', $client->id) }}">Edit</a>
                                                                        </li>
                                                                        @if ($client->clientRouteStaff && $client->clientRouteStaff->count() > 0)
                                                                            @if ($client->status == 1)
                                                                                @if ($client->service_frequency)
                                                                                    <li>
                                                                                        <a class="dropdown-item"
                                                                                            href="{{ route('client-schedule', [$client->id]) }}">Schedule</a>
                                                                                    </li>
                                                                                @else
                                                                                    <li><a class="dropdown-item"
                                                                                            href="{{ route('clients.edit', $client->id) }}">Assign
                                                                                            Frequency</a>
                                                                                    </li>
                                                                                @endif
                                                                            @endif
                                                                        @else
                                                                            <li><a class="dropdown-item"
                                                                                    href="{{ route('clients.edit', $client->id) }}">Assign
                                                                                    Route </a>
                                                                            </li>
                                                                        @endif
                                                                        @if ($client->status == 0)
                                                                            <li>
                                                                                {!! Form::open(['method' => 'DELETE', 'route' => ['clients.destroy', $client->id], 'class' => 'delete-form']) !!}
                                                                                <a class="dropdown-item"
                                                                                    href="javascript:void(0)"
                                                                                    onclick="showDeleteConfirmation(this)">
                                                                                    Delete
                                                                                </a>
                                                                                {!! Form::close() !!}
                                                                            </li>
                                                                        @endif
                                                                    </ul>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>

                                            {{-- Hidden container for child rows (outside DataTables) --}}
                                            <div id="childRowsContainerStaff" style="display: none;">
                                                <table class="potential_clients_child_table">
                                                    <tbody>
                                                        @foreach ($clients->where('staff_id', auth()->id())->where('is_child', 0) as $client)
                                                            @foreach ($client->childClients as $child)
                                                                <tr class="child-row"
                                                                    data-parent-id="{{ $client->id }}"
                                                                    data-client-type="{{ $child->client_type ?? '' }}"
                                                                    data-payment-type="{{ $child->payment_type ?? '' }}"
                                                                    data-route-name="{{ optional($child->clientRoute->first())->name ?? '' }}"
                                                                    style="display: none; background-color: #f8f9fa;">
                                                                    <td></td>
                                                                    <td>{{ $child->name ?? '' }}</td>
                                                                    <td class="d-none">{{ $child->created_at }}</td>
                                                                    <td>
                                                                        {{ $child->formatted_phone ?? '--' }}
                                                                    </td>
                                                                    <td>{{ $child->address ?? ($child->user->profile->address ?? '--') }}
                                                                    </td>
                                                                    <td>{{ $child->city ?? ($child->user->profile->city ?? '--') }}
                                                                    </td>
                                                                    <td>{{ optional($child->clientRoute->first())->name ?? '0' }}
                                                                    </td>
                                                                    <td>
                                                                        <span
                                                                            class="badge {{ $child->status ? 'bg-success' : 'bg-danger' }}">
                                                                            {{ $child->status ? 'Active' : 'Inactive' }}
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        <div class="dropdown">
                                                                            <button class="dropdown-toggle" type="button"
                                                                                id="dropdownMenuButton-child-{{ $child->id }}"
                                                                                data-bs-toggle="dropdown"
                                                                                aria-expanded="false">
                                                                                <i class="fa-solid fa-ellipsis"></i>
                                                                            </button>
                                                                            <ul class="dropdown-menu"
                                                                                aria-labelledby="dropdownMenuButton-child-{{ $child->id }}">
                                                                                <li><a class="dropdown-item"
                                                                                        href="{{ route('clients.show', [$child->id]) }}">View</a>
                                                                                </li>
                                                                                <li><a class="dropdown-item"
                                                                                        href="{{ route('branch.edit', $child->id) }}">Edit</a>
                                                                                </li>
                                                                                @if ($child->clientRouteStaff && $child->clientRouteStaff->count() > 0)
                                                                                    @if ($child->status == 1)
                                                                                        @if ($client->service_frequency)
                                                                                            <li><a class="dropdown-item"
                                                                                                    href="{{ route('client-schedule', [$client->id]) }}">Schedule</a>
                                                                                            </li>
                                                                                        @else
                                                                                            <li><a class="dropdown-item"
                                                                                                    href="{{ route('clients.edit', $client->id) }}">Assign
                                                                                                    Frequency</a>
                                                                                            </li>
                                                                                        @endif
                                                                                    @endif
                                                                                @else
                                                                                    <li><a class="dropdown-item"
                                                                                            href="{{ route('branch.edit', $child->id) }}">Assign
                                                                                            Route </a>
                                                                                    </li>
                                                                                @endif
                                                                                @if ($child->status == 0)
                                                                                    <li>
                                                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['clients.destroy', $child->id], 'class' => 'delete-form']) !!}
                                                                                        <a class="dropdown-item"
                                                                                            href="javascript:void(0)"
                                                                                            onclick="showDeleteConfirmation(this)">
                                                                                            Delete
                                                                                        </a>
                                                                                        {!! Form::close() !!}
                                                                                    </li>
                                                                                @endif
                                                                            </ul>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        @endforeach
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
            </div>
        </section>
    @endif
@endsection
@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <script>
        $(document).ready(function() {
            var currentSearchVal = '';
            var currentRouteFilter = '';
            var currentClientTypeFilter = '';
            var currentPaymentTypeFilter = '';
            var expandedParentIds = new Set();

            function resolveBranchContainer($table) {
                if ($table.hasClass('potential_clients_table')) {
                    return '#childRowsContainerPotential';
                }
                if ($('#childRowsContainerStaff').length) {
                    return '#childRowsContainerStaff';
                }
                return '#childRowsContainer';
            }

            /** Branch rows must be clones: DataTables draw() drops non-model <tr> from the DOM. */
            function restoreBranchesAfterDraw(dtApi) {
                var $table = $(dtApi.table().node());
                var containerId = resolveBranchContainer($table);

                $table.find('tbody tr.branch-row-inserted').remove();

                var searchVal = currentSearchVal;
                $table.find('tbody tr.parent-row').each(function() {
                    var $parent = $(this);
                    var parentId = String($parent.attr('data-client-id') || '');
                    if (!parentId) return;

                    var childSearch = ($parent.attr('data-child-search') || '').toLowerCase();
                    var matchSearch = !!(searchVal && childSearch.indexOf(searchVal) !== -1);
                    var manuallyExpanded = expandedParentIds.has(parentId);
                    if (!manuallyExpanded && !matchSearch) return;

                    var $tplRows = $(containerId + ' tbody tr.child-row[data-parent-id="' + parentId +
                        '"]');
                    if (!$tplRows.length) return;

                    var $insertAfter = $parent;
                    $tplRows.each(function() {
                        var $clone = $(this).clone(true, true);
                        $clone.addClass('branch-row-inserted');
                        $clone.insertAfter($insertAfter);
                        $insertAfter = $clone;
                    });

                    var $icon = $parent.find('.expand-icon');
                    $icon.removeClass('fa-plus').addClass('fa-minus');
                    $parent.find('.expand-btn').attr('aria-expanded', 'true');

                    var $inserted = $table.find('tr.branch-row-inserted[data-parent-id="' + parentId + '"]');
                    if (currentRouteFilter) {
                        $inserted.each(function() {
                            var cr = ($(this).attr('data-route-name') || '').trim();
                            $(this).toggle(cr === currentRouteFilter);
                        });
                    } else {
                        $inserted.show();
                    }
                });
            }

            var CLIENT_SORT_STORAGE_KEY = 'clientSortPreference';

            function readClientSortPreference() {
                var v = localStorage.getItem(CLIENT_SORT_STORAGE_KEY);
                return (v === 'az' || v === 'recent') ? v : 'recent';
            }

            function persistClientSortPreference(sortType) {
                if (sortType === 'az' || sortType === 'recent') {
                    localStorage.setItem(CLIENT_SORT_STORAGE_KEY, sortType);
                }
            }

            /** Keeps admin + staff sort dropdowns aligned with saved preference */
            function syncSortDropdowns() {
                $('.sortFilter').val(readClientSortPreference());
            }

            function applySortOrderToTables(sortType) {
                if (sortType === 'az') {
                    if (clientsTable) clientsTable.order([1, 'asc']).draw();
                    if (potentialTable) potentialTable.order([2, 'asc']).draw();
                } else {
                    if (clientsTable) clientsTable.order([2, 'desc']).draw();
                    if (potentialTable) potentialTable.order([3, 'desc']).draw();
                }
            }

            // Export to Excel (unchanged)
            $(document).on('click', '#exportExcel', function() {
                let exportData = [];

                Swal.fire({
                    title: 'Loading...',
                    html: 'Fetching data for export',
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: "{{ route('clients.export') }}",
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        exportData = response;

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

                        let fileName = 'Clients_Data.xlsx';

                        // Create worksheet from data
                        let ws = XLSX.utils.json_to_sheet(exportData);

                        // Set column widths (same as Staff Routes style)
                        const columnWidths = [{
                                wch: 25
                            }, // Client Name
                            {
                                wch: 30
                            }, // Email
                            {
                                wch: 20
                            }, // Phone
                            {
                                wch: 15
                            }, // Street Number
                            {
                                wch: 35
                            }, // Address
                            {
                                wch: 20
                            }, // City
                            {
                                wch: 12
                            }, // Zip Code
                            {
                                wch: 15
                            }, // Client Type
                            {
                                wch: 15
                            }, // Payment Type
                            {
                                wch: 40
                            } // Job Description
                        ];
                        ws['!cols'] = columnWidths;

                        // Get the range of the worksheet
                        const range = XLSX.utils.decode_range(ws['!ref']);

                        // Apply wrap text to all cells first
                        const wrapStyle = {
                            alignment: {
                                wrapText: true,
                                vertical: "top",
                                horizontal: "left"
                            }
                        };

                        for (let row = range.s.r; row <= range.e.r; row++) {
                            for (let col = range.s.c; col <= range.e.c; col++) {
                                const cellRef = XLSX.utils.encode_cell({
                                    r: row,
                                    c: col
                                });
                                if (ws[cellRef]) {
                                    if (ws[cellRef].s) {
                                        ws[cellRef].s.alignment = {
                                            ...ws[cellRef].s.alignment,
                                            wrapText: true,
                                            vertical: "top"
                                        };
                                    } else {
                                        ws[cellRef].s = wrapStyle;
                                    }
                                }
                            }
                        }

                        // Header style (Green background like Staff Routes)
                        const headerStyle = {
                            font: {
                                bold: true,
                                sz: 10,
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

                        // Alternating row colors (light grey) - for data rows
                        const lightGreyStyle = {
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

                        const whiteStyle = {
                            alignment: {
                                wrapText: true,
                                vertical: "top",
                                horizontal: "left"
                            }
                        };

                        // Apply header style to first row
                        for (let col = range.s.c; col <= range.e.c; col++) {
                            const cellRef = XLSX.utils.encode_cell({
                                r: 0,
                                c: col
                            });
                            if (ws[cellRef]) {
                                ws[cellRef].s = headerStyle;
                            }
                        }

                        // Apply alternating colors to data rows (starting from row 1)
                        for (let row = range.s.r + 1; row <= range.e.r; row++) {
                            const isEvenRow = (row - 1) % 2 === 0;
                            const style = isEvenRow ? whiteStyle : lightGreyStyle;

                            for (let col = range.s.c; col <= range.e.c; col++) {
                                const cellRef = XLSX.utils.encode_cell({
                                    r: row,
                                    c: col
                                });
                                if (ws[cellRef]) {
                                    ws[cellRef].s = style;
                                }
                            }
                        }

                        // Set row heights
                        if (!ws['!rows']) ws['!rows'] = [];
                        ws['!rows'][0] = {
                            hpt: 25
                        }; // Header row height

                        let wb = XLSX.utils.book_new();
                        XLSX.utils.book_append_sheet(wb, ws, 'Clients Data');

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

            // Expand/Collapse: insert clones only — originals stay in hidden container for search + redraw safety.
            $(document).on('click', '.expand-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();

                var $btn = $(this);
                var $icon = $btn.find('.expand-icon');
                var parentId = String($btn.attr('data-parent-id') || '');
                var $parentRow = $btn.closest('tr.parent-row');
                var $table = $btn.closest('table');
                var containerId = resolveBranchContainer($table);
                var $childTemplates = $(containerId + ' tbody tr.child-row[data-parent-id="' + parentId + '"]');

                if ($icon.hasClass('fa-plus')) {
                    expandedParentIds.add(parentId);
                    var $insertAfter = $parentRow;
                    $childTemplates.each(function() {
                        var $clone = $(this).clone(true, true);
                        $clone.addClass('branch-row-inserted');
                        $clone.insertAfter($insertAfter);
                        $insertAfter = $clone;
                    });
                    $icon.removeClass('fa-plus').addClass('fa-minus');
                    $btn.attr('aria-expanded', 'true');
                    var $inserted = $table.find('tr.branch-row-inserted[data-parent-id="' + parentId + '"]');
                    if (currentRouteFilter) {
                        $inserted.each(function() {
                            var childRoute = ($(this).attr('data-route-name') || '').trim();
                            $(this).toggle(childRoute === currentRouteFilter);
                        });
                    } else {
                        $inserted.show();
                    }
                } else {
                    expandedParentIds.delete(parentId);
                    $icon.removeClass('fa-minus').addClass('fa-plus');
                    $btn.attr('aria-expanded', 'false');
                    $table.find('tr.branch-row-inserted[data-parent-id="' + parentId + '"]').remove();
                }
            });



            // Auto-collapse all child rows on tab switch
            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                $('.collapse').collapse('hide');
                // Re-adjust DataTables columns after tab switch (fixes layout issues)
                if ($.fn.DataTable.isDataTable('.clients_table')) {
                    $('.clients_table').DataTable().columns.adjust().draw();
                }
                if ($.fn.DataTable.isDataTable('.potential_clients_table')) {
                    $('.potential_clients_table').DataTable().columns.adjust().draw();
                }
            });

            // Initialize DataTables on tab show (prevents init on hidden tabs)
            function initClientsTable() {
                if ($.fn.DataTable.isDataTable('.clients_table')) {
                    $('.clients_table').DataTable().destroy();
                }
                var savedSort = readClientSortPreference();
                var initialOrder = savedSort === 'az' ? [
                    [1, 'asc']
                ] : [
                    [2, 'desc']
                ];

                return $('.clients_table').DataTable({
                    searching: true,
                    bLengthChange: false,
                    paging: true,
                    info: true,
                    ordering: true,
                    order: initialOrder,
                    destroy: true, // Allow re-initialization
                    columnDefs: [{
                            orderable: false,
                            targets: 0
                        }, // Expand column non-sortable
                        {
                            targets: 1,
                            type: "string"
                        }, // Client Name
                        {
                            targets: 2,
                            type: "date"
                        } // created_at (hidden)
                    ],
                    // Exclude child rows from DataTables processing
                    createdRow: function(row, data, dataIndex) {
                        if ($(row).hasClass('child-row')) {
                            $(row).addClass('dt-ignore');
                        }
                    },
                    drawCallback: function() {
                        restoreBranchesAfterDraw(this.api());
                    }
                });
            }

            function initPotentialTable() {
                var $pot = $('.potential_clients_table');
                if (!$pot.length) {
                    return null;
                }
                if ($.fn.DataTable.isDataTable($pot)) {
                    $pot.DataTable().destroy();
                }
                var savedSort = readClientSortPreference();
                var initialOrder = savedSort === 'az' ? [
                    [2, 'asc']
                ] : [
                    [3, 'desc']
                ];

                return $pot.DataTable({
                    searching: true,
                    bLengthChange: false,
                    paging: true,
                    info: true,
                    ordering: true,
                    order: initialOrder,
                    destroy: true, // Allow re-initialization
                    columnDefs: [{
                            orderable: false,
                            targets: 0
                        }, // Expand column non-sortable
                        {
                            targets: 2,
                            type: "string"
                        }, // Client Name
                        {
                            targets: 4,
                            type: "date"
                        } // created_at (hidden)
                    ],
                    createdRow: function(row, data, dataIndex) {
                        if ($(row).hasClass('child-row')) {
                            $(row).addClass('dt-ignore');
                        }
                    },
                    drawCallback: function() {
                        restoreBranchesAfterDraw(this.api());
                    }
                });
            }

            // Init on first tab load
            var clientsTable = initClientsTable();
            var potentialTable = initPotentialTable();

            // Custom search (built-in DataTables search only sees parent columns; branches use data-child-search).
            $('.custom_search_box').on("input", function() {
                currentSearchVal = $(this).val().toLowerCase().trim();
                if (clientsTable) clientsTable.draw();
                if (potentialTable) potentialTable.draw();
            });

            if (!window.__clientListExtSearchBound) {
                window.__clientListExtSearchBound = true;
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    var $tbl = $(settings.nTable);
                    if (!$tbl.hasClass('clients_table') && !$tbl.hasClass('potential_clients_table')) {
                        return true;
                    }

                    var $row = $(settings.aoData[dataIndex].nTr);
                    if (!$row.hasClass('parent-row')) return true;

                    if (currentRouteFilter) {
                        var parentRoute = ($row.attr('data-parent-route') || '').trim();
                        var childRoutes = ($row.attr('data-child-routes') || '').split('||').map(function(r) {
                            return r.trim();
                        });
                        var routeMatch = parentRoute === currentRouteFilter ||
                            childRoutes.indexOf(currentRouteFilter) !== -1;
                        if (!routeMatch) return false;
                    }

                    if (currentClientTypeFilter) {
                        var rowClientType = ($row.attr('data-client-type') || '').trim();
                        if (rowClientType !== currentClientTypeFilter) return false;
                    }

                    if (currentPaymentTypeFilter) {
                        var rowPaymentType = ($row.attr('data-payment-type') || '').trim();
                        if (rowPaymentType !== currentPaymentTypeFilter) return false;
                    }

                    var searchVal = currentSearchVal;
                    if (!searchVal) return true;

                    var rowText = data.join(' ').toLowerCase();
                    if (rowText.indexOf(searchVal) !== -1) return true;

                    var childSearch = ($row.attr('data-child-search') || '').toLowerCase();
                    return childSearch.indexOf(searchVal) !== -1;
                });
            }

            // Dropdowns match localStorage (DataTables already use readClientSortPreference() in init)
            syncSortDropdowns();
            $('#sortByRoute, #sortByRouteStaff').val('all'); // Always start with "All Routes"

            // Make sure all rows are visible on initial load (no route filter)
            setTimeout(function() {
                currentRouteFilter = '';
                if (clientsTable) {
                    clientsTable.draw();
                }
                if (potentialTable) {
                    potentialTable.draw();
                }
            }, 100);

            $(document).on('change', '.sortFilter', function() {
                var sortType = $(this).val();
                persistClientSortPreference(sortType);
                $('.sortFilter').val(sortType);
                applySortOrderToTables(sortType);
            });

            $(window).on('pageshow', function(e) {
                if (e.originalEvent && e.originalEvent.persisted) {
                    syncSortDropdowns();
                    applySortOrderToTables(readClientSortPreference());
                }
            });

            // Route filter function
            function applyRouteFilter(routeId) {
                if (routeId === 'all') {
                    currentRouteFilter = '';
                } else {
                    currentRouteFilter = $('#sortByRoute option:selected').text().trim();
                    if (!currentRouteFilter) {
                        currentRouteFilter = $('#sortByRouteStaff option:selected').text().trim();
                    }
                }
                if (clientsTable) clientsTable.draw();
                if (potentialTable) potentialTable.draw();
            }

            // Route filter change handler for admin
            // Note: Route filter is NOT saved to localStorage to avoid hiding clients on page reload
            $('#sortByRoute').on('change', function() {
                var routeId = $(this).val();
                currentRouteFilter = routeId === 'all' ? '' : $(this).find('option:selected').text().trim();
                if (clientsTable) clientsTable.draw();
                if (potentialTable) potentialTable.draw();
            });

            // Client Type filter change handler (without reload)
            $('#filterByClientType').on('change', function() {
                currentClientTypeFilter = $(this).val() || '';
                if (clientsTable) clientsTable.draw();
                if (potentialTable) potentialTable.draw();
            });

            // Payment Type filter change handler (without reload)
            $('#filterByPaymentType').on('change', function() {
                currentPaymentTypeFilter = $(this).val() || '';
                if (clientsTable) clientsTable.draw();
                if (potentialTable) potentialTable.draw();
            });

            // Route filter change handler for staff
            $('#sortByRouteStaff').on('change', function() {
                var routeId = $(this).val();
                currentRouteFilter = routeId === 'all' ? '' : $(this).find('option:selected').text().trim();
                if (clientsTable) clientsTable.draw();
            });

            // Re-init on tab shown (for layout/visibility)
            $('#pills-clients-tab').on('shown.bs.tab', function() {
                clientsTable = initClientsTable();
                syncSortDropdowns();
                if (clientsTable) clientsTable.draw();
            });
            $('#pills-potential_clients-tab').on('shown.bs.tab', function() {
                potentialTable = initPotentialTable();
                syncSortDropdowns();
                if (potentialTable) potentialTable.draw();
            });
        });
    </script>
@endpush
