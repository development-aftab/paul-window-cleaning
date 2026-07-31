@extends('theme.layout.master')

@push('css')
@endpush
@section('navbar-title')
    <div class="custom_justify_between create_clients_navbar">

        <a href="{{ url('clients') }}" class="back_btn_navbar">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h2 class="navbar_PageTitle">Client Detail`s</h2>

    </div>
@endsection
@section('content')
    @if (auth()->user()->hasRole('admin'))
        <section class="client_details">
            <div class="container-fluid custom_container">
                <div class="row custom_row">
                    <div class="col-md-12">
                        <div class="shadow_box_wrapper">
                            <div class="custom_details_wrapper">
                                <div class="client_info">
                                    <div class="row custom_row">
                                        @if ($client->staff && $client->staff->id)
                                            <div class="col-md-12">
                                                <div class="potential_clients custom_justify_between">
                                                    <div class="potential_info custom_flex">
                                                        <div class="client_img">
                                                            <img src="{{ asset('website') }}/{{ $client->staff->profile->pic ?? 'users/no_avatar.jpg' }}"
                                                                alt="No Image">
                                                        </div>
                                                        <div class="custom_detail">
                                                            <h5>{{ $client->staff->name ?? '' }}</h5>
                                                            <div class="client_contacts custom_flex">
                                                                <span><i class="fa-solid fa-envelope"></i>{{ $client->staff->email ?? '' }}</span>
                                                                <span><i class="fa-solid fa-phone"></i>{{ $client->staff->profile->phone ?? 'Not Available' }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @if ($client->status == 0)
                                                        <div class="accept_reject_btn custom_flex">
                                                            <form id="accept-form"
                                                                action="{{ route('staff_accept_status', $client->id) }}"
                                                                method="POST">
                                                                @csrf
                                                                <input type="hidden" name="commission_percentage"
                                                                    id="commission_percentage"
                                                                    value="{{ $client->commission_percentage ?? '' }}">
                                                                <input type="hidden" name="accept_branches"
                                                                    id="accept_branches" value="0">
                                                                <button class="btn_global btn_green" type="button"
                                                                    id="acceptBtn">
                                                                    Accept <i class="fa-solid fa-check"></i>
                                                                </button>
                                                            </form>

                                                            {{-- <button class="btn_global btn_red" type="button">Reject<i
                                                                    class="fa-solid fa-close"></i></button> --}}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                        <div class="col-md-12">
                                            <div class="custom_justify_between">
                                                <h2>{{ $client->name ?? (optional($client->user)->name ?? '') }}</h2>
                                                <div class="edit_btn">
                                                    <a class="btn_global btn_sky_blue"
                                                        href="{{ route('clients.edit', $client->id) }}">Edit<i
                                                            class="fa-solid fa-pen-to-square"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="txt_field_wrapper">
                                                <label>Email :</label>
                                                <span>{{ $client->contact_email ?? 'Not Available' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="txt_field_wrapper">
                                                <label>Phone :</label>
                                                <span>
                                                    @php
                                                        $phone = $client->contact_phone ?? '';
                                                        if ($phone && strlen($phone) == 10) {
                                                            echo substr($phone, 0, 3) .
                                                                '-' .
                                                                substr($phone, 3, 3) .
                                                                '-' .
                                                                substr($phone, 6, 4);
                                                        } else {
                                                            echo $phone ?: 'Not Available';
                                                        }
                                                    @endphp
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="txt_field_wrapper">
                                                <label>Date Created :</label>
                                                <span>{{ $client->created_at->format('m-d-Y') ?? '' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="txt_field_wrapper">
                                                <label>Commission :</label>
                                                <span>{{ $client->commission_percentage . '%' ?? '' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="client_location">
                                    <div class="row custom_row">
                                        @if ($client->parent_id)
                                            <div class="col-md-12">
                                                <h3>Parent Company</h3>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="txt_field_wrapper">
                                                    <label>Parent Name :</label>
                                                    <span>{{ $client->parentClient->name ?? 'Not Available' }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="txt_field_wrapper">
                                                    <label>Parent Phone Number :</label>
                                                    <span>{{ $client->parentClient->contact_phone ?? 'Not Available' }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="txt_field_wrapper">
                                                    <label>Parent Email :</label>
                                                    <span>{{ $client->parentClient->contact_email ?? 'Not Available' }}</span>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="txt_field_wrapper">
                                                    <label>Parent Adress :</label>
                                                    <span>{{ $client->parentClient->address ?? 'Not Available' }}</span>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="col-md-12">
                                            <h3>Location</h3>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="txt_field_wrapper">
                                                <label>Address :</label>
                                                <span>{{ $client->house_no }} {{ $client->address ?? '--' }} </span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="txt_field_wrapper">
                                                <label>State :</label>
                                                <span>{{ $client->state ?? 'Not Available' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="txt_field_wrapper">
                                                <label>City :</label>
                                                <span>{{ $client->city ?? 'Not Available' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="txt_field_wrapper">
                                                <label>ZipCode :</label>
                                                <span>{{ $client->postal ?? 'Not Available' }}</span>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="client_service_detail">
                                    <div class="row custom_row">
                                        <div class="col-md-4">
                                            <div class="custom_justify_between">
                                                <h3>Price</h3>
                                            </div>
                                            <div class="custom_justify_between">
                                                <div class="custom_service_time">
                                                    @forelse($client->clientPrice as $schedule)
                                                        <span class="secondary">{{ $schedule->name ?? '' }} :
                                                            ${{ $schedule->value ?? '' }}</span>
                                                    @empty
                                                        <span>No Data Available</span>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="custom_justify_between">
                                                <h3>Frequency of Service</h3>
                                            </div>
                                            <div class="">
                                                @if ($client->service_frequency == 'normalWeek')
                                                    <span class="secondary">Weekly Cycle</span>
                                                @elseif($client->service_frequency == 'monthly')
                                                    <span class="secondary">Monthly Cycle</span>
                                                @elseif($client->service_frequency == 'biMonthly')
                                                    <span class="secondary">Bi-Monthly Cycle</span>
                                                @elseif($client->service_frequency == 'eightWeek')
                                                    <span class="secondary">Eight Weeks Cycle</span>
                                                @elseif($client->service_frequency == 'quarterly')
                                                    <span class="secondary">12 Weeks Cycle</span>
                                                @elseif($client->service_frequency == 'biAnnually')
                                                    <span class="secondary">Bi-Annually Cycle</span>
                                                @elseif($client->service_frequency == 'annually')
                                                    <span class="secondary">Annually Cycle</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <h3>Payment Type</h3>
                                            <span class="secondary">{{ ucfirst($client->payment_type) ?? '' }}</span>
                                        </div>
                                        <div class="col-md-4">
                                            <h3>Best time to service</h3>
                                            @forelse ($client->clientHour as $time)
                                                <div class="custom_service_time">
                                                    <span class="secondary">Start Hour
                                                        {{ $time->start_hour ?? 'Not Available' }} - End Hour
                                                        {{ $time->end_hour ?? 'Not Available' }}</span>
                                                </div>
                                            @empty
                                                <span>No Data Available</span>
                                            @endforelse
                                        </div>
                                        <div class="col-md-4">
                                            <h3>Assigned Route</h3>
                                            @forelse ($client->clientRoute as $route)
                                                <span class="secondary">{{ $route->name ?? '' }}</span>
                                            @empty
                                                <span>No Route Assigned</span>
                                            @endforelse
                                        </div>
                                        <div class="col-md-4">
                                            <h3>Client Type</h3>
                                            <span class="secondary">{{ $client->client_type ?? 'Not Available' }}</span>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="unbelivable_sec">
                                                <h3>Closed</h3>
                                                <div class="custom_checkbox_wrapper unavailable_days">
                                                    @foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                                        <div class="custom_radio">
                                                            <input class="form-check-input" type="checkbox"
                                                                value="{{ $day }}" name="unavail_day[]"
                                                                id="{{ substr($day, 0, 3) }}"
                                                                @if ($client->clientDay->contains('day', $day)) checked @endif disabled>
                                                            <label
                                                                for="{{ substr($day, 0, 3) }}">{{ ucfirst($day) }}</label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="custom_div">
                            <div class="clients_tab">
                                <ul class="nav nav-pills" id="pills-tab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="pills-images-tab" data-bs-toggle="pill"
                                            data-bs-target="#pills-images" type="button" role="tab"
                                            aria-controls="pills-images" aria-selected="false">Image
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="pills-businessCard-tab" data-bs-toggle="pill"
                                            data-bs-target="#pills-businessCard" type="button" role="tab"
                                            aria-controls="pills-businessCard" aria-selected="false">Business Card
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            <div class="tab-content" id="pills-tabContent">
                                <div class="tab-pane fade" id="pills-images" role="tabpanel"
                                    aria-labelledby="pills-images-tab" tabindex="0">
                                    <div class="clients_detail_images">
                                        @foreach ($client->clientImage as $image)
                                            <div class="custom_images">
                                                <img src="{{ asset('website') }}/{{ $image->name ?? 'no-image.jpg' }}">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="pills-businessCard" role="tabpanel"
                                    aria-labelledby="pills-businessCard-tab" tabindex="0">
                                    <div class="business_card">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="business_card_images">
                                                    <h4>Business Card Front</h4>
                                                    <div class="custom_images">
                                                        <img
                                                            src="{{ asset('website') }}/{{ $client->front_image ?? 'no-image.jpg' }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="business_card_images">
                                                    <h4>Business Card Back</h4>
                                                    <div class="custom_images">
                                                        <img
                                                            src="{{ asset('website') }}/{{ $client->back_image ?? 'no-image.jpg' }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal for Commission Percentage -->
            <div class="modal" id="commissionModal" tabindex="-1" aria-labelledby="commissionModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="commissionModalLabel">Enter Commission Percentage</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="commissionInput">Commission Percentage</label>
                                <input type="number" class="form-control" id="commissionInput"
                                    name="commission_percentage" placeholder="Enter Commission Percentage">
                                <div id="commissionError" class="text-danger" style="display:none;">Please enter a valid
                                    commission percentage
                                </div>

                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="submitCommission">Submit</button>
                        </div>
                    </div>
                </div>
            </div>

        </section>
    @elseif(auth()->user()->hasRole('staff'))
        <section class="client_details">
            <div class="container-fluid custom_container">
                <div class="row custom_row">
                    <div class="col-md-12">
                        <div class="shadow_box_wrapper">
                            <div class="custom_details_wrapper">
                                <div class="client_info">
                                    <div class="row custom_row">
                                        @if ($client->staff && $client->staff->id)
                                            <div class="col-md-12">
                                                <div class="potential_clients custom_justify_between">
                                                    <div class="potential_info custom_flex">
                                                        <div class="client_img">
                                                            <img src="{{ asset('website') }}/{{ $client->staff->profile->pic ?? 'users/no_avatar.jpg' }}"
                                                                alt="No Image">
                                                        </div>
                                                        <div class="custom_detail">
                                                            <h5>{{ $client->staff->name ?? '' }}</h5>
                                                            <div class="client_contacts custom_flex">
                                                                <span><i
                                                                        class="fa-solid fa-envelope"></i>{{ $client->staff->email ?? 'Not Available' }}</span>
                                                                <span><i
                                                                        class="fa-solid fa-phone"></i>{{ $client->staff->profile->phone ?? 'Not Available' }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="col-md-12">
                                            <div class="custom_justify_between">
                                                <h2>{{ $client->name ?? 'Not Available' }}</h2>
                                                <div class="edit_btn">
                                                    <a class="btn_global btn_sky_blue"
                                                        href="{{ route('clients.edit', $client->id) }}">Edit<i
                                                            class="fa-solid fa-pen-to-square"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="txt_field_wrapper">
                                                <label>Email :</label>
                                                <span>{{ $client->contact_email ?? 'Not Available' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="txt_field_wrapper">
                                                <label>Phone :</label>
                                                <span>
                                                    @php
                                                        $phone = $client->contact_phone ?? '';
                                                        if ($phone && strlen($phone) == 10) {
                                                            echo substr($phone, 0, 3) .
                                                                '-' .
                                                                substr($phone, 3, 3) .
                                                                '-' .
                                                                substr($phone, 6, 4);
                                                        } else {
                                                            echo $phone ?: 'Not Available';
                                                        }
                                                    @endphp
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="txt_field_wrapper">
                                                <label>Date Created :</label>
                                                <span>{{ $client->created_at->format('m-d-Y') ?? 'Not Available' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="txt_field_wrapper">
                                                <label>Commission :</label>
                                                <span>{{ $client->commission_percentage . '%' ?? 'Not Available' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="client_location">
                                    <div class="row custom_row">
                                        @if ($client->parent_id)
                                            <div class="col-md-12">
                                                <h3>Parent Company</h3>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="txt_field_wrapper">
                                                    <label>Parent Name :</label>
                                                    <span>{{ $client->parentClient->name ?? 'Not Available' }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="txt_field_wrapper">
                                                    <label>Parent Phone Number :</label>
                                                    <span>{{ $client->parentClient->contact_phone ?? 'Not Available' }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="txt_field_wrapper">
                                                    <label>Parent Email :</label>
                                                    <span>{{ $client->parentClient->contact_email ?? 'Not Available' }}</span>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="txt_field_wrapper">
                                                    <label>Parent Adress :</label>
                                                    <span>{{ $client->parentClient->address ?? 'Not Available' }}</span>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="col-md-12">
                                            <h3>Location</h3>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="txt_field_wrapper">
                                                <label>Address :</label>
                                                <span>{{ $client->house_no }}
                                                    {{ $client->address ?? 'Not Available' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="txt_field_wrapper">
                                                <label>State :</label>
                                                <span>{{ $client->state ?? 'Not Available' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="txt_field_wrapper">
                                                <label>City :</label>
                                                <span>{{ $client->city ?? 'Not Available' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="txt_field_wrapper">
                                                <label>ZipCode :</label>
                                                <span>{{ $client->postal ?? 'Not Available' }}</span>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="client_service_detail">
                                    <div class="row custom_row">
                                        <div class="col-md-6">
                                            <div class="custom_justify_between">
                                                <h3>Payment Type</h3>
                                                <span class="primary">{{ $client->payment_type ?? '' }}</span>
                                            </div>
                                            <div class="custom_justify_between">
                                                <div class="custom_service_time">
                                                    @forelse($client->clientPrice as $schedule)
                                                        <span class="secondary">{{ $schedule->name ?? '' }} :
                                                            ${{ $schedule->value ?? '' }}</span>
                                                    @empty
                                                        <span>No Data Available</span>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="custom_justify_between">
                                                <h3>Frequency of Service</h3>
                                            </div>
                                            <div class="txt_field_wrapper">
                                                @if ($client->service_frequency == 'normalWeek')
                                                    <span class="secondary">Weekly Cycle</span>
                                                @elseif($client->service_frequency == 'monthly')
                                                    <span class="secondary">Monthly Cycle</span>
                                                @elseif($client->service_frequency == 'biMonthly')
                                                    <span class="secondary">Bi-Monthly Cycle</span>
                                                @elseif($client->service_frequency == 'eightWeek')
                                                    <span class="secondary">Eight Weeks Cycle</span>
                                                @elseif($client->service_frequency == 'quarterly')
                                                    <span class="secondary">12 Weeks Cycle</span>
                                                @elseif($client->service_frequency == 'biAnnually')
                                                    <span class="secondary">Bi-Annually Cycle</span>
                                                @elseif($client->service_frequency == 'annually')
                                                    <span class="secondary">Annually Cycle</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h3>Best time to service</h3>
                                            @forelse ($client->clientHour as $time)
                                                <div class="custom_service_time">
                                                    <span class="secondary">Start Hour
                                                        {{ $time->start_hour ?? 'Not Available' }} - End Hour
                                                        {{ $time->end_hour ?? 'Not Available' }}</span>
                                                </div>
                                            @empty
                                                <span>No Data Available</span>
                                            @endforelse
                                        </div>
                                        <div class="col-md-6">
                                            <h3>Assigned Route</h3>
                                            @forelse ($client->clientRoute as $route)
                                                <span class="primary">{{ $route->name ?? '' }}</span>
                                            @empty
                                                <span>No Route Assigned</span>
                                            @endforelse
                                        </div>
                                        <div class="col-md-6">
                                            <h3>Client Type</h3>
                                            <span>{{ $client->client_type ?? 'Not Available' }}</span>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="unbelivable_sec">
                                                <h3>Closed</h3>
                                                <div class="custom_checkbox_wrapper unavailable_days">
                                                    @foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                                        <div class="custom_radio">
                                                            <input class="form-check-input" type="checkbox"
                                                                value="{{ $day }}" name="unavail_day[]"
                                                                id="{{ substr($day, 0, 3) }}"
                                                                @if ($client->clientDay->contains('day', $day)) checked @endif disabled>
                                                            <label
                                                                for="{{ substr($day, 0, 3) }}">{{ ucfirst($day) }}</label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="custom_div">
                            <div class="clients_tab">
                                <ul class="nav nav-pills" id="pills-tab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="pills-images-tab" data-bs-toggle="pill"
                                            data-bs-target="#pills-images" type="button" role="tab"
                                            aria-controls="pills-images" aria-selected="false">Image
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="pills-businessCard-tab" data-bs-toggle="pill"
                                            data-bs-target="#pills-businessCard" type="button" role="tab"
                                            aria-controls="pills-businessCard" aria-selected="false">Business Card
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            <div class="tab-content" id="pills-tabContent">
                                <div class="tab-pane fade" id="pills-weeklyNotes" role="tabpanel"
                                    aria-labelledby="pills-weeklyNotes-tab" tabindex="0">
                                    <div class="custom_notes">
                                        @foreach (['one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight'] as $index => $weekName)
                                            @if ($week = $client->clientWeek->where('week', $weekName)->first())
                                                <div class="weekly_notes">
                                                    <h4>Week {{ $index + 1 }}</h4>
                                                    <p>{{ $week->note }}</p>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="pills-weeklyPrices" role="tabpanel"
                                    aria-labelledby="pills-weeklyPrices-tab" tabindex="0">
                                    <div class="custom_notes">
                                        @foreach (['one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight'] as $index => $weekName)
                                            @if ($week = $client->clientWeek->where('week', $weekName)->first())
                                                <div class="weekly_notes">
                                                    <h4>Week {{ $index + 1 }}</h4>
                                                    <p>${{ number_format($week->cost), 2 }}</p>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="pills-images" role="tabpanel"
                                    aria-labelledby="pills-images-tab" tabindex="0">
                                    <div class="clients_detail_images">
                                        @foreach ($client->clientImage as $image)
                                            <div class="custom_images">
                                                <img src="{{ asset('website') }}/{{ $image->name ?? 'Not Available' }}">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="pills-businessCard" role="tabpanel"
                                    aria-labelledby="pills-businessCard-tab" tabindex="0">
                                    <div class="business_card">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="business_card_images">
                                                    <h4>Business Card Front</h4>
                                                    <div class="custom_images">
                                                        <img src="{{ asset('website') }}/{{ $client->front_image ?? 'no-image.jpg' }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="business_card_images">
                                                    <h4>Business Card Back</h4>
                                                    <div class="custom_images">
                                                        <img
                                                            src="{{ asset('website') }}/{{ $client->back_image ?? 'no-image.jpg' }}">
                                                    </div>
                                                </div>
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
    <script>
        $(document).ready(function() {
            var isChild = {{ $client->is_child ? 'true' : 'false' }};
            var hasBranches = {{ $client->childClients->count() > 0 ? 'true' : 'false' }};
            var branchCount = {{ $client->childClients->count() }};

            @php
                // For child client, check if parent is active
                $parentClient = null;
                $parentActive = true;
                if ($client->is_child) {
                    $parentClient = \App\Models\Client::where('user_id', $client->user_id)->where('is_child', false)->first();
                    $parentActive = $parentClient && $parentClient->status == 1;
                }
            @endphp
            var parentActive = {{ $parentActive ? 'true' : 'false' }};
            var parentName = "{{ $parentClient->name ?? '' }}";

            function handleAccept() {
                // If this is a CHILD (branch) client
                if (isChild) {
                    if (!parentActive) {
                        // Parent is not active - show alert
                        Swal.fire({
                            title: 'Cannot Accept Branch',
                            html: 'Please activate the parent client <strong>"' + parentName +
                                '"</strong> first before accepting this branch.',
                            icon: 'warning',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#3085d6',
                        });
                        return false;
                    } else {
                        // Parent is active - directly accept branch
                        $('#accept-form').submit();
                        return true;
                    }
                }

                // If this is a PARENT client with branches
                if (hasBranches) {
                    Swal.fire({
                        title: 'Accept Client',
                        html: 'This client has <strong>' + branchCount +
                            ' branch(es)</strong>.<br>Do you want to accept branches as well?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Accept All',
                        cancelButtonText: 'No, Only Parent',
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Accept with branches
                            $('#accept_branches').val('1');
                            $('#accept-form').submit();
                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                            // Accept only parent
                            $('#accept_branches').val('0');
                            $('#accept-form').submit();
                        }
                    });
                    return false;
                }

                // Parent without branches - direct accept
                $('#accept-form').submit();
                return true;
            }

            $('#acceptBtn').click(function() {
                console.log($('#commission_percentage').val());
                if (!$('#commission_percentage').val()) {
                    $('#commissionModal').modal('show');
                } else {
                    handleAccept();
                }
            });

            $('#submitCommission').click(function() {
                var commissionValue = $('#commissionInput').val();
                console.log(commissionValue);
                if (commissionValue) {
                    $('#commission_percentage').val(commissionValue);
                    $('#commissionModal').modal('hide');
                    handleAccept();
                } else {
                    $('#commissionInput').addClass('is-invalid');
                    $('#commissionError').show();
                }
            });
        });
    </script>
@endpush
