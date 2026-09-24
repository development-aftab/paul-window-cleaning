@extends('theme.layout.master')

@push('css')
    <!-- Include Dropzone CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/basic.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        .create_clients_wrapper .select_dropdown_create_client select {
            padding: 7px;
        }
    </style>
@endpush
@section('navbar-title')
    <div class="custom_justify_between create_clients_navbar">
        <a href="{{ url('clients', $client->id) }}" class="back_btn_navbar">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h2 class="navbar_PageTitle">Edit Client</h2>
    </div>
@endsection
@section('content')
    @if (auth()->user()->hasRole('admin'))
        <section class="create_clients_sec custom_clients_section">
            <div class="container-fluid custom_container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="create_clients_wrapper shadow_box_wrapper">
                            <form method="post" action="{{ route('clients.update', $client->id) }}" class="form-horizontal validate" id="clientValidate" enctype="multipart/form-data">
                                {{ method_field('PATCH') }}
                                {{ csrf_field() }}
                                <div class="row">
                                    <div class="col-md-12">
                                        <h4 class="main_heading mb-0">Parent Company</h4>
                                    </div>
                                    <div class="col-md-12 general_info_container">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h4>General Information</h4>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="name" value="{{ $client->name ?? '' }}" id="client_name" placeholder="">
                                                    <label for="client_name">Client Name *</label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                <div class=" form-floating txt_field mb-3">

                                                    <select name="client_type[1]" id="" class="form-select">
                                                        <option value="" disabled {{ $client->client_type === null ? 'selected' : '' }}>Client
                                                            Type</option>
                                                        <option value="residential" {{ $client->client_type === 'residential' ? 'selected' : '' }}>
                                                            Residential</option>
                                                        <option value="commercial" {{ $client->client_type === 'commercial' ? 'selected' : '' }}>
                                                            Commercial</option>
                                                    </select>
                                                    <label for="">Client Type</label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                <div class="txt_field form-floating mb-3">
                                                    <select name="payment_type[1]" id="" class="form-select">
                                                        <option value="" disabled @selected(empty($client->payment_type))>Payment
                                                            Type</option>
                                                        <option value="cash" @selected($client->payment_type == 'cash')>Cash</option>
                                                        <option value="invoice" @selected($client->payment_type == 'invoice')>Invoice
                                                        </option>
                                                    </select>
                                                    <label for="">Payment Type</label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                <div class="form-floating txt_field">
                                                    <input type="number" class="form-control" name="commission_percentage" value="{{ $client->commission_percentage ?? '' }}" id="commission_percentage" placeholder="">
                                                    <label for="commission_percentage">Commission Percentage </label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4 first_start_date" style="{{ in_array($client->service_frequency, ['biMonthly', 'biAnnually']) ? '' : 'display:none' }}">{{-- Start Date: hidden unless 24 Weeks / biMonthly --}}
                                                <div class=" d-flex align-items-start mb-3">
                                                    <div class="form-floating txt_field flex-grow-1 me-2">
                                                        @php
                                                            $startDateFormatted = '';
                                                            if ($client->start_date) {
                                                                try {
                                                                    $startDateFormatted = \Carbon\Carbon::createFromFormat('d/m/Y', $client->start_date)->format('m-d-Y');
                                                                } catch (\Exception $e) {
                                                                    $startDateFormatted = $client->start_date;
                                                                }
                                                            }
                                                        @endphp
                                                        <input type="text" class="form-control" value="{{ $startDateFormatted }}" name="start_date" id="startDate" placeholder="mm-dd-yyyy" readonly>
                                                        <label for="start_date">Start Date *</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4 select_frequency">
                                                <div class="txt_field form-floating">
                                                    <select class="form-select note-type-select" name="service_frequency" id="">
                                                        <option value="" disabled @selected(empty($client->service_frequency))> Repeat Every *</option>
                                                        <option value="normalWeek" @selected($client->service_frequency == 'normalWeek')>Weekly </option>
                                                        <option value="fourWeek" @selected($client->service_frequency == 'fourWeek')>4 Weeks </option>
                                                        <option value="eightWeek" @selected($client->service_frequency == 'eightWeek')>8 Weeks</option>
                                                        <option value="quarterly" @selected($client->service_frequency == 'quarterly')>12 Weeks</option>
                                                        <option value="biAnnually" @selected($client->service_frequency == 'biAnnually')>24 Weeks</option>
                                                        <option value="biMonthly" @selected($client->service_frequency == 'biMonthly')>biMonthly </option>
                                                        <option value="monthly" @selected($client->service_frequency == 'monthly')>Monthly </option>
                                                        <option value="annually" @selected($client->service_frequency == 'annually')>52 Weeks</option>
                                                    </select>
                                                    <label for="">Repeat Every </label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4 second_start_date" style="{{ in_array($client->service_frequency, ['biMonthly', 'biAnnually']) ? '' : 'display:none' }}">
                                                <div class=" d-flex align-items-start mb-3">
                                                    <div class="form-floating txt_field flex-grow-1 me-2">
                                                        @php
                                                            $secondStartDateFormatted = '';
                                                            if ($client->second_start_date) {
                                                                try {
                                                                    $secondStartDateFormatted = \Carbon\Carbon::createFromFormat('d/m/Y', $client->second_start_date)->format('m-d-Y');
                                                                } catch (\Exception $e) {
                                                                    $secondStartDateFormatted = $client->second_start_date;
                                                                }
                                                            }
                                                        @endphp
                                                        <input type="text" class="form-control" value="{{ $secondStartDateFormatted }}" name="start_date_second" id="startDateSecond" placeholder="mm-dd-yyyy" readonly>
                                                        <label for="startDateSecond">Second Start Date</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h4>Address</h4>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="house_no[0]" id="" placeholder="" value="{{ $client->house_no ?? '' }}">
                                                    <label for="">Number</label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4 mb-5">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="address[0]" id="" placeholder="" value="{{ $client->address ?? '' }}">
                                                    <label for="">Street *</label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="city[0]" id="" placeholder="" value="{{ $client->city ?? '' }}">
                                                    <label for="">City *</label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="state[0]" id="" placeholder="" value="{{ $client->state ?? '' }}">
                                                    <label for="">State</label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                <div class="form-floating txt_field">
                                                    <input type="number" class="form-control" name="postal[0]" id="" placeholder="" value="{{ $client->postal ?? '' }}">
                                                    <label for="">Zip Code</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 contact_info_container" id="contact_info_container">
                                        <div class="contact_info">
                                            @php
                                                // Check if profile exists (now using client->profile instead of user->profile)
                                                $hasProfile = $client->profile ? true : false;

                                                // Helper function to ensure array
                                                $toArray = function ($value) {
                                                    if (is_array($value)) {
                                                        return $value;
                                                    }
                                                    if (is_string($value)) {
                                                        $decoded = json_decode($value, true);
                                                        return is_array($decoded) ? $decoded : [];
                                                    }
                                                    return [];
                                                };

                                                // additional_* arrays - get from profile and SLICE from index 1 (skip first for display)
                                                // Database has ALL emails, but first row shows from main fields, so skip first in additional
                                                $allPhones = $hasProfile ? $toArray($client->profile->additional_phones) : [];
                                                $allNames = $hasProfile ? $toArray($client->profile->additional_names) : [];
                                                $allPositions = $hasProfile ? $toArray($client->profile->additional_positions) : [];
                                                $allEmails = $hasProfile ? $toArray($client->profile->additional_emails) : [];
                                                $allNotes = $hasProfile ? $toArray($client->profile->additional_notes) : [];

                                                // Get invoice emails from profile
                                                $invoiceEmails = $hasProfile ? $toArray($client->profile->invoice_email) : [];

                                                // Slice from index 1 to skip first item (first item already shown in main row)
                                                $additionalPhones = array_slice($allPhones, 1);
                                                $additionalNames = array_slice($allNames, 1);
                                                $additionalPositions = array_slice($allPositions, 1);
                                                $additionalEmails = array_slice($allEmails, 1);
                                                $additionalNotes = array_slice($allNotes, 1);

                                                // First row values come from main fields
                                                $firstContactName = $client->contact_name ?? '';
                                                $firstPhone = $hasProfile ? $client->profile->phone ?? '' : '';
                                                $firstPosition = $client->position ?? '';
                                                $firstEmail = $client->contact_email ?? '';
                                                $firstNote = $client->description ?? '';

                                                // Check if first email is in invoice_email array
                                                $firstEmailChecked = in_array($firstEmail, $invoiceEmails);

                                                // Get max count from additional arrays (these are extra rows after first)
                                                $maxRows = max(count($additionalPhones), count($additionalNames), count($additionalEmails), count($additionalPositions), count($additionalNotes));
                                            @endphp
                                            <div class="row" id="contact_info_wrapper">
                                                <div class="col-md-12">
                                                    <h4>Contact Information</h4>
                                                </div>
                                                {{-- First Row - Main Contact (values from main fields) --}}
                                                <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                    <div class="form-floating txt_field mb-3">
                                                        <input type="text" class="form-control" name="contact_name[]" id="contact_name-0" placeholder="" value="{{ $firstContactName }}">
                                                        <label for="contact_name-0">Contact Name</label>
                                                    </div>
                                                </div>
                                                <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                    <div class="form-floating txt_field mb-3">
                                                        <input type="text" class="form-control" name="phone[]" id="phone-0" placeholder="" value="{{ $firstPhone }}">
                                                        <label for="phone-0">Phone Number</label>
                                                    </div>
                                                </div>
                                                <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                    <div class="form-floating txt_field mb-3">
                                                        <input type="text" class="form-control" name="positions[]" id="positions-0" placeholder="" value="{{ $firstPosition }}">
                                                        <label for="positions-0">Position In Company</label>
                                                    </div>
                                                </div>
                                                <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6">
                                                    <div class="d-flex align-items-start mb-3">
                                                        <div class="form-floating txt_field flex-grow-1 me-2">
                                                            <input type="email" class="form-control" name="email[]" id="email-0" placeholder="" value="{{ $firstEmail }}">
                                                            <label for="email-0">Email</label>
                                                        </div>
                                                        <div class="form-check d-flex align-items-center ms-2" style="margin-top: 15px;">
                                                            <input class="form-check-input" type="checkbox" name="invoice_email_parent[0][]" value="0" {{ $firstEmailChecked ? 'checked' : '' }}>
                                                            <label class="form-check-label ms-1" title="Send invoice to this email">
                                                                <i class="fas fa-envelope"></i>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6">
                                                    <div class="d-flex mb-3">
                                                        <div class="form-floating txt_field flex-grow-1 me-2">
                                                            <input type="text" class="form-control" name="note[0][]" id="note-0" placeholder="" value="{{ $firstNote }}">
                                                            <label for="note-0">Note</label>
                                                        </div>
                                                        <button type="button" id="add_contact_info" class="btn btn-primary btn_add_contact_info">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                {{-- Additional Rows - Loop through additional_* arrays --}}
                                                @for ($i = 0; $i < $maxRows; $i++)
                                                    <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4 contact-row-{{ $i + 1 }}">
                                                        <div class="form-floating txt_field mb-3">
                                                            <input type="text" class="form-control" name="contact_name[]" id="contact_name-{{ $i + 1 }}" placeholder="" value="{{ isset($additionalNames[$i]) && is_string($additionalNames[$i]) ? $additionalNames[$i] : '' }}">
                                                            <label for="contact_name-{{ $i + 1 }}">Contact
                                                                Name</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4 contact-row-{{ $i + 1 }}">
                                                        <div class="form-floating txt_field mb-3">
                                                            <input type="text" class="form-control" name="phone[]" id="phone-{{ $i + 1 }}" placeholder="" value="{{ isset($additionalPhones[$i]) && is_string($additionalPhones[$i]) ? $additionalPhones[$i] : '' }}">
                                                            <label for="phone-{{ $i + 1 }}">Phone Number</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4 contact-row-{{ $i + 1 }}">
                                                        <div class="form-floating txt_field mb-3">
                                                            <input type="text" class="form-control" name="positions[]" id="positions-{{ $i + 1 }}" placeholder="" value="{{ isset($additionalPositions[$i]) && is_string($additionalPositions[$i]) ? $additionalPositions[$i] : '' }}">
                                                            <label for="positions-{{ $i + 1 }}">Position In
                                                                Company</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 contact-row-{{ $i + 1 }}">
                                                        @php
                                                            $currentEmail = isset($additionalEmails[$i]) && is_string($additionalEmails[$i]) ? $additionalEmails[$i] : '';
                                                            // Reconstruct all emails array to check index
                                                            $allEmailsArray = array_merge([$firstEmail], $additionalEmails);
                                                            $emailIndex = $i + 1; // Index in allEmailsArray (0 is first email)
                                                            $emailChecked = isset($allEmailsArray[$emailIndex]) && in_array($allEmailsArray[$emailIndex], $invoiceEmails);
                                                        @endphp
                                                        <div class="d-flex align-items-start mb-3">
                                                            <div class="form-floating txt_field flex-grow-1 me-2">
                                                                <input type="email" class="form-control" name="email[]" id="email-{{ $i + 1 }}" placeholder="" value="{{ $currentEmail }}">
                                                                <label for="email-{{ $i + 1 }}">Email</label>
                                                            </div>
                                                            <div class="form-check d-flex align-items-center ms-2" style="margin-top: 15px;">
                                                                <input class="form-check-input" type="checkbox" name="invoice_email_parent[0][]" value="{{ $i + 1 }}" {{ $emailChecked ? 'checked' : '' }}>
                                                                <label class="form-check-label ms-1" title="Send invoice to this email">
                                                                    <i class="fas fa-envelope"></i>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 contact-row-{{ $i + 1 }}">
                                                        <div class="d-flex mb-3">
                                                            <div class="form-floating txt_field flex-grow-1 me-2">
                                                                <input type="text" class="form-control" name="note[0][]" id="note-{{ $i + 1 }}" placeholder="" value="{{ isset($additionalNotes[$i]) && is_string($additionalNotes[$i]) ? $additionalNotes[$i] : '' }}">
                                                                <label for="note-{{ $i + 1 }}">Note</label>
                                                            </div>
                                                            <button type="button" class="btn btn-danger btn_remove_contact_info" data-row="{{ $i + 1 }}">
                                                                <i class="fas fa-minus"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h4>Notes</h4>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-floating txt_field mb-3">
                                                    <textarea rows="4" class="form-control" name="additional_note" id="additional_note" placeholder="Additional Notes" style="height: 100px">{{ $client->additional_note ?? '' }}</textarea>
                                                    <label for="additional_note">Notes (misc info about this client or job)</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 price_list_custom_row">
                                        <h4>Price</h4>
                                        <div class="row price_list_wrapper custom_row">
                                            @foreach ($client->clientPrice->sortBy('position') as $index => $price)
                                                <div class="price_list_append_row col-xxl-4 col-xl-6 col-lg-6 col-md-6">
                                                    <div class="price_list">
                                                        <div class="input_text_filed_price_list">
                                                            <input type="text" class="form-control" value="{{ $price->name ?? '' }}" name="prices[{{ $index }}][side]">
                                                        </div>
                                                        <div class="txt_field price_list_icon">
                                                            <i class="fa-solid fa-dollar-sign price_list_icon_doller"></i>
                                                            <input type="number" class="form-control" value="{{ $price->value ?? 0 }}" name="prices[{{ $index }}][number]">
                                                            <button type="button" class="btn_red btn_global delete_price_list">
                                                                <i class="fa-solid fa-trash"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <button type="button" class="btn_global btn_blue add_more_price_list">Add
                                            Custom<i class="fa-solid fa-plus"></i></button>
                                    </div>
                                    @php
                                        $clientClosedDays = $client->clientDay->pluck('day')->toArray();
                                    @endphp
                                    <div class="col-md-6 select_two_field">
                                        <h4>Closed</h4>
                                        <div class="txt_field form-floating">
                                            <div class="custom_multi_select">
                                                <select multiple class="multiselect form-select note-type-select" name="unavail_day[]">
                                                    <option></option>
                                                    <option value="sunday" @selected(in_array('sunday', $clientClosedDays))>Sunday</option>
                                                    <option value="monday" @selected(in_array('monday', $clientClosedDays))>Monday</option>
                                                    <option value="tuesday" @selected(in_array('tuesday', $clientClosedDays))>Tuesday</option>
                                                    <option value="wednesday" @selected(in_array('wednesday', $clientClosedDays))>Wednesday
                                                    </option>
                                                    <option value="thursday" @selected(in_array('thursday', $clientClosedDays))>Thursday
                                                    </option>
                                                    <option value="friday" @selected(in_array('friday', $clientClosedDays))>Friday</option>
                                                    <option value="saturday" @selected(in_array('saturday', $clientClosedDays))>Saturday
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="cycle_frequency_wrapper ">
                                            @forelse ($client->clientHour as $time)
                                                <div class="appended_row_time default_apended_row mt-5 row">
                                                    <div class="col-md-12">
                                                        @if ($loop->index > 0)
                                                            <div class="remove_append_time">
                                                                <button type="button" class="btn_global btn_red"><i class="fa-solid fa-trash"></i></button>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-floating txt_field custom_dates">
                                                            <input type="time" class="form-control" name="best_time[{{ $loop->index }}][start_hour]" value="{{ $time->start_hour ?? '' }}" id="startHour{{ $loop->index }}" placeholder="">
                                                            <label for="startHour{{ $loop->index }}">Starting
                                                                Hour</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="button_field_align">
                                                            <div class="form-floating txt_field custom_dates">
                                                                <input type="time" class="form-control" name="best_time[{{ $loop->index }}][end_hour]" value="{{ $time->end_hour ?? '' }}" id="endHour{{ $loop->index }}" placeholder="">
                                                                <label for="endHour{{ $loop->index }}">Ending
                                                                    Hour</label>
                                                            </div>
                                                            <div class="add_more_time">
                                                                <button type="button" class="btn_global btn_blue"><i class="fa-solid fa-plus"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                {{-- Default empty row when no best times exist --}}
                                                <div class="appended_row_time default_apended_row mt-5 row">
                                                    <div class="col-md-6">
                                                        <div class="form-floating txt_field custom_dates">
                                                            <input type="time" class="form-control" name="best_time[0][start_hour]" id="startHour0" placeholder="">
                                                            <label for="startHour0">Starting Hour</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="button_field_align">
                                                            <div class="form-floating txt_field custom_dates">
                                                                <input type="time" class="form-control" name="best_time[0][end_hour]" id="endHour0" placeholder="">
                                                                <label for="endHour0">Ending Hour</label>
                                                            </div>
                                                            <div class="add_more_time">
                                                                <button type="button" class="btn_global btn_blue"><i class="fa-solid fa-plus"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforelse
                                            <div class="create_client_cus_row row">
                                                <div class="col-md-12 append_service_time">
                                                    <div class="appended_items"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @php
                                        $clientRouteIds = $client->clientRouteStaff->pluck('route_id')->toArray();
                                    @endphp
                                    <div class="col-md-6 mt-5">
                                        <div class="select_dropdown_create_client txt_field form-floating">
                                            <select class="form-select" name="route_id[]" aria-label="Default select">
                                                <option selected>Select Route</option>
                                                @foreach ($route as $item)
                                                    <option value="{{ $item->id }}" @selected(in_array($item->id, $clientRouteIds))>
                                                        {{ $item->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <label for="">Assign Route</label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="client_upload_img">
                                            <div class="dropzone dz-clickable" id="client_dropzone_image">
                                                <div class="dz-default dz-message">
                                                    <button class="dz-button" type="button">
                                                        <i class="fa-solid fa-image"></i>
                                                        <h6>Upload Images</h6>
                                                        <p>Drag & drop or click to upload</p>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="hidden-input-container"></div>
                                    </div>
                                    <div class="col-md-6 mt-4">
                                        <div class="client_upload_img custom_img_margin mb-5">
                                            <div class="image-input @if ($client->front_image) image-input-changed @endif" data-kt-image-input="true">
                                                <label class="btn btn-active-color-primary shadow edit_icon custom_append_img" data-kt-image-input-action="change" data-bs-toggle="tooltip" data-bs-dismiss="click">
                                                    <div class="image-input-wrapper">
                                                        <img class="input_image_field" src="{{ asset('website') }}/{{ $client->front_image ?? 'default_image.jpg' }}" alt="Business Card Front" />
                                                    </div>
                                                    <div class="custom_upload_content">
                                                        <span><i class="fa-solid fa-image"></i></span>
                                                        <h4>Business Card Front</h4>
                                                        <p>Image of the front of your business card</p>
                                                    </div>
                                                    <input type="file" name="front_image" accept=".png, .jpg, .jpeg" class="myinput custom_file_input" />
                                                    <input type="hidden" name="avatar_remove" />
                                                </label>
                                                <span class="btn btn-icon btn-circle btn-active-color-primary shadow edit_icon" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" data-bs-dismiss="click" title="Cancel avatar">
                                                    <i class="ki-outline ki-cross fs-3"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mt-4">
                                        <div class="client_upload_img custom_img_margin mb-5">
                                            <div class="image-input @if ($client->back_image) image-input-changed @endif" data-kt-image-input="true">
                                                <label class="btn btn-active-color-primary shadow edit_icon custom_append_img" data-kt-image-input-action="change" data-bs-toggle="tooltip" data-bs-dismiss="click">
                                                    <div class="image-input-wrapper">
                                                        <img class="input_image_field" src="{{ asset('website') }}/{{ $client->back_image ?? 'default_image.jpg' }}" alt="Business Card Front" />
                                                    </div>

                                                    <div class="custom_upload_content">
                                                        <span><i class="fa-solid fa-image"></i></span>
                                                        <h4>Business Card Back</h4>
                                                        <p>Image of the back of your business card</p>
                                                    </div>

                                                    <input type="file" name="back_image" accept=".png, .jpg, .jpeg" class="myinput custom_file_input" />
                                                    <input type="hidden" name="avatar_remove" />
                                                </label>

                                                <span class="btn btn-icon btn-circle btn-active-color-primary shadow edit_icon" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" data-bs-dismiss="click" title="Cancel avatar">
                                                    <i class="ki-outline ki-cross fs-3"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Branches List Section --}}
                                    @if (!$client->is_child && $client->childClients && $client->childClients->count() > 0)
                                        <div class="col-md-12 mt-4">
                                            <div class="branches_list_section">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h4 class="mb-0">Branches ({{ $client->childClients->count() }})
                                                    </h4>
                                                    <a href="{{ route('branch.create', $client->id) }}" class="btn_global btn_blue btn-sm">
                                                        Add Branch <i class="fa-solid fa-plus"></i>
                                                    </a>
                                                </div>
                                                <div class="custom_table">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>Branch Name</th>
                                                                    <th>Address</th>
                                                                    <th>City</th>
                                                                    <th>Route</th>
                                                                    <th>Status</th>
                                                                    <th>Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @forelse ($client->childClients as $branch)
                                                                    <tr>
                                                                        <td>{{ $branch->name ?? '-' }}</td>
                                                                        <td>{{ $branch->address ?? '-' }}</td>
                                                                        <td>{{ $branch->city ?? '-' }}</td>
                                                                        <td>{{ optional(optional($branch->clientRouteStaff->first())->route)->name ?? '-' }}
                                                                        </td>
                                                                        <td>
                                                                            <span class="badge {{ $branch->status ? 'bg-success' : 'bg-danger' }}">
                                                                                {{ $branch->status ? 'Active' : 'Inactive' }}
                                                                            </span>
                                                                        </td>
                                                                        <td>
                                                                            <div class="dropdown">
                                                                                <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                                    <i class="fa-solid fa-ellipsis"></i>
                                                                                </button>
                                                                                <ul class="dropdown-menu dropdown-menu-end">
                                                                                    <li>
                                                                                        <a class="dropdown-item" href="{{ route('clients.show', $branch->id) }}">View</a>
                                                                                    </li>
                                                                                    <li>
                                                                                        <a class="dropdown-item" href="{{ route('branch.edit', $branch->id) }}">Edit</a>
                                                                                    </li>
                                                                                    @if ($branch->clientRouteStaff && $branch->clientRouteStaff->count() > 0)
                                                                                        <li>
                                                                                            <a class="dropdown-item" href="{{ route('client-schedule', [$branch->id]) }}">Schedule</a>
                                                                                        </li>
                                                                                    @else
                                                                                        <li>
                                                                                            <a class="dropdown-item" href="{{ route('branch.edit', $branch->id) }}">Assign
                                                                                                Route</a>
                                                                                        </li>
                                                                                    @endif
                                                                                </ul>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                @empty
                                                                    <tr>
                                                                        <td colspan="6" class="text-center">No branches
                                                                            available</td>
                                                                    </tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @elseif(!$client->is_child)
                                        <div class="col-md-12 mt-4">
                                            <div class="branches_list_section">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h4 class="mb-0">Branches</h4>
                                                    <a href="{{ route('branch.create', $client->id) }}" class="btn_global btn_blue btn-sm">
                                                        Add Branch <i class="fa-solid fa-plus"></i>
                                                    </a>
                                                </div>
                                                <div class="text-center py-4" style="background: #f8f9fa; border-radius: 8px;">
                                                    <i class="fa-solid fa-code-branch" style="font-size: 40px; color: #ccc; margin-bottom: 10px;"></i>
                                                    <p class="text-muted mb-0">No branches available for this client.
                                                    </p>
                                                </div>
                                                <br>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="col-md-12">
                                        <div class="custom_justify_between mt-4">
                                            <input type="hidden" name="action" id="form_action" value="update">
                                            <a href="{{ route('clients.index') }}" class="btn_global btn_grey">
                                                Cancel<i class="fa-solid fa-close"></i>
                                            </a>
                                            <div class="d-flex gap-3">
                                                <button type="submit" data-action="update" class="btn_global btn_blue submitButton">
                                                    Update<i class="fa-solid fa-floppy-disk ms-2"></i>
                                                </button>
                                                <button type="submit" data-action="update_and_schedule" class="btn_global btn_green submitButton">
                                                    Update & Schedule<i class="fa-solid fa-calendar-plus ms-2"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @elseif(auth()->user()->hasRole('staff'))
        <section class="create_clients_sec custom_clients_section">
            <div class="container-fluid custom_container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="create_clients_wrapper shadow_box_wrapper">
                            <form method="post" action="{{ route('clients.update', $client->id) }}" class="form-horizontal validate" id="clientValidate" enctype="multipart/form-data">
                                {{ method_field('PATCH') }}
                                {{ csrf_field() }}
                                <div class="row">
                                    <div class="col-md-12">
                                        <h4 class="main_heading mb-0">Parent Company</h4>
                                    </div>
                                    <div class="col-md-12 general_info_container">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h4>General Information</h4>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="name" value="{{ $client->name ?? '' }}" id="client_name" placeholder="">
                                                    <label for="client_name">Client Name *</label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                <div class=" form-floating txt_field mb-3">

                                                    <select name="client_type[1]" id="" class="form-select">
                                                        <option value="" disabled {{ $client->client_type === null ? 'selected' : '' }}>Client
                                                            Type</option>
                                                        <option value="residential" {{ $client->client_type === 'residential' ? 'selected' : '' }}>
                                                            Residential</option>
                                                        <option value="commercial" {{ $client->client_type === 'commercial' ? 'selected' : '' }}>
                                                            Commercial</option>
                                                    </select>
                                                    <label for="">Client Type</label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                <div class="txt_field form-floating mb-3">
                                                    <select name="payment_type[1]" id="" class="form-select">
                                                        <option value="" disabled @selected(empty($client->payment_type))>
                                                            Payment
                                                            Type</option>
                                                        <option value="cash" @selected($client->payment_type == 'cash')>Cash</option>
                                                        <option value="invoice" @selected($client->payment_type == 'invoice')>Invoice
                                                        </option>
                                                    </select>
                                                    <label for="">Payment Type</label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                <div class="form-floating txt_field">
                                                    <input type="number" class="form-control" name="commission_percentage" value="{{ $client->commission_percentage ?? '' }}" id="commission_percentage" placeholder="">
                                                    <label for="commission_percentage">Commission Percentage </label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4 first_start_date" style="{{ in_array($client->service_frequency, ['biMonthly', 'biAnnually']) ? '' : 'display:none' }}">{{-- Start Date: hidden unless 24 Weeks / biMonthly --}}
                                                <div class=" d-flex align-items-start mb-3">
                                                    <div class="form-floating txt_field flex-grow-1 me-2">
                                                        <input type="date" class="form-control" value="{{ $client->start_date ?? '' }}" name="start_date" id="startDate" placeholder="">
                                                        <label for="start_date">Start Date *</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4 select_frequency">
                                                <div class="txt_field form-floating">
                                                    <select class="form-select note-type-select" name="service_frequency" id="">
                                                        <option value="" disabled @selected(empty($client->service_frequency))>
                                                            Frequency *</option>
                                                        <option value="normalWeek" @selected($client->service_frequency == 'normalWeek')>Weekly
                                                        </option>
                                                        <option value="biMonthly" @selected($client->service_frequency == 'biMonthly')>biMonthly
                                                        </option>
                                                        <option value="monthly" @selected($client->service_frequency == 'monthly')>Monthly
                                                        </option>
                                                        {{-- <option value="eightWeek" @selected($client->service_frequency == 'eightWeek')>8 Weeks
                                                        </option>
                                                        <option value="quarterly" @selected($client->service_frequency == 'quarterly')>12 Weeks
                                                        </option>
                                                        <option value="biAnnually" @selected($client->service_frequency == 'biAnnually')>24 Weeks
                                                        </option> --}}
                                                        <option value="annually" @selected($client->service_frequency == 'annually')>52 Weeks
                                                        </option>
                                                    </select>
                                                    <label for="">Frequency</label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4 second_start_date" style="{{ in_array($client->service_frequency, ['biMonthly', 'biAnnually']) ? '' : 'display:none' }}">
                                                <div class="form-floating txt_field custom_dates">
                                                    <input type="text" class="form-control startDateSecond" value="{{ $client->second_start_date ?? '' }}" name="start_date_second" id="startDateSecondStaff" placeholder="" />
                                                    <label for="">Second Start Date</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h4>Address</h4>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="house_no[0]" id="" placeholder="" value="{{ $client->house_no ?? '' }}">
                                                    <label for="">Number</label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4 mb-5">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="address[0]" id="" placeholder="" value="{{ $client->address ?? '' }}">
                                                    <label for="">Street *</label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="city[0]" id="" placeholder="" value="{{ $client->city ?? '' }}">
                                                    <label for="">City *</label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="state[0]" id="" placeholder="" value="{{ $client->state ?? '' }}">
                                                    <label for="">State</label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                <div class="form-floating txt_field">
                                                    <input type="number" class="form-control" name="postal[0]" id="" placeholder="" value="{{ $client->postal ?? '' }}">
                                                    <label for="">Zip Code</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 contact_info_container" id="contact_info_container">
                                        <div class="contact_info">
                                            @php
                                                // Check if profile exists (now using client->profile instead of user->profile)
                                                $hasProfile = $client->profile ? true : false;

                                                // Helper function to ensure array
                                                $toArray = function ($value) {
                                                    if (is_array($value)) {
                                                        return $value;
                                                    }
                                                    if (is_string($value)) {
                                                        $decoded = json_decode($value, true);
                                                        return is_array($decoded) ? $decoded : [];
                                                    }
                                                    return [];
                                                };

                                                // additional_* arrays - get from profile and SLICE from index 1 (skip first for display)
                                                // Database has ALL emails, but first row shows from main fields, so skip first in additional
                                                $allPhones = $hasProfile ? $toArray($client->profile->additional_phones) : [];
                                                $allNames = $hasProfile ? $toArray($client->profile->additional_names) : [];
                                                $allPositions = $hasProfile ? $toArray($client->profile->additional_positions) : [];
                                                $allEmails = $hasProfile ? $toArray($client->profile->additional_emails) : [];
                                                $allNotes = $hasProfile ? $toArray($client->profile->additional_notes) : [];

                                                // Get invoice emails from profile
                                                $invoiceEmails = $hasProfile ? $toArray($client->profile->invoice_email) : [];

                                                // Slice from index 1 to skip first item (first item already shown in main row)
                                                $additionalPhones = array_slice($allPhones, 1);
                                                $additionalNames = array_slice($allNames, 1);
                                                $additionalPositions = array_slice($allPositions, 1);
                                                $additionalEmails = array_slice($allEmails, 1);
                                                $additionalNotes = array_slice($allNotes, 1);

                                                // First row values come from main fields
                                                $firstContactName = $client->contact_name ?? '';
                                                $firstPhone = $hasProfile ? $client->profile->phone ?? '' : '';
                                                $firstPosition = $client->position ?? '';
                                                $firstEmail = $client->contact_email ?? '';
                                                $firstNote = $client->description ?? '';

                                                // Check if first email is in invoice_email array
                                                $firstEmailChecked = in_array($firstEmail, $invoiceEmails);

                                                // Get max count from additional arrays (these are extra rows after first)
                                                $maxRows = max(count($additionalPhones), count($additionalNames), count($additionalEmails), count($additionalPositions), count($additionalNotes));
                                            @endphp
                                            <div class="row" id="contact_info_wrapper_staff">
                                                <div class="col-md-12">
                                                    <h4>Contact Information</h4>
                                                </div>
                                                {{-- First Row - Main Contact (values from main fields) --}}
                                                <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                    <div class="form-floating txt_field mb-3">
                                                        <input type="text" class="form-control" name="contact_name[]" id="contact_name-0" placeholder="" value="{{ $firstContactName }}">
                                                        <label for="contact_name-0">Contact Name</label>
                                                    </div>
                                                </div>
                                                <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                    <div class="form-floating txt_field mb-3">
                                                        <input type="text" class="form-control" name="phone[]" id="phone-0" placeholder="" value="{{ $firstPhone }}">
                                                        <label for="phone-0">Phone Number</label>
                                                    </div>
                                                </div>
                                                <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                    <div class="form-floating txt_field mb-3">
                                                        <input type="text" class="form-control" name="positions[]" id="positions-0" placeholder="" value="{{ $firstPosition }}">
                                                        <label for="positions-0">Position In Company</label>
                                                    </div>
                                                </div>
                                                <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6">
                                                    <div class="d-flex align-items-start mb-3">
                                                        <div class="form-floating txt_field flex-grow-1 me-2">
                                                            <input type="email" class="form-control" name="email[]" id="email-0" placeholder="" value="{{ $firstEmail }}">
                                                            <label for="email-0">Email</label>
                                                        </div>
                                                        <div class="form-check d-flex align-items-center ms-2" style="margin-top: 15px;">
                                                            <input class="form-check-input" type="checkbox" name="invoice_email_parent[0][]" value="0" {{ $firstEmailChecked ? 'checked' : '' }}>
                                                            <label class="form-check-label ms-1" title="Send invoice to this email">
                                                                <i class="fas fa-envelope"></i>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6">
                                                    <div class="d-flex mb-3">
                                                        <div class="form-floating txt_field flex-grow-1 me-2">
                                                            <input type="text" class="form-control" name="note[0][]" id="note-0" placeholder="" value="{{ $firstNote }}">
                                                            <label for="note-0">Note</label>
                                                        </div>
                                                        <button type="button" id="add_contact_info_staff" class="btn btn-primary btn_add_contact_info">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                {{-- Additional Rows - Loop through additional_* arrays --}}
                                                @for ($i = 0; $i < $maxRows; $i++)
                                                    <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4 contact-row-{{ $i + 1 }}">
                                                        <div class="form-floating txt_field mb-3">
                                                            <input type="text" class="form-control" name="contact_name[]" id="contact_name-{{ $i + 1 }}" placeholder="" value="{{ isset($additionalNames[$i]) && is_string($additionalNames[$i]) ? $additionalNames[$i] : '' }}">
                                                            <label for="contact_name-{{ $i + 1 }}">Contact
                                                                Name</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4 contact-row-{{ $i + 1 }}">
                                                        <div class="form-floating txt_field mb-3">
                                                            <input type="text" class="form-control" name="phone[]" id="phone-{{ $i + 1 }}" placeholder="" value="{{ isset($additionalPhones[$i]) && is_string($additionalPhones[$i]) ? $additionalPhones[$i] : '' }}">
                                                            <label for="phone-{{ $i + 1 }}">Phone Number</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4 contact-row-{{ $i + 1 }}">
                                                        <div class="form-floating txt_field mb-3">
                                                            <input type="text" class="form-control" name="positions[]" id="positions-{{ $i + 1 }}" placeholder="" value="{{ isset($additionalPositions[$i]) && is_string($additionalPositions[$i]) ? $additionalPositions[$i] : '' }}">
                                                            <label for="positions-{{ $i + 1 }}">Position In
                                                                Company</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 contact-row-{{ $i + 1 }}">
                                                        @php
                                                            $currentEmail = isset($additionalEmails[$i]) && is_string($additionalEmails[$i]) ? $additionalEmails[$i] : '';
                                                            // Reconstruct all emails array to check index
                                                            $allEmailsArray = array_merge([$firstEmail], $additionalEmails);
                                                            $emailIndex = $i + 1; // Index in allEmailsArray (0 is first email)
                                                            $emailChecked = isset($allEmailsArray[$emailIndex]) && in_array($allEmailsArray[$emailIndex], $invoiceEmails);
                                                        @endphp
                                                        <div class="d-flex align-items-start mb-3">
                                                            <div class="form-floating txt_field flex-grow-1 me-2">
                                                                <input type="email" class="form-control" name="email[]" id="email-{{ $i + 1 }}" placeholder="" value="{{ $currentEmail }}">
                                                                <label for="email-{{ $i + 1 }}">Email</label>
                                                            </div>
                                                            <div class="form-check d-flex align-items-center ms-2" style="margin-top: 15px;">
                                                                <input class="form-check-input" type="checkbox" name="invoice_email_parent[0][]" value="{{ $i + 1 }}" {{ $emailChecked ? 'checked' : '' }}>
                                                                <label class="form-check-label ms-1" title="Send invoice to this email">
                                                                    <i class="fas fa-envelope"></i>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 contact-row-{{ $i + 1 }}">
                                                        <div class="d-flex mb-3">
                                                            <div class="form-floating txt_field flex-grow-1 me-2">
                                                                <input type="text" class="form-control" name="note[0][]" id="note-{{ $i + 1 }}" placeholder="" value="{{ isset($additionalNotes[$i]) && is_string($additionalNotes[$i]) ? $additionalNotes[$i] : '' }}">
                                                                <label for="note-{{ $i + 1 }}">Note</label>
                                                            </div>
                                                            <button type="button" class="btn btn-danger btn_remove_contact_info" data-row="{{ $i + 1 }}">
                                                                <i class="fas fa-minus"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h4>Notes</h4>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-floating txt_field mb-3">
                                                    <textarea rows="4" class="form-control" name="additional_note" id="additional_note" placeholder="Additional Notes" style="height: 100px">{{ $client->additional_note ?? '' }}</textarea>
                                                    <label for="additional_note">Notes (misc info about this client or job)</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 price_list_custom_row">
                                        <h4>Price</h4>
                                        <div class="row price_list_wrapper custom_row">
                                            <!-- Predefined Entries -->
                                            <!-- Check if there are existing prices -->

                                            <!-- Existing Prices Loop -->
                                            @foreach ($client->clientPrice as $index => $price)
                                                <div class="price_list_append_row col-xxl-4 col-xl-6 col-lg-6 col-md-6">
                                                    <div class="price_list">
                                                        <div class="input_text_filed_price_list">
                                                            <input type="text" class="form-control" value="{{ $price->name ?? '' }}" name="prices[{{ $index }}][side]">
                                                        </div>
                                                        <div class="txt_field price_list_icon">
                                                            <i class="fa-solid fa-dollar-sign price_list_icon_doller"></i>
                                                            <input type="number" class="form-control" value="{{ $price->value ?? 0 }}" name="prices[{{ $index }}][number]">
                                                            @if ($loop->index > 0)
                                                                <button type="button" class="btn_red btn_global delete_price_list">
                                                                    <i class="fa-solid fa-trash"></i></button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <button type="button" class="btn_global btn_blue add_more_price_list">Add
                                            Custom<i class="fa-solid fa-plus"></i></button>
                                    </div>
                                    @php
                                        $clientClosedDays = $client->clientDay->pluck('day')->toArray();
                                    @endphp
                                    <div class="col-md-6 select_two_field">
                                        <h4>Closed</h4>
                                        <div class="txt_field form-floating">
                                            <div class="custom_multi_select">
                                                <select multiple class="multiselect form-select note-type-select" name="unavail_day[]">
                                                    <option></option>
                                                    <option value="sunday" @selected(in_array('sunday', $clientClosedDays))>Sunday</option>
                                                    <option value="monday" @selected(in_array('monday', $clientClosedDays))>Monday</option>
                                                    <option value="tuesday" @selected(in_array('tuesday', $clientClosedDays))>Tuesday</option>
                                                    <option value="wednesday" @selected(in_array('wednesday', $clientClosedDays))>Wednesday
                                                    </option>
                                                    <option value="thursday" @selected(in_array('thursday', $clientClosedDays))>Thursday
                                                    </option>
                                                    <option value="friday" @selected(in_array('friday', $clientClosedDays))>Friday</option>
                                                    <option value="saturday" @selected(in_array('saturday', $clientClosedDays))>Saturday
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="cycle_frequency_wrapper ">
                                            @forelse ($client->clientHour as $time)
                                                <div class="appended_row_time default_apended_row mt-5 row">
                                                    <div class="col-md-12">
                                                        @if ($loop->index > 0)
                                                            <div class="remove_append_time">
                                                                <button type="button" class="btn_global btn_red"><i class="fa-solid fa-trash"></i></button>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-floating txt_field custom_dates">
                                                            <input type="time" class="form-control" name="best_time[{{ $loop->index }}][start_hour]" value="{{ $time->start_hour ?? '' }}" id="startHour{{ $loop->index }}" placeholder="">
                                                            <label for="startHour{{ $loop->index }}">Starting
                                                                Hour</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="button_field_align">
                                                            <div class="form-floating txt_field custom_dates">
                                                                <input type="time" class="form-control" name="best_time[{{ $loop->index }}][end_hour]" value="{{ $time->end_hour ?? '' }}" id="endHour{{ $loop->index }}" placeholder="">
                                                                <label for="endHour{{ $loop->index }}">Ending
                                                                    Hour</label>

                                                            </div>
                                                            <div class="add_more_time">
                                                                <button type="button" class="btn_global btn_blue"><i class="fa-solid fa-plus"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                {{-- Default empty row when no best times exist --}}
                                                <div class="appended_row_time default_apended_row mt-5 row">
                                                    <div class="col-md-6">
                                                        <div class="form-floating txt_field custom_dates">
                                                            <input type="time" class="form-control" name="best_time[0][start_hour]" id="startHour0" placeholder="">
                                                            <label for="startHour0">Starting Hour</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="button_field_align">
                                                            <div class="form-floating txt_field custom_dates">
                                                                <input type="time" class="form-control" name="best_time[0][end_hour]" id="endHour0" placeholder="">
                                                                <label for="endHour0">Ending Hour</label>
                                                            </div>
                                                            <div class="add_more_time">
                                                                <button type="button" class="btn_global btn_blue"><i class="fa-solid fa-plus"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforelse
                                            <div class="create_client_cus_row row">
                                                <div class="col-md-12 append_service_time">
                                                    <div class="appended_items"></div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    @php
                                        $clientRouteIds = $client->clientRouteStaff->pluck('route_id')->toArray();
                                    @endphp
                                    <div class="col-md-6 mt-5">
                                        <div class="select_dropdown_create_client txt_field form-floating">
                                            <select class="form-select" name="route_id[]" aria-label="Default select">
                                                <option disabled selected>Select Route</option>
                                                @foreach ($route as $item)
                                                    <option value="{{ $item->id }}" @selected(in_array($item->id, $clientRouteIds))>
                                                        {{ $item->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <label for="">Assign Route</label>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="client_upload_img">
                                            <div class="dropzone dz-clickable" id="client_dropzone_image">
                                                <div class="dz-default dz-message">
                                                    <button class="dz-button" type="button">
                                                        <i class="fa-solid fa-image"></i>
                                                        <h6>Upload Images</h6>
                                                        <p>Drag & drop or click to upload</p>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="hidden-input-container"></div>
                                    </div>
                                    <div class="col-md-6 mt-4">
                                        <div class="client_upload_img custom_img_margin mb-5">
                                            <div class="image-input @if ($client->front_image) image-input-changed @endif" data-kt-image-input="true">
                                                <label class="btn btn-active-color-primary shadow edit_icon custom_append_img" data-kt-image-input-action="change" data-bs-toggle="tooltip" data-bs-dismiss="click">
                                                    <div class="image-input-wrapper">
                                                        <img class="input_image_field" src="{{ asset('website') }}/{{ $client->front_image ?? 'default_image.jpg' }}" alt="Business Card Front" />
                                                    </div>
                                                    <div class="custom_upload_content">
                                                        <span><i class="fa-solid fa-image"></i></span>
                                                        <h4>Business Card Front</h4>
                                                        <p>Image of the front of your business card</p>
                                                    </div>
                                                    <input type="file" name="front_image" accept=".png, .jpg, .jpeg" class="myinput custom_file_input" />
                                                    <input type="hidden" name="avatar_remove" />
                                                </label>
                                                <span class="btn btn-icon btn-circle btn-active-color-primary shadow edit_icon" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" data-bs-dismiss="click" title="Cancel avatar">
                                                    <i class="ki-outline ki-cross fs-3"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mt-4">
                                        <div class="client_upload_img custom_img_margin mb-5">
                                            <div class="image-input @if ($client->back_image) image-input-changed @endif" data-kt-image-input="true">
                                                <label class="btn btn-active-color-primary shadow edit_icon custom_append_img" data-kt-image-input-action="change" data-bs-toggle="tooltip" data-bs-dismiss="click">
                                                    <div class="image-input-wrapper">
                                                        <img class="input_image_field" src="{{ asset('website') }}/{{ $client->back_image ?? 'default_image.jpg' }}" alt="Business Card Front" />
                                                    </div>

                                                    <div class="custom_upload_content">
                                                        <span><i class="fa-solid fa-image"></i></span>
                                                        <h4>Business Card Back</h4>
                                                        <p>Image of the back of your business card</p>
                                                    </div>

                                                    <input type="file" name="back_image" accept=".png, .jpg, .jpeg" class="myinput custom_file_input" />
                                                    <input type="hidden" name="avatar_remove" />
                                                </label>

                                                <span class="btn btn-icon btn-circle btn-active-color-primary shadow edit_icon" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" data-bs-dismiss="click" title="Cancel avatar">
                                                    <i class="ki-outline ki-cross fs-3"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Branches List Section for Staff --}}
                                    @if (!$client->is_child && $client->childClients && $client->childClients->count() > 0)
                                        <div class="col-md-12 mt-4">
                                            <div class="branches_list_section">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h4 class="mb-0">Branches ({{ $client->childClients->count() }})
                                                    </h4>
                                                    <a href="{{ route('branch.create', $client->id) }}" class="btn_global btn_blue btn-sm">
                                                        Add Branch <i class="fa-solid fa-plus"></i>
                                                    </a>
                                                </div>
                                                <div class="custom_table">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>Branch Name</th>
                                                                    <th>Address</th>
                                                                    <th>City</th>
                                                                    <th>Route</th>
                                                                    <th>Status</th>
                                                                    <th>Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($client->childClients as $branch)
                                                                    <tr>
                                                                        <td>{{ $branch->name ?? '-' }}</td>
                                                                        <td>{{ $branch->address ?? '-' }}</td>
                                                                        <td>{{ $branch->city ?? '-' }}</td>
                                                                        <td>{{ optional(optional($branch->clientRouteStaff->first())->route)->name ?? '-' }}
                                                                        </td>
                                                                        <td>
                                                                            <span class="badge {{ $branch->status ? 'bg-success' : 'bg-danger' }}">
                                                                                {{ $branch->status ? 'Active' : 'Inactive' }}
                                                                            </span>
                                                                        </td>
                                                                        <td>
                                                                            <div class="dropdown">
                                                                                <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                                    <i class="fa-solid fa-ellipsis"></i>
                                                                                </button>
                                                                                <ul class="dropdown-menu dropdown-menu-end">
                                                                                    <li>
                                                                                        <a class="dropdown-item" href="{{ route('clients.show', $branch->id) }}">View</a>
                                                                                    </li>
                                                                                    <li>
                                                                                        <a class="dropdown-item" href="{{ route('branch.edit', $branch->id) }}">Edit</a>
                                                                                    </li>
                                                                                    @if ($branch->clientRouteStaff && $branch->clientRouteStaff->count() > 0)
                                                                                        <li>
                                                                                            <a class="dropdown-item" href="{{ route('client-schedule', [$branch->id]) }}">Schedule</a>
                                                                                        </li>
                                                                                    @else
                                                                                        <li>
                                                                                            <a class="dropdown-item" href="{{ route('branch.edit', $branch->id) }}">Assign
                                                                                                Route</a>
                                                                                        </li>
                                                                                    @endif
                                                                                </ul>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @elseif(!$client->is_child)
                                        <div class="col-md-12 mt-4">
                                            <div class="branches_list_section">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h4 class="mb-0">Branches</h4>
                                                    <a href="{{ route('branch.create', $client->id) }}" class="btn_global btn_blue btn-sm">
                                                        Add Branch <i class="fa-solid fa-plus"></i>
                                                    </a>
                                                </div>
                                                <div class="text-center py-4" style="background: #f8f9fa; border-radius: 8px;">
                                                    <i class="fa-solid fa-code-branch" style="font-size: 40px; color: #ccc; margin-bottom: 10px;"></i>
                                                    <p class="text-muted mb-0">No branches available for this client.
                                                    </p>
                                                </div>
                                                <br>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="col-md-12">
                                        <div class="custom_justify_between">
                                            <input type="hidden" name="action" id="form_action_staff" value="update">
                                            <a href="{{ route('clients.index') }}" class="btn_global btn_grey">
                                                Cancel<i class="fa-solid fa-close"></i>
                                            </a>
                                            <div class="d-flex gap-3">
                                                <button type="submit" data-action="update" class="btn_global btn_blue submitButton">
                                                    Update<i class="fa-solid fa-floppy-disk ms-2"></i>
                                                </button>
                                                <button type="submit" data-action="update_and_schedule" class="btn_global btn_green submitButton">
                                                    Update & Schedule<i class="fa-solid fa-calendar-plus ms-2"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
