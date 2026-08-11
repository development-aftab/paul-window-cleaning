@extends('theme.layout.master')
@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/basic.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .btn>i {
            padding-right: 0px !important;
        }

        .create_clients_sec .create_client_cus_row {
            row-gap: 0px !important;
        }
    </style>
@endpush
@section('navbar-title')
    <div class="custom_justify_between create_clients_navbar">
        <a href="{{ route('clients.edit', $parent_id) }}" class="back_btn_navbar">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h2 class="navbar_PageTitle">Add Branch</h2>
    </div>
@endsection
@section('content')
    @if (auth()->user()->hasRole('admin'))
        <section class="create_clients_sec">
            <div class="container-fluid custom_container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="create_clients_wrapper shadow_box_wrapper">
                            <form method="post" action="{{ route('branch.store', $parent_id) }}" class="form-horizontal" id="branchValidate" enctype="multipart/form-data">
                                @csrf
                                <div class="col-md-12">
                                    <h4 class="main_heading mb-0">Add Branch for {{ $parent->name }}</h4>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 general_info_container">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h4>General Information</h4>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="name" id="client_name" placeholder="" required>
                                                    <label for="client_name">Client Name *</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-floating txt_field mb-3">
                                                    <select name="client_type" id="" class="form-select" required>
                                                        <option value="" selected disabled>Client Type</option>
                                                        <option value="residential">Residential</option>
                                                        <option value="commercial">Commercial</option>
                                                    </select>
                                                    <label for="">Client Type *</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="txt_field form-floating mb-3">
                                                    <select name="payment_type" id="" class="form-select" required>
                                                        <option value="" selected disabled>Payment Type</option>
                                                        <option value="cash">Cash</option>
                                                        <option value="invoice">Invoice</option>
                                                    </select>
                                                    <label for="">Payment Type *</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-floating txt_field">
                                                    <input type="number" class="form-control commission_percentage" name="commission_percentage" id="commission_percentage" placeholder="" required value="50">
                                                    <label for="commission_percentage">Commission Percentage *</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="d-flex align-items-start mb-3">
                                                    <div class="form-floating txt_field flex-grow-1 me-2">
                                                        <input type="date" class="form-control startDate" name="start_date" placeholder="" required>
                                                        <label for="">Start Date *</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2 select_frequency">
                                                <div class="txt_field form-floating">
                                                    <select class="form-select note-type-select" name="service_frequency" id="">
                                                        <option value="normalWeek">Weekly</option>
                                                        <option value="biMonthly">biMonthly</option>
                                                        <option value="monthly">Monthly</option>
                                                        {{-- <option value="eightWeek">8 Weeks</option>
                                                        <option value="quarterly">12 Weeks</option>
                                                        <option value="biAnnually">24 Weeks</option> --}}
                                                        <option value="annually">52 Weeks</option>
                                                    </select>
                                                    <label for="">Frequency *</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2 second_start_date" style="display: none">
                                                <div class="d-flex align-items-start mb-3">
                                                    <div class="form-floating txt_field flex-grow-1 me-2">
                                                        <input type="date" class="form-control startDateSecond" name="start_date_second" placeholder="" required>
                                                        <label for="">Second Starting Date *</label>
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
                                                    <input type="text" class="form-control" name="house_no" id="" placeholder="">
                                                    <label for="">Number</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2 mb-5">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="street" id="" placeholder="">
                                                    <label for="">Street</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="city" id="" placeholder="">
                                                    <label for="">City</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="state" id="" placeholder="">
                                                    <label for="">State</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-floating txt_field">
                                                    <input type="number" class="form-control" name="postal" id="" placeholder="">
                                                    <label for="">Zip Code</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Contact Information Section -->
                                    <div class="col-md-12 contact_info_container" id="contact_info_container">
                                        <div class="contact_info">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <h4>Contact Information</h4>
                                                </div>
                                                <div id="contact_name_container" class="col-md-2">
                                                    <div class="phone-group d-flex align-items-start mb-3">
                                                        <div class="form-floating txt_field flex-grow-1 me-2">
                                                            <input type="text" class="form-control" name="contact_name[]" id="contact_name" placeholder="">
                                                            <label for="contact_name">Contact Name</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="phone-container" class="col-md-2">
                                                    <div class="phone-group d-flex align-items-start mb-3">
                                                        <div class="form-floating txt_field flex-grow-1 me-2">
                                                            <input type="text" class="form-control" name="phone[]" id="phone_number" placeholder="">
                                                            <label for="phone_number">Phone Number</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="position-container" class="col-md-2">
                                                    <div class="phone-group d-flex align-items-start mb-3">
                                                        <div class="form-floating txt_field flex-grow-1 me-2">
                                                            <input type="text" class="form-control" name="positions[]" id="positions" placeholder="">
                                                            <label for="positions">Position In Company</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="email-container" class="col-md-3">
                                                    <div class="d-flex align-items-start mb-3">
                                                        <div class="form-floating txt_field flex-grow-1 me-2">
                                                            <input type="email" class="form-control" name="email[]" id="email-0" placeholder="">
                                                            <label for="email-0">Invoice Email</label>
                                                        </div>
                                                        <div class="form-check d-flex align-items-center ms-2" style="margin-top: 15px;">
                                                            <input class="form-check-input" type="checkbox" name="invoice_email_branch[]" value="0" checked>
                                                            <label class="form-check-label ms-1" title="Send invoice to this email">
                                                                <i class="fas fa-envelope"></i>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="d-flex mb-3">
                                                        <div class="form-floating txt_field flex-grow-1 me-2">
                                                            <input type="text" class="form-control" name="note[]" id="" placeholder="">
                                                            <label for="">Note</label>
                                                        </div>
                                                        <button type="button" id="add_contact_info" class="btn btn-primary btn_add_contact_info">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn_remove_contact_info" style="display: none;">
                                                            <i class="fas fa-minus"></i>
                                                        </button>
                                                    </div>
                                                </div>
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
                                                    <textarea rows="4" class="form-control" name="additional_note" id="additional_note" placeholder="Additional Notes" style="height: 100px"></textarea>
                                                    <label for="additional_note">Notes (misc info about this client or job)</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Price Section -->
                                    <div class="col-md-6 price_list_custom_row">
                                        <h4>Price</h4>
                                        <div class="row price_list_wrapper" data-first-index="0">
                                            <div class="col-md-4">
                                                <div class="price_list editable_field">
                                                    <i class="fa-solid fa-pen-to-square edit_icon"></i>
                                                    <div class="input_text_filed_price_list">
                                                        <input type="text" class="form-control" value="Interior" name="prices[0][side]" required>
                                                    </div>
                                                    <div class="txt_field price_list_icon">
                                                        <i class="price_list_icon_doller fa-solid fa-dollar-sign"></i>
                                                        <input type="number" class="form-control" value="0" name="prices[0][number]" required>
                                                        <button type="button" class="btn_red btn_global delete_price_list"><i class="fa-solid fa-trash"></i></button>
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
                                        </div>
                                        <button type="button" class="btn_global btn_blue add_more_price_list">Add
                                            Custom<i class="fa-solid fa-plus"></i></button>
                                    </div>
                                    <!-- Closed Days Section -->
                                    <div class="col-md-6 select_two_field">
                                        <h4>Closed</h4>
                                        <div class="txt_field form-floating">
                                            <div class="custom_multi_select">
                                                <select multiple class="multiselect form-select note-type-select" name="unavail_day[]">
                                                    <option></option>
                                                    <option value="sunday">Sunday</option>
                                                    <option value="monday">Monday</option>
                                                    <option value="tuesday">Tuesday</option>
                                                    <option value="wednesday">Wednesday</option>
                                                    <option value="thursday">Thursday</option>
                                                    <option value="friday">Friday</option>
                                                    <option value="saturday">Saturday</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Best Times Section -->
                                    <div class="col-md-12">
                                        <div class="cycle_frequency_wrapper">
                                            <div class="row create_client_cus_row">
                                                <div class="col-md-6 row append_service_time">
                                                    <div class="col-md-6 mt-2">
                                                        <div class="form-floating txt_field custom_dates">
                                                            <input type="time" class="form-control" name="best_time[0][start_hour]" id="startHour" placeholder="">
                                                            <label for="startHour">Starting Hour</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mt-2">
                                                        <div class="button_field_align">
                                                            <div class="form-floating txt_field custom_dates">
                                                                <input type="time" class="form-control" name="best_time[0][end_hour]" id="endHour" placeholder="">
                                                                <label for="endHour">Ending Hour</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="appended_items"></div>
                                                    <div class="add_more_time">
                                                        <button type="button" class="btn_global btn_blue float-start">Add<i class="fa-solid fa-plus"></i></button>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mt-2">
                                                    <div class="txt_field form-floating">
                                                        <select class="form-select" name="route_id" aria-label="Default select">
                                                            <option disabled selected>Select Route</option>
                                                            @foreach ($route as $item)
                                                                <option value="{{ $item->id }}">{{ $item->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <label for="">Assign Route</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Dropzone Upload Images Section -->
                                    <div class="col-md-12 mt-4">
                                        <div class="client_upload_img">
                                            <div class="dropzone dz-clickable" id="admin_dropzone_image">
                                                <div class="dz-default dz-message">
                                                    <button class="dz-button" type="button">
                                                        <i class="fa-solid fa-image"></i>
                                                        <h6>Upload Images</h6>
                                                        <p>Drag & drop or click to upload</p>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="admin-hidden-input-container"></div>
                                    </div>
                                    <!-- Image Upload Section -->
                                    <div class="col-md-6 create_client_cus_row">
                                        <div class="client_upload_img custom_img_margin">
                                            <div class="image-input" data-kt-image-input="true">
                                                <label class="btn btn-active-color-primary shadow edit_icon custom_append_img" data-kt-image-input-action="change" data-bs-toggle="tooltip" data-bs-dismiss="click">
                                                    <div class="image-input-wrapper">
                                                        <img class="input_image_field" src="">
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
                                    <div class="col-md-6 create_client_cus_row">
                                        <div class="client_upload_img custom_img_margin">
                                            <div class="image-input" data-kt-image-input="true">
                                                <label class="btn btn-active-color-primary shadow edit_icon custom_append_img" data-kt-image-input-action="change" data-bs-toggle="tooltip" data-bs-dismiss="click">
                                                    <div class="image-input-wrapper">
                                                        <img class="input_image_field" src="" data-original-src="{{ asset('website') }}/assets/images/create_client_img_plus_sign.png">
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
                                <div class="col-md-12 create_client_cus_row">
                                    <div class="d-flex gap-3">
                                        <input type="hidden" name="action" id="form_action" value="create">
                                        <button type="submit" data-action="create" class="btn_global btn_blue submitButton">
                                            Save Branch<i class="fa-solid fa-floppy-disk ms-2"></i>
                                        </button>
                                        <button type="submit" data-action="create_and_schedule" class="btn_global btn_green submitButton">
                                            Save & Schedule<i class="fa-solid fa-calendar-plus ms-2"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if (auth()->user()->hasRole('staff'))
        <section class="create_clients_sec">
            <div class="container-fluid custom_container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="create_clients_wrapper shadow_box_wrapper">
                            <form method="post" action="{{ route('branch.store', $parent_id) }}" class="form-horizontal" id="branchValidateStaff" enctype="multipart/form-data">
                                @csrf
                                <div class="col-md-12">
                                    <h4 class="main_heading mb-0">Add Branch for {{ $parent->name }}</h4>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 general_info_container">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h4>General Information</h4>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="name" id="client_name_staff" placeholder="" required>
                                                    <label for="client_name_staff">Client Name *</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-floating txt_field mb-3">
                                                    <select name="client_type" id="" class="form-select" required>
                                                        <option value="" selected disabled>Client Type</option>
                                                        <option value="residential">Residential</option>
                                                        <option value="commercial">Commercial</option>
                                                    </select>
                                                    <label for="">Client Type *</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="txt_field form-floating mb-3">
                                                    <select name="payment_type" id="" class="form-select" required>
                                                        <option value="" selected disabled>Payment Type</option>
                                                        <option value="cash">Cash</option>
                                                        <option value="invoice">Invoice</option>
                                                    </select>
                                                    <label for="">Payment Type *</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-floating txt_field">
                                                    <input type="number" class="form-control commission_percentage" name="commission_percentage" id="commission_percentage_staff" placeholder="" required value="50">
                                                    <label for="commission_percentage_staff">Commission Percentage
                                                        *</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="d-flex align-items-start mb-3">
                                                    <div class="form-floating txt_field flex-grow-1 me-2">
                                                        <input type="date" class="form-control startDate" name="start_date" placeholder="" required>
                                                        <label for="">Start Date *</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2 select_frequency">
                                                <div class="txt_field form-floating">
                                                    <select class="form-select note-type-select" name="service_frequency" id="">
                                                        <option value="normalWeek">Weekly</option>
                                                        <option value="biMonthly">biMonthly</option>
                                                        <option value="monthly">Monthly</option>
                                                        {{-- <option value="eightWeek">8 Weeks</option>
                                                        <option value="quarterly">12 Weeks</option>
                                                        <option value="biAnnually">24 Weeks</option> --}}
                                                        <option value="annually">52 Weeks</option>
                                                    </select>
                                                    <label for="">Frequency *</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2 second_start_date" style="display: none">
                                                <div class="d-flex align-items-start mb-3">
                                                    <div class="form-floating txt_field flex-grow-1 me-2">
                                                        <input type="date" class="form-control startDateSecond" name="start_date_second" placeholder="" required>
                                                        <label for="">Second Starting Date *</label>
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
                                                    <input type="text" class="form-control" name="house_no" id="" placeholder="">
                                                    <label for="">Number</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2 mb-5">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="street" id="" placeholder="">
                                                    <label for="">Street</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="city" id="" placeholder="">
                                                    <label for="">City</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="state" id="" placeholder="">
                                                    <label for="">State</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-floating txt_field">
                                                    <input type="number" class="form-control" name="postal" id="" placeholder="">
                                                    <label for="">Zip Code</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Contact Information Section -->
                                    <div class="col-md-12 contact_info_container" id="contact_info_container_staff">
                                        <div class="contact_info">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <h4>Contact Information</h4>
                                                </div>
                                                <div id="contact_name_container" class="col-md-2">
                                                    <div class="phone-group d-flex align-items-start mb-3">
                                                        <div class="form-floating txt_field flex-grow-1 me-2">
                                                            <input type="text" class="form-control" name="contact_name[]" id="contact_name_staff" placeholder="">
                                                            <label for="contact_name_staff">Contact Name</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="phone-container" class="col-md-2">
                                                    <div class="phone-group d-flex align-items-start mb-3">
                                                        <div class="form-floating txt_field flex-grow-1 me-2">
                                                            <input type="text" class="form-control" name="phone[]" id="phone_number_staff" placeholder="">
                                                            <label for="phone_number_staff">Phone Number</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="position-container" class="col-md-2">
                                                    <div class="phone-group d-flex align-items-start mb-3">
                                                        <div class="form-floating txt_field flex-grow-1 me-2">
                                                            <input type="text" class="form-control" name="positions[]" id="positions_staff" placeholder="">
                                                            <label for="positions_staff">Position In Company</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="email-container" class="col-md-3">
                                                    <div class="d-flex align-items-start mb-3">
                                                        <div class="form-floating txt_field flex-grow-1 me-2">
                                                            <input type="email" class="form-control" name="email[]" id="email-staff" placeholder="">
                                                            <label for="email-staff">Invoice Email</label>
                                                        </div>
                                                        <div class="form-check d-flex align-items-center ms-2" style="margin-top: 15px;">
                                                            <input class="form-check-input" type="checkbox" name="invoice_email_branch[]" value="0" checked>
                                                            <label class="form-check-label ms-1" title="Send invoice to this email">
                                                                <i class="fas fa-envelope"></i>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="d-flex mb-3">
                                                        <div class="form-floating txt_field flex-grow-1 me-2">
                                                            <input type="text" class="form-control" name="note[]" id="" placeholder="">
                                                            <label for="">Note</label>
                                                        </div>
                                                        <button type="button" id="add_contact_info_staff" class="btn btn-primary btn_add_contact_info">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn_remove_contact_info" style="display: none;">
                                                            <i class="fas fa-minus"></i>
                                                        </button>
                                                    </div>
                                                </div>
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
                                                    <textarea rows="4" class="form-control" name="additional_note" id="additional_note_staff" placeholder="Additional Notes" style="height: 100px"></textarea>
                                                    <label for="additional_note_staff">Notes (misc info about this client or job)</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Price Section -->
                                    <div class="col-md-6 price_list_custom_row">
                                        <h4>Price</h4>
                                        <div class="row price_list_wrapper" data-first-index="0">
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
                                        </div>
                                        <button type="button" class="btn_global btn_blue add_more_price_list">Add
                                            Custom<i class="fa-solid fa-plus"></i></button>
                                    </div>
                                    <!-- Closed Days Section -->
                                    <div class="col-md-6 select_two_field">
                                        <h4>Closed</h4>
                                        <div class="txt_field form-floating">
                                            <div class="custom_multi_select">
                                                <select multiple class="multiselect form-select note-type-select" name="unavail_day[]">
                                                    <option></option>
                                                    <option value="sunday">Sunday</option>
                                                    <option value="monday">Monday</option>
                                                    <option value="tuesday">Tuesday</option>
                                                    <option value="wednesday">Wednesday</option>
                                                    <option value="thursday">Thursday</option>
                                                    <option value="friday">Friday</option>
                                                    <option value="saturday">Saturday</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Best Times Section -->
                                    <div class="col-md-12">
                                        <div class="cycle_frequency_wrapper">
                                            <div class="row create_client_cus_row">
                                                <div class="col-md-6 row append_service_time">
                                                    <div class="col-md-6 mt-2">
                                                        <div class="form-floating txt_field custom_dates">
                                                            <input type="time" class="form-control" name="best_time[0][start_hour]" id="startHour_staff" placeholder="">
                                                            <label for="startHour_staff">Starting Hour</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mt-2">
                                                        <div class="button_field_align">
                                                            <div class="form-floating txt_field custom_dates">
                                                                <input type="time" class="form-control" name="best_time[0][end_hour]" id="endHour_staff" placeholder="">
                                                                <label for="endHour_staff">Ending Hour</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="appended_items"></div>
                                                    <div class="add_more_time">
                                                        <button type="button" class="btn_global btn_blue float-start">Add<i class="fa-solid fa-plus"></i></button>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mt-2">
                                                    <div class="txt_field form-floating">
                                                        <select class="form-select" name="route_id" aria-label="Default select">
                                                            <option disabled selected>Select Route</option>
                                                            @foreach ($route as $item)
                                                                <option value="{{ $item->id }}">{{ $item->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <label for="">Assign Route</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Dropzone Upload Images Section -->
                                    <div class="col-md-12 mt-4">
                                        <div class="client_upload_img">
                                            <div class="dropzone dz-clickable" id="staff_dropzone_image">
                                                <div class="dz-default dz-message">
                                                    <button class="dz-button" type="button">
                                                        <i class="fa-solid fa-image"></i>
                                                        <h6>Upload Images</h6>
                                                        <p>Drag & drop or click to upload</p>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="staff-hidden-input-container"></div>
                                    </div>
                                    <!-- Image Upload Section -->
                                    <div class="col-md-6 create_client_cus_row">
                                        <div class="client_upload_img custom_img_margin">
                                            <div class="image-input" data-kt-image-input="true">
                                                <label class="btn btn-active-color-primary shadow edit_icon custom_append_img" data-kt-image-input-action="change" data-bs-toggle="tooltip" data-bs-dismiss="click">
                                                    <div class="image-input-wrapper">
                                                        <img class="input_image_field" src="">
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
                                    <div class="col-md-6 create_client_cus_row">
                                        <div class="client_upload_img custom_img_margin">
                                            <div class="image-input" data-kt-image-input="true">
                                                <label class="btn btn-active-color-primary shadow edit_icon custom_append_img" data-kt-image-input-action="change" data-bs-toggle="tooltip" data-bs-dismiss="click">
                                                    <div class="image-input-wrapper">
                                                        <img class="input_image_field" src="" data-original-src="{{ asset('website') }}/assets/images/create_client_img_plus_sign.png">
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
                                <div class="col-md-12 create_client_cus_row">
                                    <div class="d-flex gap-3">
                                        <input type="hidden" name="action" id="form_action_staff" value="create">
                                        <button type="submit" data-action="create" class="btn_global btn_blue submitButton">
                                            Save Branch<i class="fa-solid fa-floppy-disk ms-2"></i>
                                        </button>
                                        <button type="submit" data-action="create_and_schedule" class="btn_global btn_green submitButton">
                                            Save & Schedule<i class="fa-solid fa-calendar-plus ms-2"></i>
                                        </button>
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
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.multiselect').select2({
                placeholder: "Select Days",
                allowClear: true
            });

            // Set default date to 01/01/2025
            let defaultStartDate = new Date(2025, 0, 6); // January 1, 2025

            // Calculate second start date (7 days after first start date)
            let defaultSecondStartDate = new Date(2025, 0, 1);
            defaultSecondStartDate.setDate(defaultSecondStartDate.getDate() + 7); // Add 7 days = 01/08/2025

            let startDatePicker = flatpickr(".startDate", {
                dateFormat: "m/d/Y",
                defaultDate: defaultStartDate,
                onChange: function(selectedDates) {
                    if (selectedDates.length > 0) {
                        let selectedDate = new Date(selectedDates[0]);

                        // Calculate 7 days after selected date
                        let secondDate = new Date(selectedDate);
                        secondDate.setDate(selectedDate.getDate() + 7);

                        // Update second date picker with new date (7 days after)
                        startDateSecondPicker.setDate(secondDate);
                        startDateSecondPicker.set("minDate", secondDate);
                    }
                }
            });

            let startDateSecondPicker = flatpickr(".startDateSecond", {
                dateFormat: "m/d/Y",
                defaultDate: defaultSecondStartDate, // 01/08/2025 (7 days after 01/01/2025)
            });
        });



        // Price Add/Remove
        $(document).ready(function() {
            var priceCounters = {};

            $(document).on('click', '.add_more_price_list', function(e) {
                e.preventDefault();
                var priceWrapper = $(this).closest('.price_list_custom_row').find('.price_list_wrapper');
                var wrapperId = priceWrapper.attr('data-wrapper-id');
                if (!wrapperId) {
                    wrapperId = 'wrapper_' + Date.now() + '_' + Math.random();
                    priceWrapper.attr('data-wrapper-id', wrapperId);
                    priceCounters[wrapperId] = priceWrapper.find('.col-md-4, .price_list_append_row')
                        .length;
                }
                var customCount = priceCounters[wrapperId];
                var customLabel = 'Custom ' + String.fromCharCode(65 + customCount - 3);
                var nameIndex = customCount;

                var newRow = `
                    <div class="price_list_append_row col-md-4">
                        <div class="price_list editable_field">
                            <i class="fa-solid fa-pen-to-square edit_icon"></i>
                            <div class="input_text_filed_price_list">
                                <input type="text" class="form-control" value="${customLabel}" name="prices[${nameIndex}][side]">
                            </div>
                            <div class="txt_field price_list_icon">
                                <i class="price_list_icon_doller fa-solid fa-dollar-sign"></i>
                                <input type="number" class="form-control" name="prices[${nameIndex}][number]" value="0">
                                <button type="button" class="btn_red btn_global delete_price_list"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    </div>`;
                priceWrapper.append(newRow);
                priceCounters[wrapperId]++;
            });

            $(document).on('click', '.delete_price_list', function() {
                var priceRow = $(this).closest('.price_list_append_row');
                if (priceRow.length) {
                    priceRow.remove();
                } else {
                    // Remove default price row
                    $(this).closest('.col-md-4').remove();
                }
            });
        });

        // Best Time Add/Remove
        $(document).ready(function() {
            var timeCounters = {};

            $(document).on('click', '.add_more_time button', function(e) {
                e.preventDefault();
                var container = $(this).closest('.append_service_time');
                var appendedItems = container.find('.appended_items');
                var containerId = container.attr('data-container-id');
                if (!containerId) {
                    containerId = 'time_' + Date.now();
                    container.attr('data-container-id', containerId);
                    timeCounters[containerId] = 1;
                }
                var idx = timeCounters[containerId];
                var newRow = `
                    <div class="row appended_time_row mt-2">
                        <div class="col-md-6">
                            <div class="form-floating txt_field custom_dates">
                                <input type="time" class="form-control" name="best_time[${idx}][start_hour]" placeholder="">
                                <label>Starting Hour</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="button_field_align">
                                <div class="form-floating txt_field custom_dates">
                                    <input type="time" class="form-control" name="best_time[${idx}][end_hour]" placeholder="">
                                    <label>Ending Hour</label>
                                </div>
                                <button type="button" class="btn btn-danger remove_time_row"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>
                    </div>`;
                appendedItems.append(newRow);
                timeCounters[containerId]++;
            });

            $(document).on('click', '.remove_time_row', function() {
                $(this).closest('.appended_time_row').remove();
            });
        });

        // Image Preview
        $(document).on('change', '.custom_file_input', function() {
            var input = this;
            var imgWrapper = $(this).closest('label').find('.image-input-wrapper img');
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    imgWrapper.attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        });

        // Second Start Date toggle for 2 Weeks and 24 Weeks
        $(document).on('change', '.note-type-select', function() {
            var selectedValue = $(this).val();
            var secondDateField = $(this).closest('.general_info_container').find('.second_start_date');
            if (selectedValue === 'biMonthly' || selectedValue === 'biAnnually') {
                secondDateField.show();
            } else {
                secondDateField.hide();
            }
        });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.js" referrerpolicy="no-referrer"></script>
    <script>
        Dropzone.autoDiscover = false;

        // Initialize Dropzone for admin section
        if (document.getElementById('admin_dropzone_image')) {
            const adminDropzone = new Dropzone("#admin_dropzone_image", {
                url: "/dummy-upload",
                autoProcessQueue: false,
                paramName: "file",
                maxFilesize: 2,
                acceptedFiles: ".jpg,.jpeg,.png,.gif",
                dictDefaultMessage: '<i class="fa-solid fa-image"></i><h6>Upload Images</h6><p>Drag & drop or click to upload</p>',
                addRemoveLinks: true,
                dictRemoveFile: "Remove",
                clickable: true,
                init: function() {
                    this.on("addedfile", function(file) {
                        convertToBase64(file, 'admin-hidden-input-container');
                    });

                    this.on("removedfile", function(file) {
                        removeHiddenInput(file, 'admin-hidden-input-container');
                    });

                    this.on("error", function(file, message) {
                        if (message === "You can't upload files of this type.") {
                            alert(
                                "Invalid file type! Please upload a .jpg, .jpeg, .png, or .gif file."
                            );
                            this.removeFile(file);
                        }
                    });
                }
            });
        }

        // Initialize Dropzone for staff section
        if (document.getElementById('staff_dropzone_image')) {
            const staffDropzone = new Dropzone("#staff_dropzone_image", {
                url: "/dummy-upload",
                autoProcessQueue: false,
                paramName: "file",
                maxFilesize: 2,
                acceptedFiles: ".jpg,.jpeg,.png,.gif",
                dictDefaultMessage: '<i class="fa-solid fa-image"></i><h6>Upload Images</h6><p>Drag & drop or click to upload</p>',
                addRemoveLinks: true,
                dictRemoveFile: "Remove",
                clickable: true,
                init: function() {
                    this.on("addedfile", function(file) {
                        convertToBase64(file, 'staff-hidden-input-container');
                    });

                    this.on("removedfile", function(file) {
                        removeHiddenInput(file, 'staff-hidden-input-container');
                    });

                    this.on("error", function(file, message) {
                        if (message === "You can't upload files of this type.") {
                            alert(
                                "Invalid file type! Please upload a .jpg, .jpeg, .png, or .gif file."
                            );
                            this.removeFile(file);
                        }
                    });
                }
            });
        }

        // Convert new file to Base64
        function convertToBase64(file, containerId) {
            const reader = new FileReader();
            reader.onloadend = function() {
                file.base64 = reader.result;
                addHiddenInput(file.base64, containerId);
            };
            reader.readAsDataURL(file);
        }

        function addHiddenInput(value, containerId) {
            const container = document.getElementById(containerId);
            if (!container) return;
            const input = document.createElement("input");
            input.type = "hidden";
            input.name = "image[]";
            input.value = value;
            container.appendChild(input);
        }

        function removeHiddenInput(file, containerId) {
            const container = document.getElementById(containerId);
            if (!container) return;
            const inputs = container.querySelectorAll('input[name="image[]"]');

            inputs.forEach(input => {
                if (file.base64 && input.value === file.base64) {
                    input.remove();
                }
            });
        }

        // Business Card Front/Back Image Preview
        $(document).ready(function() {
            $('.custom_file_input').on('change', function() {
                var input = $(this);
                var img = input.closest('.image-input').find('.input_image_field');
                var file = this.files[0];
                if (file) {
                    img.attr('src', URL.createObjectURL(file));
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
                //     // hasError already declared above for internal duplicate check

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
                //             type: "create",
                //             client_id: ""
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
                //                 type: "create",
                //                 client_id: ""
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
                //                 type: "create",
                //                 client_id: ""
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
                //             type: "create",
                //             client_id: ""
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
                    // hasError already declared above for internal duplicate check

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
                            type: "create",
                            client_id: ""
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
                                type: "create",
                                client_id: ""
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
                                type: "create",
                                client_id: ""
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

        $(document).ready(function() {
            // Set placeholder for phone inputs
            $('input[name^="phone"]').each(function() {
                $(this).attr('placeholder', '111-111-1111');
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

        $(document).ready(function() {
            // Remove "Contact Information" heading from cloned row
            $(document).on('click', '.btn_add_contact_info', function() {
                const container = $(this).closest('.contact_info_container');
                const original = container.find('.contact_info').first();
                const newContactInfoGroup = original.clone();

                const inputFields = newContactInfoGroup.find('input[type="text"], input[type="email"]');
                inputFields.val(''); // Clear all input fields

                // Remove "Contact Information" heading from cloned row - remove the entire div containing h4
                newContactInfoGroup.find('.row > .col-md-12').each(function() {
                    if ($(this).find('h4:contains("Contact Information")').length > 0) {
                        $(this).remove();
                    }
                });

                // Update checkbox value to match the new index
                const contactInfoCount = container.find('.contact_info').length;
                const checkbox = newContactInfoGroup.find('input[type="checkbox"]');
                if (checkbox.length > 0) {
                    checkbox.val(contactInfoCount);
                    checkbox.prop('checked', false); // Uncheck by default for new rows
                }

                // Ensure the 'add' button disappears and the 'remove' button shows
                newContactInfoGroup.find('.btn_remove_contact_info').show();
                newContactInfoGroup.find('.btn_add_contact_info').hide();

                container.append(newContactInfoGroup); // Append the new contact group
            });

            $(document).on('click', '.btn_remove_contact_info', function() {
                $(this).closest('.contact_info').remove(); // Remove the current contact info group
            });
        });
    </script>
@endpush
