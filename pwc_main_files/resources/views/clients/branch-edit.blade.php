@extends('theme.layout.master')

@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/basic.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style></style>
@endpush

@section('navbar-title')
    <div class="custom_justify_between create_clients_navbar">
        <a href="{{ route('clients.edit', $branch->parent_id) }}" class="back_btn_navbar">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h2 class="navbar_PageTitle">Edit Branch</h2>
    </div>
@endsection

@section('content')
    @if (auth()->user()->hasRole('admin'))
        <section class="create_clients_sec">
            <div class="container-fluid custom_container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="create_clients_wrapper shadow_box_wrapper">
                            <form method="post" action="{{ route('branch.update', $branch->id) }}" class="form-horizontal validate" id="branchValidate" enctype="multipart/form-data">
                                @method('PUT')
                                @csrf
                                <div class="row">
                                    <div class="col-md-12">
                                        <h4 class="main_heading mb-0">Edit Branch</h4>
                                    </div>
                                    <!-- General Information Section -->
                                    <div class="col-md-12 general_info_container">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h4>General Information</h4>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="name" value="{{ $branch->name ?? '' }}" id="client_name" placeholder="">
                                                    <label for="client_name">Client Name *</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-floating txt_field mb-3">
                                                    <select name="client_type" id="" class="form-select">
                                                        <option value="" disabled @selected(empty($branch->client_type))>Client
                                                            Type</option>
                                                        <option value="residential" @selected($branch->client_type == 'residential')>Residential
                                                        </option>
                                                        <option value="commercial" @selected($branch->client_type == 'commercial')>Commercial
                                                        </option>
                                                    </select>
                                                    <label for="">Client Type</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="txt_field form-floating mb-3">
                                                    <select name="payment_type" id="" class="form-select">
                                                        <option value="" disabled @selected(empty($branch->payment_type))>Payment
                                                            Type</option>
                                                        <option value="cash" @selected($branch->payment_type == 'cash')>Cash</option>
                                                        <option value="invoice" @selected($branch->payment_type == 'invoice')>Invoice
                                                        </option>
                                                    </select>
                                                    <label for="">Payment Type</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-floating txt_field">
                                                    <input type="number" class="form-control" name="commission_percentage" value="{{ $branch->commission_percentage ?? '' }}" id="commission_percentage" placeholder="">
                                                    <label for="commission_percentage">Commission Percentage</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="d-flex align-items-start mb-3">
                                                    <div class="form-floating txt_field flex-grow-1 me-2">
                                                        @php
                                                            $startDateFormatted = '';
                                                            if ($branch->start_date) {
                                                                try {
                                                                    $startDateFormatted = \Carbon\Carbon::createFromFormat('d/m/Y', $branch->start_date)->format('m-d-Y');
                                                                } catch (\Exception $e) {
                                                                    $startDateFormatted = $branch->start_date;
                                                                }
                                                            }
                                                        @endphp
                                                        <input type="text" class="form-control" value="{{ $startDateFormatted }}" name="start_date" id="startDate" placeholder="mm-dd-yyyy" readonly>
                                                        <label for="start_date">Start Date *</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2 select_frequency">
                                                <div class="txt_field form-floating">
                                                    <select class="form-select note-type-select" name="service_frequency" id="">
                                                        <option value="" disabled @selected(empty($branch->service_frequency))>
                                                            Frequency *</option>
                                                        <option value="normalWeek" @selected($branch->service_frequency == 'normalWeek')>Weekly
                                                        </option>
                                                        <option value="biMonthly" @selected($branch->service_frequency == 'biMonthly')>biMonthly
                                                        </option>
                                                        <option value="monthly" @selected($branch->service_frequency == 'monthly')>Monthly
                                                        </option>
                                                        {{-- <option value="eightWeek" @selected($branch->service_frequency == 'eightWeek')>8 Weeks
                                                        </option>
                                                        <option value="quarterly" @selected($branch->service_frequency == 'quarterly')>12 Weeks
                                                        </option>
                                                        <option value="biAnnually" @selected($branch->service_frequency == 'biAnnually')>24 Weeks
                                                        </option> --}}
                                                        <option value="annually" @selected($branch->service_frequency == 'annually')>52 Weeks
                                                        </option>
                                                    </select>
                                                    <label for="">Frequency</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2 second_start_date" style="{{ in_array($branch->service_frequency, ['biMonthly', 'biAnnually']) ? '' : 'display:none' }}">
                                                <div class="d-flex align-items-start mb-3">
                                                    <div class="form-floating txt_field flex-grow-1 me-2">
                                                        @php
                                                            $secondStartDateFormatted = '';
                                                            if ($branch->second_start_date) {
                                                                try {
                                                                    $secondStartDateFormatted = \Carbon\Carbon::createFromFormat('d/m/Y', $branch->second_start_date)->format('m-d-Y');
                                                                } catch (\Exception $e) {
                                                                    $secondStartDateFormatted = $branch->second_start_date;
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
                                    <!-- Address Section -->
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h4>Address</h4>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="house_no" id="" placeholder="" value="{{ $branch->house_no ?? '' }}">
                                                    <label for="">Number</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2 mb-5">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="address" id="" placeholder="" value="{{ $branch->address ?? '' }}">
                                                    <label for="">Street *</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="city" id="" placeholder="" value="{{ $branch->city ?? '' }}">
                                                    <label for="">City *</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="state" id="" placeholder="" value="{{ $branch->state ?? '' }}">
                                                    <label for="">State</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-floating txt_field">
                                                    <input type="number" class="form-control" name="postal" id="" placeholder="" value="{{ $branch->postal ?? '' }}">
                                                    <label for="">Zip Code</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Contact Information Section -->
                                    <div class="col-md-12 contact_info_container" id="contact_info_container">
                                        <div class="contact_info">
                                            @php
                                                // Check if profile exists
                                                $hasProfile = $branch->profile ? true : false;

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

                                                // Get ALL data from profile and slice for display
                                                $allPhones = $hasProfile ? $toArray($branch->profile->additional_phones) : [];
                                                $allNames = $hasProfile ? $toArray($branch->profile->additional_names) : [];
                                                $allPositions = $hasProfile ? $toArray($branch->profile->additional_positions) : [];
                                                $allEmails = $hasProfile ? $toArray($branch->profile->additional_emails) : [];
                                                $allNotes = $hasProfile ? $toArray($branch->profile->additional_notes) : [];
                                                $invoiceEmails = $hasProfile ? $toArray($branch->profile->invoice_email) : [];

                                                // Slice from index 1 to skip first item (first item already shown in main row)
                                                $additionalPhones = array_slice($allPhones, 1);
                                                $additionalNames = array_slice($allNames, 1);
                                                $additionalPositions = array_slice($allPositions, 1);
                                                $additionalEmails = array_slice($allEmails, 1);
                                                $additionalNotes = array_slice($allNotes, 1);

                                                // First row values
                                                $firstContactName = $branch->contact_name ?? '';
                                                $firstPhone = $hasProfile ? $branch->profile->phone ?? '' : '';
                                                $firstPosition = $branch->position ?? '';
                                                $firstEmail = $branch->contact_email ?? '';
                                                $firstNote = $branch->description ?? '';

                                                // Check if first email is in invoice_email array
                                                $firstEmailChecked = in_array($firstEmail, $invoiceEmails);

                                                // Get max count
                                                $maxRows = max(count($additionalPhones), count($additionalNames), count($additionalEmails), count($additionalPositions), count($additionalNotes));
                                            @endphp
                                            <div class="row" id="contact_info_wrapper">
                                                <div class="col-md-12">
                                                    <h4>Contact Information</h4>
                                                </div>
                                                {{-- First Row --}}
                                                <div class="col-md-2">
                                                    <div class="form-floating txt_field mb-3">
                                                        <input type="text" class="form-control" name="contact_name[]" id="contact_name-0" placeholder="" value="{{ $firstContactName }}">
                                                        <label for="contact_name-0">Contact Name</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-floating txt_field mb-3">
                                                        <input type="text" class="form-control" name="phone[]" id="phone-0" placeholder="" value="{{ $firstPhone }}">
                                                        <label for="phone-0">Phone Number</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-floating txt_field mb-3">
                                                        <input type="text" class="form-control" name="positions[]" id="positions-0" placeholder="" value="{{ $firstPosition }}">
                                                        <label for="positions-0">Position In Company</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="d-flex align-items-start mb-3">
                                                        <div class="form-floating txt_field flex-grow-1 me-2">
                                                            <input type="email" class="form-control" name="email[]" id="email-0" placeholder="" value="{{ $firstEmail }}">
                                                            <label for="email-0">Invoice Email</label>
                                                        </div>
                                                        <div class="form-check d-flex align-items-center ms-2" style="margin-top: 15px;">
                                                            <input class="form-check-input" type="checkbox" name="invoice_email_branch[]" value="0" {{ $firstEmailChecked ? 'checked' : '' }}>
                                                            <label class="form-check-label ms-1" title="Send invoice to this email">
                                                                <i class="fas fa-envelope"></i>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="d-flex mb-3">
                                                        <div class="form-floating txt_field flex-grow-1 me-2">
                                                            <input type="text" class="form-control" name="note[]" id="note-0" placeholder="" value="{{ $firstNote }}">
                                                            <label for="note-0">Note</label>
                                                        </div>
                                                        <button type="button" id="add_contact_info" class="btn btn-primary btn_add_contact_info">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                {{-- Additional Rows --}}
                                                @for ($i = 0; $i < $maxRows; $i++)
                                                    @php
                                                        $currentEmail = isset($additionalEmails[$i]) && is_string($additionalEmails[$i]) ? $additionalEmails[$i] : '';
                                                        $emailChecked = in_array($currentEmail, $invoiceEmails);
                                                    @endphp
                                                    <div class="col-md-2 contact-row-{{ $i + 1 }}">
                                                        <div class="form-floating txt_field mb-3">
                                                            <input type="text" class="form-control" name="contact_name[]" id="contact_name-{{ $i + 1 }}" placeholder="" value="{{ isset($additionalNames[$i]) && is_string($additionalNames[$i]) ? $additionalNames[$i] : '' }}">
                                                            <label for="contact_name-{{ $i + 1 }}">Contact
                                                                Name</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2 contact-row-{{ $i + 1 }}">
                                                        <div class="form-floating txt_field mb-3">
                                                            <input type="text" class="form-control" name="phone[]" id="phone-{{ $i + 1 }}" placeholder="" value="{{ isset($additionalPhones[$i]) && is_string($additionalPhones[$i]) ? $additionalPhones[$i] : '' }}">
                                                            <label for="phone-{{ $i + 1 }}">Phone Number</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2 contact-row-{{ $i + 1 }}">
                                                        <div class="form-floating txt_field mb-3">
                                                            <input type="text" class="form-control" name="positions[]" id="positions-{{ $i + 1 }}" placeholder="" value="{{ isset($additionalPositions[$i]) && is_string($additionalPositions[$i]) ? $additionalPositions[$i] : '' }}">
                                                            <label for="positions-{{ $i + 1 }}">Position In
                                                                Company</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 contact-row-{{ $i + 1 }}">
                                                        <div class="d-flex align-items-start mb-3">
                                                            <div class="form-floating txt_field flex-grow-1 me-2">
                                                                <input type="email" class="form-control" name="email[]" id="email-{{ $i + 1 }}" placeholder="" value="{{ $currentEmail }}">
                                                                <label for="email-{{ $i + 1 }}">Invoice
                                                                    Email</label>
                                                            </div>
                                                            <div class="form-check d-flex align-items-center ms-2" style="margin-top: 15px;">
                                                                <input class="form-check-input" type="checkbox" name="invoice_email_branch[]" value="{{ $i + 1 }}" {{ $emailChecked ? 'checked' : '' }}>
                                                                <label class="form-check-label ms-1" title="Send invoice to this email">
                                                                    <i class="fas fa-envelope"></i>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 contact-row-{{ $i + 1 }}">
                                                        <div class="d-flex mb-3">
                                                            <div class="form-floating txt_field flex-grow-1 me-2">
                                                                <input type="text" class="form-control" name="note[]" id="note-{{ $i + 1 }}" placeholder="" value="{{ isset($additionalNotes[$i]) && is_string($additionalNotes[$i]) ? $additionalNotes[$i] : '' }}">
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
                                                    <textarea rows="4" class="form-control" name="additional_note" id="additional_note" placeholder="Additional Notes" style="height: 100px">{{ $branch->additional_note ?? '' }}</textarea>
                                                    <label for="additional_note">Notes (misc info about this client or job)</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Price Section -->
                                    <div class="col-md-6 price_list_custom_row">
                                        <h4>Price</h4>
                                        <div class="row price_list_wrapper" data-first-index="{{ count($branch->clientPrice ?? []) }}">
                                            @forelse ($branch->clientPrice->sortBy('position') as $index => $price)
                                                <div class="price_list_append_row col-md-4">
                                                    <div class="price_list editable_field">
                                                        <i class="fa-solid fa-pen-to-square edit_icon"></i>
                                                        <div class="input_text_filed_price_list">
                                                            <input type="text" class="form-control" value="{{ $price->name ?? '' }}" name="prices[{{ $index }}][side]">
                                                        </div>
                                                        <div class="txt_field price_list_icon">
                                                            <i class="price_list_icon_doller fa-solid fa-dollar-sign"></i>
                                                            <input type="number" class="form-control" value="{{ $price->value ?? 0 }}" name="prices[{{ $index }}][number]">
                                                            <button type="button" class="btn_red btn_global delete_price_list"><i class="fa-solid fa-trash"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="col-md-4">
                                                    <div class="price_list editable_field">
                                                        <i class="fa-solid fa-pen-to-square edit_icon"></i>
                                                        <div class="input_text_filed_price_list">
                                                            <input type="text" class="form-control" value="Interior" name="prices[0][side]" required>
                                                        </div>
                                                        <div class="txt_field price_list_icon">
                                                            <i class="price_list_icon_doller fa-solid fa-dollar-sign"></i>
                                                            <input type="number" class="form-control" value="0" name="prices[0][number]" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="price_list_append_row col-md-4">
                                                    <div class="price_list editable_field">
                                                        <i class="fa-solid fa-pen-to-square edit_icon"></i>
                                                        <div class="input_text_filed_price_list">
                                                            <input type="text" class="form-control" value="Exterior" name="prices[1][side]" required>
                                                        </div>
                                                        <div class="txt_field price_list_icon">
                                                            <i class="price_list_icon_doller fa-solid fa-dollar-sign"></i>
                                                            <input type="number" class="form-control" value="0" name="prices[1][number]" required>
                                                            <button type="button" class="btn_red btn_global delete_price_list"><i class="fa-solid fa-trash"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="price_list_append_row col-md-4">
                                                    <div class="price_list editable_field">
                                                        <i class="fa-solid fa-pen-to-square edit_icon"></i>
                                                        <div class="input_text_filed_price_list">
                                                            <input type="text" class="form-control" value="Interior & Exterior" name="prices[2][side]">
                                                        </div>
                                                        <div class="txt_field price_list_icon">
                                                            <i class="price_list_icon_doller fa-solid fa-dollar-sign"></i>
                                                            <input type="number" class="form-control" value="0" name="prices[2][number]">
                                                            <button type="button" class="btn_red btn_global delete_price_list"><i class="fa-solid fa-trash"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforelse
                                        </div>
                                        <button type="button" class="btn_global btn_blue add_more_price_list">Add
                                            Custom<i class="fa-solid fa-plus"></i></button>
                                    </div>
                                    <!-- Closed Days Section -->
                                    @php
                                        $branchClosedDays = $branch->clientDay->pluck('day')->toArray();
                                    @endphp
                                    <div class="col-md-6 select_two_field">
                                        <h4>Closed</h4>
                                        <div class="txt_field form-floating">
                                            <div class="custom_multi_select">
                                                <select multiple class="multiselect form-select note-type-select" name="unavail_day[]">
                                                    <option></option>
                                                    <option value="sunday" @selected(in_array('sunday', $branchClosedDays))>Sunday</option>
                                                    <option value="monday" @selected(in_array('monday', $branchClosedDays))>Monday</option>
                                                    <option value="tuesday" @selected(in_array('tuesday', $branchClosedDays))>Tuesday</option>
                                                    <option value="wednesday" @selected(in_array('wednesday', $branchClosedDays))>Wednesday
                                                    </option>
                                                    <option value="thursday" @selected(in_array('thursday', $branchClosedDays))>Thursday
                                                    </option>
                                                    <option value="friday" @selected(in_array('friday', $branchClosedDays))>Friday</option>
                                                    <option value="saturday" @selected(in_array('saturday', $branchClosedDays))>Saturday
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Best Times Section -->
                                    <div class="col-md-6">
                                        <div class="cycle_frequency_wrapper">
                                            @forelse ($branch->clientHour as $time)
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
                                                                <button type="button" class="btn_global btn_blue">Add<i class="fa-solid fa-plus"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
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
                                                                <button type="button" class="btn_global btn_blue">Add<i class="fa-solid fa-plus"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforelse
                                            <div class="create_client_cus_row row">
                                                <div class="col-md-6 append_service_time">
                                                    <div class="appended_items"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Route Selection -->
                                    {{-- @php
                                        $branchRouteIds = $branch->clientRouteStaff->pluck('route_id')->toArray();
                                    @endphp --}}
                                    <div class="col-md-6 mt-5">
                                        <div class="txt_field form-floating">
                                            {{-- Variable ka naam 'route' ki bajaye 'routes' ya 'availableRoutes' kar diya hai --}}
                                            <select class="form-select" name="route_id" aria-label="Default select">
                                                <option disabled {{ is_null($currentRouteId) ? 'selected' : '' }}>Select
                                                    Route</option>

                                                {{-- $routes variable use ho raha hai --}}
                                                @foreach ($route as $item)
                                                    <option value="{{ $item->id }}" {{ $item->id == $currentRouteId ? 'selected' : '' }}>
                                                        {{ $item->name }}
                                                    </option>
                                                @endforeach

                                            </select>
                                            <label for="">Assign Route</label>
                                        </div>
                                    </div>
                                    <!-- Dropzone Upload Images Section -->
                                    <div class="col-md-12 mt-4">
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
                                    <!-- Image Upload Section -->
                                    <div class="col-md-6 mt-4">
                                        <div class="client_upload_img custom_img_margin mb-5">
                                            <div class="image-input @if ($branch->front_image) image-input-changed @endif" data-kt-image-input="true">
                                                <label class="btn btn-active-color-primary shadow edit_icon custom_append_img" data-kt-image-input-action="change" data-bs-toggle="tooltip" data-bs-dismiss="click">
                                                    <div class="image-input-wrapper">
                                                        <img class="input_image_field" src="{{ asset('website') }}/{{ $branch->front_image ?? 'default_image.jpg' }}" alt="Business Card Front" />
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
                                            <div class="image-input @if ($branch->back_image) image-input-changed @endif" data-kt-image-input="true">
                                                <label class="btn btn-active-color-primary shadow edit_icon custom_append_img" data-kt-image-input-action="change" data-bs-toggle="tooltip" data-bs-dismiss="click">
                                                    <div class="image-input-wrapper">
                                                        <img class="input_image_field" src="{{ asset('website') }}/{{ $branch->back_image ?? 'default_image.jpg' }}" alt="Business Card Back" />
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
                                </div>
                                <div class="col-md-12">
                                    <div class="custom_justify_between mt-4">
                                        <input type="hidden" name="action" id="form_action" value="update">
                                        <a href="{{ route('clients.edit', $branch->parent_id) }}" class="btn_global btn_grey">
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
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- Staff Section --}}
    @if (auth()->user()->hasRole('staff'))
        <section class="create_clients_sec">
            <div class="container-fluid custom_container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="create_clients_wrapper shadow_box_wrapper">
                            <form method="post" action="{{ route('branch.update', $branch->id) }}" class="form-horizontal validate" id="branchValidateStaff" enctype="multipart/form-data">
                                @method('PUT')
                                @csrf
                                <div class="row">
                                    <div class="col-md-12">
                                        <h4 class="main_heading mb-0">Edit Branch</h4>
                                    </div>
                                    <!-- General Information Section -->
                                    <div class="col-md-12 general_info_container">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h4>General Information</h4>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="name" value="{{ $branch->name ?? '' }}" id="client_name" placeholder="">
                                                    <label for="client_name">Client Name *</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-floating txt_field mb-3">
                                                    <select name="client_type" id="" class="form-select">
                                                        <option value="" disabled @selected(empty($branch->client_type))>
                                                            Client
                                                            Type</option>
                                                        <option value="residential" @selected($branch->client_type == 'residential')>
                                                            Residential
                                                        </option>
                                                        <option value="commercial" @selected($branch->client_type == 'commercial')>Commercial
                                                        </option>
                                                    </select>
                                                    <label for="">Client Type</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="txt_field form-floating mb-3">
                                                    <select name="payment_type" id="" class="form-select">
                                                        <option value="" disabled @selected(empty($branch->payment_type))>
                                                            Payment
                                                            Type</option>
                                                        <option value="cash" @selected($branch->payment_type == 'cash')>Cash</option>
                                                        <option value="invoice" @selected($branch->payment_type == 'invoice')>Invoice
                                                        </option>
                                                    </select>
                                                    <label for="">Payment Type</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-floating txt_field">
                                                    <input type="number" class="form-control" name="commission_percentage" value="{{ $branch->commission_percentage ?? '' }}" id="commission_percentage" placeholder="">
                                                    <label for="commission_percentage">Commission Percentage</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="d-flex align-items-start mb-3">
                                                    <div class="form-floating txt_field flex-grow-1 me-2">
                                                        @php
                                                            $startDateFormatted = '';
                                                            if ($branch->start_date) {
                                                                try {
                                                                    $startDateFormatted = \Carbon\Carbon::createFromFormat('d/m/Y', $branch->start_date)->format('m-d-Y');
                                                                } catch (\Exception $e) {
                                                                    $startDateFormatted = $branch->start_date;
                                                                }
                                                            }
                                                        @endphp
                                                        <input type="text" class="form-control" value="{{ $startDateFormatted }}" name="start_date" id="startDateStaff" placeholder="mm-dd-yyyy" readonly>
                                                        <label for="start_date">Start Date *</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2 select_frequency">
                                                <div class="txt_field form-floating">
                                                    <select class="form-select note-type-select" name="service_frequency" id="">
                                                        <option value="" disabled @selected(empty($branch->service_frequency))>
                                                            Frequency *</option>
                                                        <option value="normalWeek" @selected($branch->service_frequency == 'normalWeek')>Weekly
                                                        </option>
                                                        <option value="biMonthly" @selected($branch->service_frequency == 'biMonthly')>biMonthly
                                                        </option>
                                                        <option value="monthly" @selected($branch->service_frequency == 'monthly')>Monthly
                                                        </option>
                                                        {{-- <option value="eightWeek" @selected($branch->service_frequency == 'eightWeek')>8 Weeks
                                                        </option>
                                                        <option value="quarterly" @selected($branch->service_frequency == 'quarterly')>12 Weeks
                                                        </option>
                                                        <option value="biAnnually" @selected($branch->service_frequency == 'biAnnually')>24 Weeks
                                                        </option> --}}
                                                        <option value="annually" @selected($branch->service_frequency == 'annually')>52 Weeks
                                                        </option>
                                                    </select>
                                                    <label for="">Frequency</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2 second_start_date" style="{{ in_array($branch->service_frequency, ['biMonthly', 'biAnnually']) ? '' : 'display:none' }}">
                                                <div class="d-flex align-items-start mb-3">
                                                    <div class="form-floating txt_field flex-grow-1 me-2">
                                                        @php
                                                            $secondStartDateFormatted = '';
                                                            if ($branch->second_start_date) {
                                                                try {
                                                                    $secondStartDateFormatted = \Carbon\Carbon::createFromFormat('d/m/Y', $branch->second_start_date)->format('m-d-Y');
                                                                } catch (\Exception $e) {
                                                                    $secondStartDateFormatted = $branch->second_start_date;
                                                                }
                                                            }
                                                        @endphp
                                                        <input type="text" class="form-control" value="{{ $secondStartDateFormatted }}" name="start_date_second" id="startDateSecondStaff" placeholder="mm-dd-yyyy" readonly>
                                                        <label for="startDateSecondStaff">Second Start Date</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Address Section -->
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h4>Address</h4>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="house_no" id="" placeholder="" value="{{ $branch->house_no ?? '' }}">
                                                    <label for="">Number</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2 mb-5">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="address" id="" placeholder="" value="{{ $branch->address ?? '' }}">
                                                    <label for="">Street *</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="city" id="" placeholder="" value="{{ $branch->city ?? '' }}">
                                                    <label for="">City *</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="state" id="" placeholder="" value="{{ $branch->state ?? '' }}">
                                                    <label for="">State</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-floating txt_field">
                                                    <input type="number" class="form-control" name="postal" id="" placeholder="" value="{{ $branch->postal ?? '' }}">
                                                    <label for="">Zip Code</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Contact Information Section -->
                                    <div class="col-md-12 contact_info_container" id="contact_info_container">
                                        <div class="contact_info">
                                            @php
                                                // Check if profile exists
                                                $hasProfile = $branch->profile ? true : false;

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

                                                // Get ALL data from profile and slice for display
                                                $allPhones = $hasProfile ? $toArray($branch->profile->additional_phones) : [];
                                                $allNames = $hasProfile ? $toArray($branch->profile->additional_names) : [];
                                                $allPositions = $hasProfile ? $toArray($branch->profile->additional_positions) : [];
                                                $allEmails = $hasProfile ? $toArray($branch->profile->additional_emails) : [];
                                                $allNotes = $hasProfile ? $toArray($branch->profile->additional_notes) : [];
                                                $invoiceEmails = $hasProfile ? $toArray($branch->profile->invoice_email) : [];

                                                // Slice from index 1 to skip first item (first item already shown in main row)
                                                $additionalPhones = array_slice($allPhones, 1);
                                                $additionalNames = array_slice($allNames, 1);
                                                $additionalPositions = array_slice($allPositions, 1);
                                                $additionalEmails = array_slice($allEmails, 1);
                                                $additionalNotes = array_slice($allNotes, 1);

                                                // First row values
                                                $firstContactName = $branch->contact_name ?? '';
                                                $firstPhone = $hasProfile ? $branch->profile->phone ?? '' : '';
                                                $firstPosition = $branch->position ?? '';
                                                $firstEmail = $branch->contact_email ?? '';
                                                $firstNote = $branch->description ?? '';

                                                // Check if first email is in invoice_email array
                                                $firstEmailChecked = in_array($firstEmail, $invoiceEmails);

                                                // Get max count
                                                $maxRows = max(count($additionalPhones), count($additionalNames), count($additionalEmails), count($additionalPositions), count($additionalNotes));
                                            @endphp
                                            <div class="row" id="contact_info_wrapper_staff">
                                                <div class="col-md-12">
                                                    <h4>Contact Information</h4>
                                                </div>
                                                {{-- First Row --}}
                                                <div class="col-md-2">
                                                    <div class="form-floating txt_field mb-3">
                                                        <input type="text" class="form-control" name="contact_name[]" id="contact_name-0" placeholder="" value="{{ $firstContactName }}">
                                                        <label for="contact_name-0">Contact Name</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-floating txt_field mb-3">
                                                        <input type="text" class="form-control" name="phone[]" id="phone-0" placeholder="" value="{{ $firstPhone }}">
                                                        <label for="phone-0">Phone Number</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-floating txt_field mb-3">
                                                        <input type="text" class="form-control" name="positions[]" id="positions-0" placeholder="" value="{{ $firstPosition }}">
                                                        <label for="positions-0">Position In Company</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="d-flex align-items-start mb-3">
                                                        <div class="form-floating txt_field flex-grow-1 me-2">
                                                            <input type="email" class="form-control" name="email[]" id="email-0" placeholder="" value="{{ $firstEmail }}">
                                                            <label for="email-0">Invoice Email</label>
                                                        </div>
                                                        <div class="form-check d-flex align-items-center ms-2" style="margin-top: 15px;">
                                                            <input class="form-check-input" type="checkbox" name="invoice_email_branch[]" value="0" {{ $firstEmailChecked ? 'checked' : '' }}>
                                                            <label class="form-check-label ms-1" title="Send invoice to this email">
                                                                <i class="fas fa-envelope"></i>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="d-flex mb-3">
                                                        <div class="form-floating txt_field flex-grow-1 me-2">
                                                            <input type="text" class="form-control" name="note[]" id="note-0" placeholder="" value="{{ $firstNote }}">
                                                            <label for="note-0">Note</label>
                                                        </div>
                                                        <button type="button" id="add_contact_info_staff" class="btn btn-primary btn_add_contact_info">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                {{-- Additional Rows --}}
                                                @for ($i = 0; $i < $maxRows; $i++)
                                                    @php
                                                        $currentEmail = isset($additionalEmails[$i]) && is_string($additionalEmails[$i]) ? $additionalEmails[$i] : '';
                                                        $emailChecked = in_array($currentEmail, $invoiceEmails);
                                                    @endphp
                                                    <div class="col-md-2 contact-row-{{ $i + 1 }}">
                                                        <div class="form-floating txt_field mb-3">
                                                            <input type="text" class="form-control" name="contact_name[]" id="contact_name-{{ $i + 1 }}" placeholder="" value="{{ isset($additionalNames[$i]) && is_string($additionalNames[$i]) ? $additionalNames[$i] : '' }}">
                                                            <label for="contact_name-{{ $i + 1 }}">Contact
                                                                Name</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2 contact-row-{{ $i + 1 }}">
                                                        <div class="form-floating txt_field mb-3">
                                                            <input type="text" class="form-control" name="phone[]" id="phone-{{ $i + 1 }}" placeholder="" value="{{ isset($additionalPhones[$i]) && is_string($additionalPhones[$i]) ? $additionalPhones[$i] : '' }}">
                                                            <label for="phone-{{ $i + 1 }}">Phone Number</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2 contact-row-{{ $i + 1 }}">
                                                        <div class="form-floating txt_field mb-3">
                                                            <input type="text" class="form-control" name="positions[]" id="positions-{{ $i + 1 }}" placeholder="" value="{{ isset($additionalPositions[$i]) && is_string($additionalPositions[$i]) ? $additionalPositions[$i] : '' }}">
                                                            <label for="positions-{{ $i + 1 }}">Position In
                                                                Company</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 contact-row-{{ $i + 1 }}">
                                                        <div class="d-flex align-items-start mb-3">
                                                            <div class="form-floating txt_field flex-grow-1 me-2">
                                                                <input type="email" class="form-control" name="email[]" id="email-{{ $i + 1 }}" placeholder="" value="{{ $currentEmail }}">
                                                                <label for="email-{{ $i + 1 }}">Invoice
                                                                    Email</label>
                                                            </div>
                                                            <div class="form-check d-flex align-items-center ms-2" style="margin-top: 15px;">
                                                                <input class="form-check-input" type="checkbox" name="invoice_email_branch[]" value="{{ $i + 1 }}" {{ $emailChecked ? 'checked' : '' }}>
                                                                <label class="form-check-label ms-1" title="Send invoice to this email">
                                                                    <i class="fas fa-envelope"></i>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 contact-row-{{ $i + 1 }}">
                                                        <div class="d-flex mb-3">
                                                            <div class="form-floating txt_field flex-grow-1 me-2">
                                                                <input type="text" class="form-control" name="note[]" id="note-{{ $i + 1 }}" placeholder="" value="{{ isset($additionalNotes[$i]) && is_string($additionalNotes[$i]) ? $additionalNotes[$i] : '' }}">
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
                                                    <textarea rows="4" class="form-control" name="additional_note" id="additional_note_staff" placeholder="Additional Notes" style="height: 100px">{{ $branch->additional_note ?? '' }}</textarea>
                                                    <label for="additional_note_staff">Notes (misc info about this client or job)</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Price Section -->
                                    <div class="col-md-6 price_list_custom_row">
                                        <h4>Price</h4>
                                        <div class="row price_list_wrapper" data-first-index="{{ count($branch->clientPrice ?? []) }}">
                                            @forelse ($branch->clientPrice as $index => $price)
                                                <div class="price_list_append_row col-md-4">
                                                    <div class="price_list editable_field">
                                                        <i class="fa-solid fa-pen-to-square edit_icon"></i>
                                                        <div class="input_text_filed_price_list">
                                                            <input type="text" class="form-control" value="{{ $price->name ?? '' }}" name="prices[{{ $index }}][side]">
                                                        </div>
                                                        <div class="txt_field price_list_icon">
                                                            <i class="price_list_icon_doller fa-solid fa-dollar-sign"></i>
                                                            <input type="number" class="form-control" value="{{ $price->value ?? 0 }}" name="prices[{{ $index }}][number]">
                                                            @if ($loop->index > 0)
                                                                <button type="button" class="btn_red btn_global delete_price_list"><i class="fa-solid fa-trash"></i></button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="col-md-4">
                                                    <div class="price_list editable_field">
                                                        <i class="fa-solid fa-pen-to-square edit_icon"></i>
                                                        <div class="input_text_filed_price_list">
                                                            <input type="text" class="form-control" value="Interior" name="prices[0][side]" required>
                                                        </div>
                                                        <div class="txt_field price_list_icon">
                                                            <i class="price_list_icon_doller fa-solid fa-dollar-sign"></i>
                                                            <input type="number" class="form-control" value="0" name="prices[0][number]" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="price_list_append_row col-md-4">
                                                    <div class="price_list editable_field">
                                                        <i class="fa-solid fa-pen-to-square edit_icon"></i>
                                                        <div class="input_text_filed_price_list">
                                                            <input type="text" class="form-control" value="Exterior" name="prices[1][side]" required>
                                                        </div>
                                                        <div class="txt_field price_list_icon">
                                                            <i class="price_list_icon_doller fa-solid fa-dollar-sign"></i>
                                                            <input type="number" class="form-control" value="0" name="prices[1][number]" required>
                                                            <button type="button" class="btn_red btn_global delete_price_list"><i class="fa-solid fa-trash"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="price_list_append_row col-md-4">
                                                    <div class="price_list editable_field">
                                                        <i class="fa-solid fa-pen-to-square edit_icon"></i>
                                                        <div class="input_text_filed_price_list">
                                                            <input type="text" class="form-control" value="Interior & Exterior" name="prices[2][side]">
                                                        </div>
                                                        <div class="txt_field price_list_icon">
                                                            <i class="price_list_icon_doller fa-solid fa-dollar-sign"></i>
                                                            <input type="number" class="form-control" value="0" name="prices[2][number]">
                                                            <button type="button" class="btn_red btn_global delete_price_list"><i class="fa-solid fa-trash"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforelse
                                        </div>
                                        <button type="button" class="btn_global btn_blue add_more_price_list">Add
                                            Custom<i class="fa-solid fa-plus"></i></button>
                                    </div>
                                    <!-- Closed Days Section -->
                                    @php
                                        $branchClosedDays = $branch->clientDay->pluck('day')->toArray();
                                    @endphp
                                    <div class="col-md-6 select_two_field">
                                        <h4>Closed</h4>
                                        <div class="txt_field form-floating">
                                            <div class="custom_multi_select">
                                                <select multiple class="multiselect form-select note-type-select" name="unavail_day[]">
                                                    <option></option>
                                                    <option value="sunday" @selected(in_array('sunday', $branchClosedDays))>Sunday</option>
                                                    <option value="monday" @selected(in_array('monday', $branchClosedDays))>Monday</option>
                                                    <option value="tuesday" @selected(in_array('tuesday', $branchClosedDays))>Tuesday</option>
                                                    <option value="wednesday" @selected(in_array('wednesday', $branchClosedDays))>Wednesday
                                                    </option>
                                                    <option value="thursday" @selected(in_array('thursday', $branchClosedDays))>Thursday
                                                    </option>
                                                    <option value="friday" @selected(in_array('friday', $branchClosedDays))>Friday</option>
                                                    <option value="saturday" @selected(in_array('saturday', $branchClosedDays))>Saturday
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Best Times Section -->
                                    <div class="col-md-6">
                                        <div class="cycle_frequency_wrapper">
                                            @forelse ($branch->clientHour as $time)
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
                                                                <button type="button" class="btn_global btn_blue">Add<i class="fa-solid fa-plus"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
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
                                                                <button type="button" class="btn_global btn_blue">Add<i class="fa-solid fa-plus"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforelse
                                            <div class="create_client_cus_row row">
                                                <div class="col-md-6 append_service_time">
                                                    <div class="appended_items"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Route Selection -->
                                    {{-- @php
                                        $branchRouteIds = $branch->clientRouteStaff->pluck('route_id')->toArray();
                                    @endphp --}}
                                    <div class="col-md-6 mt-5">
                                        <div class="txt_field form-floating">
                                            {{-- Variable ka naam 'route' ki bajaye 'routes' ya 'availableRoutes' kar diya hai --}}
                                            <select class="form-select" name="route_id" aria-label="Default select">
                                                <option disabled {{ is_null($currentRouteId) ? 'selected' : '' }}>Select
                                                    Route</option>

                                                {{-- $routes variable use ho raha hai --}}
                                                @foreach ($route as $item)
                                                    <option value="{{ $item->id }}" {{ $item->id == $currentRouteId ? 'selected' : '' }}>
                                                        {{ $item->name }}
                                                    </option>
                                                @endforeach

                                            </select>
                                            <label for="">Assign Route</label>
                                        </div>
                                    </div>
                                    <!-- Dropzone Upload Images Section -->
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
                                    <!-- Image Upload Section -->
                                    <div class="col-md-6 mt-4">
                                        <div class="client_upload_img custom_img_margin mb-5">
                                            <div class="image-input @if ($branch->front_image) image-input-changed @endif" data-kt-image-input="true">
                                                <label class="btn btn-active-color-primary shadow edit_icon custom_append_img" data-kt-image-input-action="change" data-bs-toggle="tooltip" data-bs-dismiss="click">
                                                    <div class="image-input-wrapper">
                                                        <img class="input_image_field" src="{{ asset('website') }}/{{ $branch->front_image ?? 'default_image.jpg' }}" alt="Business Card Front" />
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
                                            <div class="image-input @if ($branch->back_image) image-input-changed @endif" data-kt-image-input="true">
                                                <label class="btn btn-active-color-primary shadow edit_icon custom_append_img" data-kt-image-input-action="change" data-bs-toggle="tooltip" data-bs-dismiss="click">
                                                    <div class="image-input-wrapper">
                                                        <img class="input_image_field" src="{{ asset('website') }}/{{ $branch->back_image ?? 'default_image.jpg' }}" alt="Business Card Back" />
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
                                </div>
                                <div class="col-md-12">
                                    <div class="custom_justify_between">
                                        <input type="hidden" name="action" id="form_action_staff" value="update">
                                        <a href="{{ route('clients.edit', $branch->parent_id) }}" class="btn_global btn_grey">
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


            var append_limit = @php echo count($branch->clientHour); @endphp; // Initialize with the existing number of items

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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.js" referrerpolicy="no-referrer"></script>
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
                const existingImages = @json($branch->clientImage); // Existing images data
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

                submitHandler: function(form) {
                    Swal.fire({
                        title: 'Please wait',
                        text: 'Processing request, this may take a few seconds...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const name = $('#client_name').val();
                    const nameInput = $('#client_name');
                    const nameErrorSpan = $('#name-error');

                    if (nameErrorSpan.length) {
                        nameErrorSpan.remove();
                    }
                    nameInput.removeClass("is-invalid");

                    $('.submitButton').prop('disabled', true);

                    $.ajax({
                        url: "{{ url('check_client_name') }}",
                        type: "GET",
                        data: {
                            name: name,
                            type: 'edit',
                            client_id: '{{ $branch->id }}',
                            user_id: '{{ $branch->user_id ?? '' }}'
                        },
                        success: function(response) {
                            if (response.exists) {
                                Swal.close();

                                nameInput.after(
                                    '<span id="name-error" class="text-danger">This name already exists.</span>'
                                );
                                nameInput.addClass("is-invalid");
                                $('.submitButton').prop('disabled', false);

                                $('html, body').animate({
                                    scrollTop: nameInput.offset().top - 100
                                }, 800);

                            } else {
                                validateAddressAndEmail(form);
                            }
                        }
                    });

                    function validateAddressAndEmail(form) {
                        let isValid = true;
                        let errorMessage = "";
                        $('.address-group').each(function(index) {
                            const addressInput = $(this).find('input[name="address[]"]');
                            const addressVal = addressInput.val();
                            const address = addressVal ? addressVal.trim() : '';
                            const route = $(this).find('select[name="route_id[]"]').val();

                            if (address && !route) {
                                isValid = false;
                                errorMessage = "Please select a route for address: " + address;
                                $(this).find('select[name="route_id[]"]').addClass(
                                    'is-invalid');
                                return false; // Break the loop
                            }

                            if (route && !address) {
                                isValid = false;
                                errorMessage =
                                    "Please enter an address for the selected route.";
                                $(this).find('input[name="address[]"]').addClass('is-invalid');
                                return false; // Break the loop
                            }
                        });

                        if (!isValid) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Validation Error',
                                text: errorMessage,
                                confirmButtonText: 'OK'
                            });
                            return false;
                        }

                        const email = $('#email-0').val();
                        const emailInput = $('#email-0');
                        const emailErrorSpan = $('#email-error');

                        if (emailErrorSpan.length) {
                            emailErrorSpan.remove();
                        }


                        form.submit();
                    }

                }
            });

            $('#client_name').on('input', function() {
                $('#name-error').remove();
                $(this).removeClass("is-invalid");
            });

            $(document).on('input', 'input[name="address[]"]', function() {
                $(this).removeClass('is-invalid');
            });

            $(document).on('change', 'select[name="route_id[]"]', function() {
                $(this).removeClass('is-invalid');
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
                if ($branch->start_date) {
                    try {
                        $startDateJs = \Carbon\Carbon::createFromFormat('d/m/Y', $branch->start_date)->format('m-d-Y');
                    } catch (\Exception $e) {
                        $startDateJs = '';
                    }
                }

                $secondStartDateJs = '';
                if ($branch->second_start_date) {
                    try {
                        $secondStartDateJs = \Carbon\Carbon::createFromFormat('d/m/Y', $branch->second_start_date)->format('m-d-Y');
                    } catch (\Exception $e) {
                        $secondStartDateJs = '';
                    }
                }
            @endphp

            let startDatePicker = flatpickr("#startDate", {
                dateFormat: "m-d-Y",
                defaultDate: "{{ $startDateJs }}",
                {{--                minDate: "{{$branch->start_date ?? 'today'}}", --}}
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

            // Staff section date pickers
            let startDatePickerStaff = flatpickr("#startDateStaff", {
                dateFormat: "m-d-Y",
                defaultDate: "{{ $startDateJs }}",
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

                        startDateSecondPickerStaff.set("minDate", nextMonday);

                        $("#startDateSecondStaff").val("");
                    }
                }
            });

            let startDateSecondPickerStaff = flatpickr("#startDateSecondStaff", {
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
        });
    </script>
    <script>
        $(document).ready(function() {
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
    <script>
        // Add contact info row in branch edit page - ADMIN SECTION
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
                <div class="col-md-2 contact-row-${newIndex}">
                    <div class="form-floating txt_field mb-3">
                        <input type="text" class="form-control" name="contact_name[]" id="contact_name-${newIndex}" placeholder="">
                        <label for="contact_name-${newIndex}">Contact Name</label>
                    </div>
                </div>
                <div class="col-md-2 contact-row-${newIndex}">
                    <div class="form-floating txt_field mb-3">
                        <input type="text" class="form-control" name="phone[]" id="phone-${newIndex}" placeholder="">
                        <label for="phone-${newIndex}">Phone Number</label>
                    </div>
                </div>
                <div class="col-md-2 contact-row-${newIndex}">
                    <div class="form-floating txt_field mb-3">
                        <input type="text" class="form-control" name="positions[]" id="positions-${newIndex}" placeholder="">
                        <label for="positions-${newIndex}">Position In Company</label>
                    </div>
                </div>
                <div class="col-md-3 contact-row-${newIndex}">
                    <div class="d-flex align-items-start mb-3">
                        <div class="form-floating txt_field flex-grow-1 me-2">
                            <input type="email" class="form-control" name="email[]" id="email-${newIndex}" placeholder="">
                            <label for="email-${newIndex}">Invoice Email</label>
                        </div>
                        <div class="form-check d-flex align-items-center ms-2" style="margin-top: 15px;">
                            <input class="form-check-input" type="checkbox" name="invoice_email_branch[]" value="${newIndex}" checked>
                            <label class="form-check-label ms-1" title="Send invoice to this email">
                                <i class="fas fa-envelope"></i>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 contact-row-${newIndex}">
                    <div class="d-flex mb-3">
                        <div class="form-floating txt_field flex-grow-1 me-2">
                            <input type="text" class="form-control" name="note[]" id="note-${newIndex}" placeholder="">
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

        // Remove contact info row in branch edit page
        $(document).on('click', '.btn_remove_contact_info', function() {
            const rowIndex = $(this).data('row');
            console.log('Remove button clicked, row index:', rowIndex);
            $(`.contact-row-${rowIndex}`).remove();
        });

        // Add contact info row in branch edit page - STAFF SECTION
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

            const newContactInfoGroup = `
                <div class="col-md-2 contact-row-${newIndex}">
                    <div class="txt_field form-floating">
                        <input type="text" class="form-control contact_phone" name="contact_phone[]" id="contact_phone-${newIndex}" placeholder="" data-raw="">
                        <label for="contact_phone-${newIndex}">Phone</label>
                    </div>
                </div>
                <div class="col-md-2 contact-row-${newIndex}">
                    <div class="txt_field form-floating">
                        <input type="text" class="form-control" name="contact_name[]" id="contact_name-${newIndex}" placeholder="">
                        <label for="contact_name-${newIndex}">Name</label>
                    </div>
                </div>
                <div class="col-md-2 contact-row-${newIndex}">
                    <div class="txt_field form-floating">
                        <input type="text" class="form-control" name="contact_title[]" id="contact_title-${newIndex}" placeholder="">
                        <label for="contact_title-${newIndex}">Title</label>
                    </div>
                </div>
                <div class="col-md-2 contact-row-${newIndex}">
                    <div class="txt_field form-floating">
                        <input type="email" class="form-control" name="contact_email[]" id="contact_email-${newIndex}" placeholder="">
                        <label for="contact_email-${newIndex}">Email</label>
                    </div>
                </div>
                <div class="col-md-2 contact-row-${newIndex}">
                    <div class="txt_field form-floating">
                        <input type="text" class="form-control" name="note[]" id="note-${newIndex}" placeholder="">
                        <label for="note-${newIndex}">Note</label>
                    </div>
                    <button type="button" class="btn btn-danger btn_remove_contact_info" data-row="${newIndex}">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            `;

            wrapper.append(newContactInfoGroup);
        });
    </script>
    <script>
        $(document).ready(function() {
            var customCount = {{ count($branch->clientPrice) + 1 }};
            $(document).on('click', '.add_more_price_list', function(e) {
                var customLabel = 'Custom ' + String.fromCharCode(64 + customCount);
                var newRow = `
            <div class="price_list_append_row col-md-4">
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
                var secondDateDiv = $(this).closest('.select_frequency').next('.second_start_date');

                if (selectedValue === 'biMonthly' || selectedValue === 'biAnnually') {
                    secondDateDiv.show();
                } else {
                    secondDateDiv.hide();
                    secondDateDiv.find('.startDateSecond').val('');
                }
            });
        });

        // Form Validation - Admin
        $(document).ready(function() {
            $("#branchValidate").validate({
                rules: {
                    name: {
                        required: true
                    },
                    client_type: {
                        required: true
                    },
                    payment_type: {
                        required: true
                    },
                    commission_percentage: {
                        required: true
                    },
                    service_frequency: {
                        required: true
                    }
                },
                messages: {
                    name: {
                        required: "Please Enter Client Name"
                    },
                    client_type: {
                        required: "Please select Client Type"
                    },
                    payment_type: {
                        required: "Please select Payment Type"
                    },
                    commission_percentage: {
                        required: "Please enter Commission Percentage"
                    },
                    service_frequency: {
                        required: "Please select Service Frequency"
                    }
                },
                errorElement: "span",
                errorClass: "text-danger",
                invalidHandler: function(event, validator) {
                    if (validator.numberOfInvalids()) {
                        $('html, body').animate({
                            scrollTop: $(validator.errorList[0].element).offset().top - 100
                        }, 800);
                    }
                },
                // submitHandler: function(form) {
                //     const name = $('#client_name').val();
                //     const clientId = "{{ $branch->id ?? '' }}";

                //     // Collect ALL emails and phones from all contact info rows
                //     let allEmails = [];
                //     let allPhones = [];

                //     $('input[name^="email"]').each(function() {
                //         let val = $(this).val();
                //         if (val && val.trim() !== '') {
                //             allEmails.push({
                //                 value: val.toLowerCase().trim(),
                //                 element: $(this)
                //             });
                //         }
                //     });

                //     $('input[name^="phone"]').each(function() {
                //         let val = $(this).val();
                //         if (val && val.trim() !== '') {
                //             // Clean phone for comparison (remove dashes)
                //             let cleanPhone = val.replace(/\D/g, '');
                //             allPhones.push({
                //                 value: val,
                //                 cleanValue: cleanPhone,
                //                 element: $(this)
                //             });
                //         }
                //     });

                //     const nameInput = $('#client_name');

                //     // Remove previous errors
                //     $('.duplicate-error').remove();
                //     $('input[name^="email"], input[name^="phone"]').removeClass("is-invalid");
                //     nameInput.removeClass("is-invalid");

                //     $('.submitButton').prop('disabled', true);

                //     let hasError = false;

                //     // Check for INTERNAL duplicates (within the same form)
                //     // Check duplicate emails
                //     let emailValues = {};
                //     allEmails.forEach(function(emailObj, index) {
                //         if (emailValues[emailObj.value]) {
                //             emailObj.element.after(
                //                 '<span class="duplicate-error text-danger">Email already used above.</span>'
                //             );
                //             emailObj.element.addClass("is-invalid");
                //             hasError = true;
                //         } else {
                //             emailValues[emailObj.value] = true;
                //         }
                //     });

                //     // Check duplicate phones
                //     let phoneValues = {};
                //     allPhones.forEach(function(phoneObj, index) {
                //         if (phoneValues[phoneObj.cleanValue]) {
                //             phoneObj.element.after(
                //                 '<span class="duplicate-error text-danger">Phone already used above.</span>'
                //             );
                //             phoneObj.element.addClass("is-invalid");
                //             hasError = true;
                //         } else {
                //             phoneValues[phoneObj.cleanValue] = true;
                //         }
                //     });

                //     // If internal duplicates found, stop here
                //     if (hasError) {
                //         $('.submitButton').prop('disabled', false);
                //         const firstError = $('.is-invalid').first();
                //         $('html, body').animate({
                //             scrollTop: firstError.offset().top - 100
                //         }, 800);
                //         return;
                //     }

                //     // Check each email and phone for duplicates (database check)
                //     let checksCompleted = 0;
                //     let totalChecks = allEmails.length + allPhones.length + 1; // +1 for name

                //     // Function to check if all validations are done
                //     function checkIfComplete() {
                //         checksCompleted++;
                //         if (checksCompleted === totalChecks) {
                //             if (!hasError) {
                //                 // All good - submit form
                //                 Swal.fire({
                //                     title: 'Please wait',
                //                     text: 'Processing request...',
                //                     allowOutsideClick: false,
                //                     didOpen: () => {
                //                         Swal.showLoading();
                //                     }
                //                 });
                //                 form.submit();
                //             } else {
                //                 // Scroll to first error
                //                 $('.submitButton').prop('disabled', false);
                //                 const firstError = $('.is-invalid').first();
                //                 $('html, body').animate({
                //                     scrollTop: firstError.offset().top - 100
                //                 }, 800);
                //             }
                //         }
                //     }

                //     // Check name
                //     $.ajax({
                //         url: "{{ url('check_client_name') }}",
                //         type: "GET",
                //         data: {
                //             name: name,
                //             type: "edit",
                //             client_id: clientId
                //         },
                //         success: function(response) {
                //             if (response.name_exists) {
                //                 nameInput.after(
                //                     '<span class="duplicate-error text-danger">Name already exists.</span>'
                //                 );
                //                 nameInput.addClass("is-invalid");
                //                 hasError = true;
                //             }
                //             checkIfComplete();
                //         }
                //     });

                //     // Check each email
                //     allEmails.forEach(function(emailObj) {
                //         $.ajax({
                //             url: "{{ url('check_client_name') }}",
                //             type: "GET",
                //             data: {
                //                 contact_email: emailObj.value,
                //                 type: "edit",
                //                 client_id: clientId
                //             },
                //             success: function(response) {
                //                 if (response.email_exists) {
                //                     emailObj.element.after(
                //                         '<span class="duplicate-error text-danger">Email already exists.</span>'
                //                     );
                //                     emailObj.element.addClass("is-invalid");
                //                     hasError = true;
                //                 }
                //                 checkIfComplete();
                //             }
                //         });
                //     });

                //     // Check each phone
                //     allPhones.forEach(function(phoneObj) {
                //         $.ajax({
                //             url: "{{ url('check_client_name') }}",
                //             type: "GET",
                //             data: {
                //                 phone_number: phoneObj.value,
                //                 type: "edit",
                //                 client_id: clientId
                //             },
                //             success: function(response) {
                //                 if (response.phone_exists) {
                //                     phoneObj.element.after(
                //                         '<span class="duplicate-error text-danger">Phone already exists.</span>'
                //                     );
                //                     phoneObj.element.addClass("is-invalid");
                //                     hasError = true;
                //                 }
                //                 checkIfComplete();
                //             }
                //         });
                //     });
                // }
                // submitHandler: function(form) {
                //     const name = $('#client_name').val();
                //     const clientId = "{{ $branch->id ?? '' }}";
                //     const nameInput = $('#client_name');

                //     // Remove previous errors
                //     $('.duplicate-error').remove();
                //     nameInput.removeClass("is-invalid");

                //     $('.submitButton').prop('disabled', true);

                //     let hasError = false;
                //     let checksCompleted = 0;
                //     let totalChecks = 1; // Sirf name check

                //     function checkIfComplete() {
                //         checksCompleted++;
                //         if (checksCompleted === totalChecks) {
                //             if (!hasError) {
                //                 Swal.fire({
                //                     title: 'Please wait',
                //                     text: 'Processing request...',
                //                     allowOutsideClick: false,
                //                     didOpen: () => {
                //                         Swal.showLoading();
                //                     }
                //                 });
                //                 form.submit();
                //             } else {
                //                 $('.submitButton').prop('disabled', false);
                //                 const firstError = $('.is-invalid').first();
                //                 $('html, body').animate({
                //                     scrollTop: firstError.offset().top - 100
                //                 }, 800);
                //             }
                //         }
                //     }

                //     // Sirf name check
                //     $.ajax({
                //         url: "{{ url('check_client_name') }}",
                //         type: "GET",
                //         data: {
                //             name: name,
                //             type: "edit",
                //             client_id: clientId
                //         },
                //         success: function(response) {
                //             if (response.name_exists) {
                //                 nameInput.after(
                //                     '<span class="duplicate-error text-danger">Name already exists.</span>'
                //                 );
                //                 nameInput.addClass("is-invalid");
                //                 hasError = true;
                //             }
                //             checkIfComplete();
                //         }
                //     });
                // }
            });

            // Staff Form Validation
            $("#branchValidateStaff").validate({
                rules: {
                    name: {
                        required: true
                    },
                    client_type: {
                        required: true
                    },
                    payment_type: {
                        required: true
                    },
                    commission_percentage: {
                        required: true
                    },
                    service_frequency: {
                        required: true
                    }
                },
                messages: {
                    name: {
                        required: "Please Enter Client Name"
                    },
                    client_type: {
                        required: "Please select Client Type"
                    },
                    payment_type: {
                        required: "Please select Payment Type"
                    },
                    commission_percentage: {
                        required: "Please enter Commission Percentage"
                    },
                    service_frequency: {
                        required: "Please select Service Frequency"
                    }
                },
                errorElement: "span",
                errorClass: "text-danger",
                invalidHandler: function(event, validator) {
                    if (validator.numberOfInvalids()) {
                        $('html, body').animate({
                            scrollTop: $(validator.errorList[0].element).offset().top - 100
                        }, 800);
                    }
                },
                submitHandler: function(form) {
                    const name = $('#client_name_staff').val();
                    const clientId = "{{ $branch->id ?? '' }}";

                    // Collect ALL emails and phones from all contact info rows
                    let allEmails = [];
                    let allPhones = [];

                    $('input[name^="email"]').each(function() {
                        let val = $(this).val();
                        if (val && val.trim() !== '') {
                            allEmails.push({
                                value: val.toLowerCase().trim(),
                                element: $(this)
                            });
                        }
                    });

                    $('input[name^="phone"]').each(function() {
                        let val = $(this).val();
                        if (val && val.trim() !== '') {
                            // Clean phone for comparison (remove dashes)
                            let cleanPhone = val.replace(/\D/g, '');
                            allPhones.push({
                                value: val,
                                cleanValue: cleanPhone,
                                element: $(this)
                            });
                        }
                    });

                    const nameInput = $('#client_name_staff');

                    // Remove previous errors
                    $('.duplicate-error').remove();
                    $('input[name^="email"], input[name^="phone"]').removeClass("is-invalid");
                    nameInput.removeClass("is-invalid");

                    $('.submitButton').prop('disabled', true);

                    let hasError = false;

                    // Check for INTERNAL duplicates (within the same form)
                    // Check duplicate emails
                    let emailValues = {};
                    allEmails.forEach(function(emailObj, index) {
                        if (emailValues[emailObj.value]) {
                            emailObj.element.after(
                                '<span class="duplicate-error text-danger">Email already used above.</span>'
                            );
                            emailObj.element.addClass("is-invalid");
                            hasError = true;
                        } else {
                            emailValues[emailObj.value] = true;
                        }
                    });

                    // Check duplicate phones
                    let phoneValues = {};
                    allPhones.forEach(function(phoneObj, index) {
                        if (phoneValues[phoneObj.cleanValue]) {
                            phoneObj.element.after(
                                '<span class="duplicate-error text-danger">Phone already used above.</span>'
                            );
                            phoneObj.element.addClass("is-invalid");
                            hasError = true;
                        } else {
                            phoneValues[phoneObj.cleanValue] = true;
                        }
                    });

                    // If internal duplicates found, stop here
                    if (hasError) {
                        $('.submitButton').prop('disabled', false);
                        const firstError = $('.is-invalid').first();
                        $('html, body').animate({
                            scrollTop: firstError.offset().top - 100
                        }, 800);
                        return;
                    }

                    // Check each email and phone for duplicates (database check)
                    let checksCompleted = 0;
                    let totalChecks = allEmails.length + allPhones.length + 1; // +1 for name

                    // Function to check if all validations are done
                    function checkIfComplete() {
                        checksCompleted++;
                        if (checksCompleted === totalChecks) {
                            if (!hasError) {
                                // All good - submit form
                                Swal.fire({
                                    title: 'Please wait',
                                    text: 'Processing request...',
                                    allowOutsideClick: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    }
                                });
                                form.submit();
                            } else {
                                // Scroll to first error
                                $('.submitButton').prop('disabled', false);
                                const firstError = $('.is-invalid').first();
                                $('html, body').animate({
                                    scrollTop: firstError.offset().top - 100
                                }, 800);
                            }
                        }
                    }

                    // Check name
                    $.ajax({
                        url: "{{ url('check_client_name') }}",
                        type: "GET",
                        data: {
                            name: name,
                            type: "edit",
                            client_id: clientId
                        },
                        success: function(response) {
                            if (response.name_exists) {
                                nameInput.after(
                                    '<span class="duplicate-error text-danger">Name already exists.</span>'
                                );
                                nameInput.addClass("is-invalid");
                                hasError = true;
                            }
                            checkIfComplete();
                        }
                    });

                    // Check each email
                    allEmails.forEach(function(emailObj) {
                        $.ajax({
                            url: "{{ url('check_client_name') }}",
                            type: "GET",
                            data: {
                                contact_email: emailObj.value,
                                type: "edit",
                                client_id: clientId
                            },
                            success: function(response) {
                                if (response.email_exists) {
                                    emailObj.element.after(
                                        '<span class="duplicate-error text-danger">Email already exists.</span>'
                                    );
                                    emailObj.element.addClass("is-invalid");
                                    hasError = true;
                                }
                                checkIfComplete();
                            }
                        });
                    });

                    // Check each phone
                    allPhones.forEach(function(phoneObj) {
                        $.ajax({
                            url: "{{ url('check_client_name') }}",
                            type: "GET",
                            data: {
                                phone_number: phoneObj.value,
                                type: "edit",
                                client_id: clientId
                            },
                            success: function(response) {
                                if (response.phone_exists) {
                                    phoneObj.element.after(
                                        '<span class="duplicate-error text-danger">Phone already exists.</span>'
                                    );
                                    phoneObj.element.addClass("is-invalid");
                                    hasError = true;
                                }
                                checkIfComplete();
                            }
                        });
                    });
                }
            });

            // Clear error on input for client name
            $('#client_name, #client_name_staff').on('input', function() {
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