@endsection
@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>

    <script>
        const phoneContainer = document.getElementById('phone-container');

        document.getElementById('add-phone-btn').addEventListener('click', function() {
            const original = phoneContainer.querySelector('.phone-group');
            const newEmailGroup = original.cloneNode(true);

            const input = newEmailGroup.querySelector('input');
            input.value = '';
            newEmailGroup.querySelector('.btn-remove-phone').style.display = 'inline-block';
            newEmailGroup.querySelector('.btn-add-phone').style.display = 'none';

            phoneContainer.appendChild(newEmailGroup);
        });

        phoneContainer.addEventListener('click', function(event) {
            if (event.target.closest('.btn-remove-phone')) {
                const phoneGroup = event.target.closest('.phone-group');
                if (phoneGroup) {
                    phoneContainer.removeChild(phoneGroup);
                }
            }
        });
    </script>

    <script>
        const container = document.getElementById('email-container');

        document.getElementById('add-email-btn').addEventListener('click', function() {
            const original = container.querySelector('.email-group');
            const newEmailGroup = original.cloneNode(true);

            const input = newEmailGroup.querySelector('input');
            input.value = '';
            newEmailGroup.querySelector('.btn-remove-email').style.display = 'inline-block';
            newEmailGroup.querySelector('.btn-add-email').style.display = 'none';

            container.appendChild(newEmailGroup);
        });

        container.addEventListener('click', function(event) {
            if (event.target.closest('.btn-remove-email')) {
                const emailGroup = event.target.closest('.email-group');
                if (emailGroup) {
                    container.removeChild(emailGroup);
                }
            }
        });

        $(document).on('click', '.btn-add-address', function() {
            const addressContainer = $(this).closest('.address-container');
            const original = addressContainer.find('.address-group').first();
            const newAddressGroup = original.clone();

            newAddressGroup.find('input').val('');
            newAddressGroup.find('select').val('');
            newAddressGroup.find('.btn-remove-address').show();
            newAddressGroup.find('.btn-add-address').hide();

            addressContainer.append(newAddressGroup);
        });

        $(document).on('click', '.btn-remove-address', function() {
            $(this).closest('.address-group').remove();
        });
    </script>
    <script>
        $(document).ready(function() {
            $('.address-container .address-group').hide();
            $('#branchCheckbox').change(function() {
                if ($(this).prop('checked')) {
                    $('.address-container .address-group').show();
                } else {
                    $('.address-container .address-group').hide();
                }
            });
        });
    </script>
    {{--    picture upload jquery --}}
    <script>
        $(document).ready(function() {
            // File Uploading Jquery
            // When the file input changes, update the corresponding image preview
            $('.custom_file_input').on('change', function() {
                // Get the file input and its corresponding image
                var input = $(this);
                var img = input.closest('.image-input').find('.input_image_field');

                // Update the image source
                var file = this.files[0];
                if (file) {
                    img.attr('src', URL.createObjectURL(file));
                }
            });

        });
    </script>

    <script>
        $(document).ready(function() {

            $('.cycle_frequency_wrapper .custom_dates .form-floating').hide();

            function updateDateInputs() {
                var selectedValue = $('input[name="service_frequency"]:checked').val();

                if (["normalWeek", "quarterly", "eightWeek", "monthly", "biMonthly"].includes(selectedValue)) {
                    $(".cycle_frequency_wrapper .custom_dates input[name='start_date']").attr("disabled", false)
                        .closest('.form-floating').show();

                    if (selectedValue === "biMonthly") {
                        $(".cycle_frequency_wrapper .custom_dates input[name='start_date_second']").attr("disabled",
                            false).closest('.form-floating').show();
                    } else {
                        $(".cycle_frequency_wrapper .custom_dates input[name='start_date_second']")
                            .attr("disabled", true)
                            .val("")
                            .closest('.form-floating')
                            .hide();
                    }
                } else {
                    $(".cycle_frequency_wrapper .custom_dates input[name='start_date'], .cycle_frequency_wrapper .custom_dates input[name='start_date_second']")
                        .attr("disabled", true)
                        .val("")
                        .closest('.form-floating')
                        .hide();
                }
            }

            updateDateInputs();

            $('.cycle_frequency_wrapper .radio_btn_wrapper input[type=radio]').change(updateDateInputs);


            var append_limit = @php echo count($client->clientHour); @endphp; // Initialize with the existing number of items

            // Handle adding more time slots
            $(document).on("click", ".cycle_frequency_wrapper .add_more_time button", function() {
                if (append_limit < 4) {
                    append_limit++;
                    $(this).closest(".cycle_frequency_wrapper").find(".append_service_time .appended_items")
                        .append(
                            `<div class="row appended_row_time custom_row" data-index="${append_limit}" style="margin-bottom:20px">

                <div class="col-md-6">
                    <div class="form-floating txt_field custom_dates">
                        <input type="time" class="form-control" name="best_time[${append_limit}][start_hour]" placeholder="">
                        <label>Starting Hour</label>
<!--                        <p>Please Select Starting Hour</p>-->
                    </div>
                </div>
                <div class="col-md-6">
             <div class="button_field_align">
       <div class="form-floating txt_field custom_dates">
                        <input type="time" class="form-control" name="best_time[${append_limit}][end_hour]" placeholder="">
                        <label>Ending Hour</label>
                    </div>
  <div class="remove_append_time">
                        <button type="button" class="btn_global btn_red"><i class="fa-solid fa-trash"></i></button>
                    </div>
</div>
                </div>
            </div>`
                        );

                    if (append_limit === 4) {
                        $(this).closest(".add_more_time").hide();
                    }
                }
            });

            // Handle removing the appended time slot
            $(document).on("click", ".cycle_frequency_wrapper .remove_append_time button", function() {
                $(this).closest(".appended_row_time").remove();
                append_limit--;

                $(".cycle_frequency_wrapper .add_more_time")
                    .show(); // Ensure the add button is shown if we can still add more
                // Update indices for remaining rows
                $(".cycle_frequency_wrapper .appended_row_time").each(function(index) {
                    $(this).attr("data-index", index + 1);
                    $(this).find("input[name^='best_time']").each(function() {
                        if ($(this).attr("name").includes("start_hour")) {
                            $(this).attr("name", `best_time[${index}][start_hour]`);
                        } else {
                            $(this).attr("name", `best_time[${index}][end_hour]`);
                        }
                    });
                });
            });


        });
    </script>

    <!-- Include Dropzone JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.js" referrerpolicy="no-referrer"></script>
    <!-- Initialize Dropzone -->
    <script>
        Dropzone.autoDiscover = false;

        const myDropzone = new Dropzone("#client_dropzone_image", {
            url: "#", // Update with actual endpoint URL for uploads
            paramName: "file",
            maxFilesize: 2, // Maximum file size in MB
            acceptedFiles: ".jpg,.jpeg,.png,.gif",
            dictDefaultMessage: '<i class="fa-solid fa-image"></i><h6>Upload Images</h6><p>Drag & drop or click to upload</p>',
            addRemoveLinks: true,
            dictRemoveFile: "Remove",
            init: function() {
                // Load existing images
                const existingImages = @json($client->clientImage); // Existing images data
                existingImages.forEach((image) => {
                    const mockFile = {
                        name: image.name, // Set the image name from database
                        size: 12345, // Placeholder size
                        accepted: true, // Mark as accepted
                        existing: true // Mark as an existing image
                    };

                    this.emit("addedfile", mockFile);
                    this.emit("thumbnail", mockFile, `{{ asset('website') }}/${image.name}`);
                    this.emit("complete", mockFile);

                    // Add hidden input for existing image
                    addExistingHiddenInput(image.name);
                });

                // Add new file
                this.on("addedfile", function(file) {
                    if (!file.existing) {
                        convertToBase64(file);
                    }
                });

                // Remove file (new or existing)
                this.on("removedfile", function(file) {
                    removeHiddenInput(file);
                });

                // Handle error on invalid file types
                this.on("error", function(file, message) {
                    if (message === "You can't upload files of this type.") {
                        alert("Invalid file type! Please upload a .jpg, .jpeg, .png, or .gif file.");
                        this.removeFile(file);
                    }
                });
            }
        });

        // Convert new file to Base64
        function convertToBase64(file) {
            const reader = new FileReader();
            reader.onloadend = function() {
                file.base64 = reader.result; // Store base64 data in file object
                addHiddenInput(file.base64); // Add hidden input for the new image
            };
            reader.readAsDataURL(file);
        }

        function addExistingHiddenInput(value) {
            const container = document.getElementById("hidden-input-container");
            const input = document.createElement("input");
            input.type = "hidden";
            input.name = "existing_image[]";
            input.value = value;
            container.appendChild(input);
        }

        // Add hidden input for an image
        function addHiddenInput(value) {
            const container = document.getElementById("hidden-input-container");
            const input = document.createElement("input");
            input.type = "hidden";
            input.name = "image[]";
            input.value = value;
            container.appendChild(input);
        }

        // Remove the corresponding hidden input
        function removeHiddenInput(file) {
            const container = document.getElementById("hidden-input-container");
            const inputs = container.querySelectorAll('input[name="existing_image[]"]');
            inputs.forEach(input => {
                if (file.base64 && input.value === file.base64) {
                    input.remove(); // Remove new image
                } else if (file.existing && input.value === file.name) {
                    input.remove(); // Remove existing image
                }
            });
        }
    </script>
    {{--    // Validation --}}
    <script>
        $(document).ready(function() {
            $("#clientValidate").validate({
                rules: {
                    name: {
                        required: true
                    },

                    start_date: {
                        required: true
                    },
                    start_date_second: {
                        required: true
                    }
                },
                messages: {
                    name: {
                        required: "Please enter client name."
                    },
                    start_date: {
                        required: "Please select start date."
                    },
                    start_date_second: {
                        required: "Please select second start date."
                    }
                },
                errorElement: "span",
                errorClass: "text-danger",

            });

            $('#client_name').on('input', function() {
                $('.duplicate-error').remove();
                $(this).removeClass("is-invalid");
            });

            // Clear email error on input for ALL email fields
            $(document).on('input', 'input[name^="email"]', function() {
                $(this).next('.duplicate-error').remove();
                $(this).removeClass("is-invalid");
            });

            // Clear phone error on input for ALL phone fields
            $(document).on('input', 'input[name^="phone"]', function() {
                $(this).next('.duplicate-error').remove();
                $(this).removeClass("is-invalid");
            });

            $(document).on('input', 'input[name="address[]"]', function() {
                $(this).removeClass('is-invalid');
            });

            $(document).on('change', 'select[name="route_id[]"]', function() {
                $(this).removeClass('is-invalid');
            });

            // Handle button clicks to set action value (for both admin and staff forms)
            $('.submitButton').on('click', function(e) {
                const action = $(this).data('action');

                // Set value for both hidden inputs (admin and staff)
                $('#form_action').val(action);
                $('#form_action_staff').val(action);

                console.log('Button clicked, action set to:', action);
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            function formatPhoneNumber(input) {
                input = input.replace(/\D/g, ''); // Remove non-numeric characters
                let formatted = "";

                if (input.length > 0) {
                    formatted = input.substring(0, 3);
                }
                if (input.length > 3) {
                    formatted += "-" + input.substring(3, 6);
                }
                if (input.length > 6) {
                    formatted += "-" + input.substring(6, 10);
                }
                if (input.length > 10) {
                    formatted += "" + input.substring(10);
                }

                return formatted;
            }

            $("#phone_number").on("input", function() {
                $(this).val(formatPhoneNumber($(this).val()));
            });

            let existingPhone = $("#phone_number").val();
            if (existingPhone) {
                $("#phone_number").val(formatPhoneNumber(existingPhone));
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        $(document).ready(function() {
            @php
                $startDateJs = '';
                if ($client->start_date) {
                    try {
                        $startDateJs = \Carbon\Carbon::createFromFormat('d/m/Y', $client->start_date)->format('m-d-Y');
                    } catch (\Exception $e) {
                        $startDateJs = '';
                    }
                }

                $secondStartDateJs = '';
                if ($client->second_start_date) {
                    try {
                        $secondStartDateJs = \Carbon\Carbon::createFromFormat('d/m/Y', $client->second_start_date)->format('m-d-Y');
                    } catch (\Exception $e) {
                        $secondStartDateJs = '';
                    }
                }
            @endphp

            let startDatePicker = flatpickr("#startDate", {
                dateFormat: "m-d-Y",
                defaultDate: "{{ $startDateJs }}",
                {{--                minDate: "{{$client->start_date ?? 'today'}}", --}}
                parseDate: (datestr, format) => {
                    const parts = datestr.split('-');
                    if (parts.length === 3) {
                        return new Date(parts[2], parts[0] - 1, parts[1]);
                    }
                    return new Date(datestr);
                },
                onChange: function(selectedDates) {
                    if (selectedDates.length > 0) {
                        let selectedDate = new Date(selectedDates[0]);

                        let nextMonday = new Date(selectedDate);
                        let dayOfWeek = selectedDate.getDay();
                        let daysToAdd = (dayOfWeek === 0) ? 1 : (7 - dayOfWeek + 1);

                        nextMonday.setDate(selectedDate.getDate() + daysToAdd);

                        startDateSecondPicker.set("minDate", nextMonday);

                        $("#startDateSecond").val("");
                    }
                }
            });

            let startDateSecondPicker = flatpickr("#startDateSecond", {
                dateFormat: "m-d-Y",
                defaultDate: "{{ $secondStartDateJs }}",
                parseDate: (datestr, format) => {
                    const parts = datestr.split('-');
                    if (parts.length === 3) {
                        return new Date(parts[2], parts[0] - 1, parts[1]);
                    }
                    return new Date(datestr);
                }
            });

            // Staff section second date picker
            let startDateSecondPickerStaff = flatpickr("#startDateSecondStaff", {
                dateFormat: "d/m/Y",
                defaultDate: "{{ $client->second_start_date ?? '' }}"
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
    <script>
        // Add contact info row in edit page
        $(document).on('click', '#add_contact_info', function() {
            const wrapper = $('#contact_info_wrapper');
            // Find highest existing row index
            let maxIndex = 0;
            wrapper.find('[class*="contact-row-"]').each(function() {
                const classes = $(this).attr('class').split(' ');
                classes.forEach(function(className) {
                    if (className.startsWith('contact-row-')) {
                        const index = parseInt(className.replace('contact-row-', ''));
                        if (index > maxIndex) {
                            maxIndex = index;
                        }
                    }
                });
            });
            const newIndex = maxIndex + 1;

            const newRow = `
                <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4 contact-row-${newIndex}">
                    <div class="form-floating txt_field mb-3">
                        <input type="text" class="form-control" name="contact_name[]" id="contact_name-${newIndex}" placeholder="">
                        <label for="contact_name-${newIndex}">Contact Name</label>
                    </div>
                </div>
                <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4 contact-row-${newIndex}">
                    <div class="form-floating txt_field mb-3">
                        <input type="text" class="form-control" name="phone[]" id="phone-${newIndex}" placeholder="">
                        <label for="phone-${newIndex}">Phone Number</label>
                    </div>
                </div>
                <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4 contact-row-${newIndex}">
                    <div class="form-floating txt_field mb-3">
                        <input type="text" class="form-control" name="positions[]" id="positions-${newIndex}" placeholder="">
                        <label for="positions-${newIndex}">Position In Company</label>
                    </div>
                </div>
                <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 contact-row-${newIndex}">
                    <div class="d-flex align-items-start mb-3">
                        <div class="form-floating txt_field flex-grow-1 me-2">
                            <input type="email" class="form-control" name="email[]" id="email-${newIndex}" placeholder="">
                            <label for="email-${newIndex}">Email</label>
                        </div>
                        <div class="form-check d-flex align-items-center ms-2" style="margin-top: 15px;">
                            <input class="form-check-input" type="checkbox" name="invoice_email_parent[0][]" value="${newIndex}">
                            <label class="form-check-label ms-1" title="Send invoice to this email">
                                <i class="fas fa-envelope"></i>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 contact-row-${newIndex}">
                    <div class="d-flex mb-3">
                        <div class="form-floating txt_field flex-grow-1 me-2">
                            <input type="text" class="form-control" name="note[0][]" id="note-${newIndex}" placeholder="">
                            <label for="note-${newIndex}">Note</label>
                        </div>
                        <button type="button" class="btn btn-danger btn_remove_contact_info" data-row="${newIndex}">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
            `;

            wrapper.append(newRow);
        });

        // Remove contact info row in edit page
        $(document).on('click', '.btn_remove_contact_info', function() {
            const rowIndex = $(this).data('row');
            console.log('Remove button clicked, row index:', rowIndex);
            $(`.contact-row-${rowIndex}`).remove();
        });
    </script>
    <script>
        // Add contact info row in edit page - STAFF SECTION
        $(document).on('click', '#add_contact_info_staff', function() {
            const wrapper = $('#contact_info_wrapper_staff');
            // Find highest existing row index
            let maxIndex = 0;
            wrapper.find('[class*="contact-row-"]').each(function() {
                const classes = $(this).attr('class').split(' ');
                classes.forEach(function(className) {
                    if (className.startsWith('contact-row-')) {
                        const index = parseInt(className.replace('contact-row-', ''));
                        if (index > maxIndex) {
                            maxIndex = index;
                        }
                    }
                });
            });
            const newIndex = maxIndex + 1;

            const newRow = `
                <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4 contact-row-${newIndex}">
                    <div class="form-floating txt_field mb-3">
                        <input type="text" class="form-control" name="contact_name[]" id="contact_name-${newIndex}" placeholder="">
                        <label for="contact_name-${newIndex}">Contact Name</label>
                    </div>
                </div>
                <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4 contact-row-${newIndex}">
                    <div class="form-floating txt_field mb-3">
                        <input type="text" class="form-control" name="phone[]" id="phone-${newIndex}" placeholder="">
                        <label for="phone-${newIndex}">Phone Number</label>
                    </div>
                </div>
                <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4 contact-row-${newIndex}">
                    <div class="form-floating txt_field mb-3">
                        <input type="text" class="form-control" name="positions[]" id="positions-${newIndex}" placeholder="">
                        <label for="positions-${newIndex}">Position In Company</label>
                    </div>
                </div>
                <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 contact-row-${newIndex}">
                    <div class="d-flex align-items-start mb-3">
                        <div class="form-floating txt_field flex-grow-1 me-2">
                            <input type="email" class="form-control" name="email[]" id="email-${newIndex}" placeholder="">
                            <label for="email-${newIndex}">Email</label>
                        </div>
                        <div class="form-check d-flex align-items-center ms-2" style="margin-top: 15px;">
                            <input class="form-check-input" type="checkbox" name="invoice_email_parent[0][]" value="${newIndex}">
                            <label class="form-check-label ms-1" title="Send invoice to this email">
                                <i class="fas fa-envelope"></i>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 contact-row-${newIndex}">
                    <div class="d-flex mb-3">
                        <div class="form-floating txt_field flex-grow-1 me-2">
                            <input type="text" class="form-control" name="note[0][]" id="note-${newIndex}" placeholder="">
                            <label for="note-${newIndex}">Note</label>
                        </div>
                        <button type="button" class="btn btn-danger btn_remove_contact_info" data-row="${newIndex}">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
            `;

            wrapper.append(newRow);
        });
    </script>
    <script>
        $(document).ready(function() {
            var customCount = {{ count($client->clientPrice) + 1 }};
            $(document).on('click', '.add_more_price_list', function(e) {
                var customLabel = 'Custom ' + String.fromCharCode(64 + customCount);
                var newRow = `
            <div class="price_list_append_row col-xxl-4 col-xl-6 col-lg-6 col-md-6">
                <div class="price_list">
                    <div class="input_text_filed_price_list">
                        <input type="text" class="form-control" value="${customLabel}" name="prices[${customCount}][side]" >
                    </div>
                    <div class="txt_field price_list_icon">
                        <i class="price_list_icon_doller fa-solid fa-dollar-sign"></i>
                        <input type="number" class="form-control" name="prices[${customCount}][number]">
                        <button type="button" class="btn_red btn_global delete_price_list">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>`;

                console.log('New row ready, appending...');
                $('.price_list_wrapper').append(newRow);

                customCount++;
            });
            $(document).on('click', '.delete_price_list', function() {
                $(this).closest('.price_list_append_row').remove();
            });
            if ($('.select2-multiple').length) {
                $('.select2-multiple').select2({
                    placeholder: "Select Route",
                    allowClear: true
                });
            }
        });
        $(document).ready(function() {
            // Function to initialize Select2
            function initializeSelect2() {
                $('.multiselect').each(function() {
                    // Check if Select2 is already initialized
                    if (!$(this).hasClass("select2-hidden-accessible")) {
                        $(this).select2({
                            placeholder: "Closed Day",
                            allowClear: true
                        });
                    }
                });
            }

            initializeSelect2();

            // Show/hide second start date based on frequency selection
            $(document).on('change', '.select_frequency .note-type-select', function() {
                var selectedValue = $(this).val();
                var firstDateDiv = $(this).closest('.select_frequency').prev('.first_start_date');
                var secondDateDiv = $(this).closest('.select_frequency').next('.second_start_date');

                if (selectedValue === 'biMonthly' || selectedValue === 'biAnnually') {
                    firstDateDiv.show();
                    secondDateDiv.show();
                } else {
                    firstDateDiv.hide(); // keep its value, it is still submitted
                    secondDateDiv.hide();
                    secondDateDiv.find('.startDateSecond').val('');
                }
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            // Format existing phone numbers from database on page load
            $('input[name^="phone"]').each(function() {
                $(this).attr('placeholder', '111-111-1111');

                let currentValue = $(this).val();
                if (currentValue && currentValue.trim() !== '') {
                    // Remove all non-digits
                    let digits = currentValue.replace(/\D/g, '');

                    // Limit to 10 digits
                    if (digits.length > 10) {
                        digits = digits.substring(0, 10);
                    }

                    // Format as XXX-XXX-XXXX
                    let formatted = '';
                    if (digits.length === 0) {
                        formatted = '';
                    } else if (digits.length <= 3) {
                        formatted = digits;
                    } else if (digits.length <= 6) {
                        formatted = digits.substring(0, 3) + '-' + digits.substring(3);
                    } else {
                        formatted = digits.substring(0, 3) + '-' + digits.substring(3, 6) + '-' + digits.substring(6);
                    }

                    $(this).val(formatted);
                }
            });

            // Auto-format phone as XXX-XXX-XXXX on user input only
            $(document).on("input", 'input[name^="phone"]', function(e) {
                // Check if this is a real user input (not programmatic)
                if (e.originalEvent === undefined) {
                    return; // Skip if programmatic change
                }

                let value = $(this).val();
                let cursorPos = this.selectionStart;

                // Remove all non-digits
                let digits = value.replace(/\D/g, '');

                // Limit to 10 digits
                if (digits.length > 10) {
                    digits = digits.substring(0, 10);
                }

                // Format as XXX-XXX-XXXX
                let formatted = '';
                if (digits.length === 0) {
                    formatted = '';
                } else if (digits.length <= 3) {
                    formatted = digits;
                } else if (digits.length <= 6) {
                    formatted = digits.substring(0, 3) + '-' + digits.substring(3);
                } else {
                    formatted = digits.substring(0, 3) + '-' + digits.substring(3, 6) + '-' + digits.substring(6);
                }

                // Only update if changed
                if (formatted !== value) {
                    $(this).val(formatted);

                    // Restore cursor position
                    let newCursorPos = cursorPos;
                    if (formatted.length > value.length) {
                        newCursorPos++;
                    }
                    this.setSelectionRange(newCursorPos, newCursorPos);
                }
            });
        });
    </script>
@endpush
