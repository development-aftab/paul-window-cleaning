@extends('theme.layout.master')
@push('css')
    <!-- Include Dropzone CSS -->
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
        <a href="{{ url('clients') }}" class="back_btn_navbar">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h2 class="navbar_PageTitle">Create Client</h2>
    </div>
@endsection
@section('content')
    @if (auth()->user()->hasRole('admin'))
        <section class="create_clients_sec custom_clients_section">
            <div class="container-fluid custom_container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="create_clients_wrapper shadow_box_wrapper">
                            <form method="post" action="{{ route('clients.store') }}" class="form-horizontal" id="clientValidate" enctype="multipart/form-data">
                                @csrf

                                <div class="col-md-12">
                                    <h4 class="main_heading mb-0">Parent Company</h4>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 general_info_container">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h4>General Information</h4>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="name[0]" id="client_name" placeholder="" required>
                                                    <label for="client_name">Client Name *</label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                <div class=" form-floating txt_field mb-3">
                                                    <select name="client_type[0]" id="" class="form-select" required>
                                                        <option value="" selected disabled>Client Type </option>
                                                        <option value="residential">Residential</option>
                                                        <option value="commercial">Commercial</option>
                                                    </select>
                                                    <label for="">Client Type *</label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                <div class="txt_field form-floating mb-3">
                                                    <select name="payment_type[0]" id="" class="form-select" required>
                                                        <option value="" selected disabled>Payment Type </option>
                                                        <option value="cash">Cash</option>
                                                        <option value="invoice">Invoice</option>
                                                    </select>
                                                    <label for="">Payment Type *</label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                <div class="form-floating txt_field">
                                                    <input type="number" class="form-control commission_percentage" name="commission_percentage[0]" id="commission_percentage" placeholder="" required value="50">
                                                    <label for="client_name">Commission Percentage *</label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                <div class=" d-flex align-items-start mb-3">
                                                    <div class="form-floating txt_field flex-grow-1 me-2">
                                                        <input type="date" class="form-control startDate" name="start_date[0]" placeholder="" required>
                                                        <label for="">Start Date *</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4 select_frequency">
                                                <div class="txt_field form-floating">
                                                    <select class="form-select note-type-select" name="service_frequency[0]" id="">
                                                        {{--                                                        <option value="" disabled selected>Frequency </option> --}}
                                                        <option value="normalWeek">Weekly</option>
                                                        <option value="biMonthly">biMonthly</option>
                                                        <option value="monthly">Monthly</option>
                                                        {{--                                                        <option value="eightWeek">8 Weeks</option> --}}
                                                        {{--                                                        <option value="quarterly">12 Weeks</option> --}}
                                                        {{--                                                        <option value="biAnnually">24 Weeks</option> --}}
                                                        <option value="annually">52 Weeks</option>
                                                    </select>
                                                    <label for="">Frequency *</label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4 second_start_date" style="display: none">
                                                <div class=" d-flex align-items-start mb-3">
                                                    <div class="form-floating txt_field flex-grow-1 me-2">
                                                        <input type="date" class="form-control startDateSecond" name="start_date_second[0]" placeholder="" required>
                                                        <label for="">Second Starting Date *</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h4>Address </h4>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="house_no[0]" id="" placeholder="">
                                                    <label for="">Number </label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4 mb-5">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="street[0]" id="" placeholder="">
                                                    <label for="">Street </label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="city[0]" id="" placeholder="">
                                                    <label for="">City </label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="state[0]" id="" placeholder="">
                                                    <label for="">State </label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                <div class="form-floating txt_field">
                                                    <input type="number" class="form-control" name="postal[0]" id="" placeholder="">
                                                    <label for="">Zip Code </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 contact_info_container" id="contact_info_container">
                                        <div class="contact_info">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <h4>Contact Information</h4>
                                                </div>
                                                <div id="contact_name_container" class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                    <div class="phone-group d-flex align-items-start mb-3">
                                                        <div class="form-floating txt_field flex-grow-1 me-2">
                                                            <input type="text" class="form-control" name="contact_name[0][]" id="contact_name" placeholder="">
                                                            <label for="contact_name">Contact Name </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="phone-container" class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                    <div class="phone-group d-flex align-items-start mb-3">
                                                        <div class="form-floating txt_field flex-grow-1 me-2">
                                                            <input type="text" class="form-control" name="phone[0][]" id="phone_number" placeholder="">
                                                            <label for="phone_number">Phone Number </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="position-container" class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                    <div class="phone-group d-flex align-items-start mb-3">
                                                        <div class="form-floating txt_field flex-grow-1 me-2">
                                                            <input type="text" class="form-control" name="positions[0][]" id="positions" placeholder="">
                                                            <label for="positions">Position In Company </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="email-container" class="col-xxl-3 col-xl-6 col-lg-6 col-md-6">
                                                    <div class="email-group d-flex align-items-start mb-3">
                                                        <div class="form-floating txt_field flex-grow-1 me-2">
                                                            <input type="email" class="form-control" name="email[0][]" id="email-0-0" placeholder="">
                                                            <label for="email-0-0">Email</label>
                                                        </div>
                                                        <div class="form-check d-flex align-items-center ms-2" style="margin-top: 15px;">
                                                            <input class="form-check-input" type="checkbox" name="invoice_email_parent[0][]" value="0" checked>
                                                            <label class="form-check-label ms-1" title="Send invoice to this email">
                                                                <i class="fas fa-envelope"></i>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6">
                                                    <div class="d-flex mb-3">
                                                        <div class="form-floating txt_field flex-grow-1 me-2">
                                                            <input type="text" class="form-control" name="note[0][]" id="" placeholder="">
                                                            <label for="">Note </label>
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
                                                    <textarea rows="4" class="form-control" name="additional_note[0]" id="additional_note_0" placeholder="Additional Notes" style="height: 100px"></textarea>
                                                    <label for="additional_note_0">Notes (misc info about this client or job)</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 price_list_custom_row">
                                        <h4>Price</h4>
                                        <div class="row price_list_wrapper" data-first-index="0">
                                            <!-- Predefined Entries -->
                                            <div class="col-xxl-4 col-xl-6 col-lg-6 col-md-6">
                                                <div class="price_list editable_field">
                                                    <i class="fa-solid fa-pen-to-square edit_icon"></i>
                                                    <div class="input_text_filed_price_list">
                                                        <input type="text" class="form-control" value="Interior" name="prices[0][0][side]" required>
                                                    </div>
                                                    <div class="txt_field price_list_icon">
                                                        <i class="price_list_icon_doller fa-solid fa-dollar-sign"></i>
                                                        <input type="number" class="form-control" value="0" name="prices[0][0][number]" required>
                                                        <button type="button" class="btn_red btn_global delete_price_list">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xxl-4 col-xl-6 col-lg-6 col-md-6">
                                                <div class="price_list editable_field">
                                                    <i class="fa-solid fa-pen-to-square edit_icon"></i>
                                                    <div class="input_text_filed_price_list">
                                                        <input type="text" class="form-control" value="Exterior" name="prices[0][1][side]" required>
                                                    </div>
                                                    <div class="txt_field price_list_icon">
                                                        <i class="price_list_icon_doller fa-solid fa-dollar-sign"></i>
                                                        <input type="number" class="form-control" value="0" name="prices[0][1][number]" required>
                                                        <button type="button" class="btn_red btn_global delete_price_list">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xxl-4 col-xl-6 col-lg-6 col-md-6">
                                                <div class="price_list editable_field">
                                                    <i class="fa-solid fa-pen-to-square edit_icon"></i>
                                                    <div class="input_text_filed_price_list">
                                                        <input type="text" class="form-control" value="Interior & Exterior" name="prices[0][2][side]">
                                                    </div>
                                                    <div class="txt_field price_list_icon">
                                                        <i class="price_list_icon_doller fa-solid fa-dollar-sign"></i>
                                                        <input type="number" class="form-control" value="0" name="prices[0][2][number]">
                                                        <button type="button" class="btn_red btn_global delete_price_list">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn_global btn_blue add_more_price_list">Add
                                            Custom<i class="fa-solid fa-plus"></i></button>
                                    </div>
                                    <div class="col-md-6 select_two_field">
                                        <h4>Closed</h4>
                                        <div class="txt_field form-floating">
                                            <div class="custom_multi_select">
                                                <select multiple class="multiselect form-select note-type-select" name="unavail_day[0][]">
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
                                    <div class="col-md-12">
                                        <div class="cycle_frequency_wrapper">
                                            <div class="row create_client_cus_row">

                                                <div class="col-md-6 row append_service_time">
                                                    <div class="col-md-6 mt-2">
                                                        <div class="form-floating txt_field custom_dates">
                                                            <input type="time" class="form-control" name="best_time[0][0][start_hour]" id="startHour" placeholder="">
                                                            <label for="startDate">Starting Hour</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mt-2">
                                                        <div class="button_field_align">
                                                            <div class="form-floating txt_field custom_dates">
                                                                <input type="time" class="form-control" name="best_time[0][0][end_hour]" id="endHour" placeholder="">
                                                                <label for="endDate">Ending Hour</label>
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
                                                        <select class="form-select" name="route_id[]" aria-label="Default select">
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
                                    <div class="col-md-12 create_client_cus_row">
                                        <div class="client_upload_img">
                                            <div class="dropzone dz-clickable client_dropzone_image" id="">
                                                <div class="dz-default dz-message">
                                                    <button class="dz-button" type="button">
                                                        <i class="fa-solid fa-image"></i>
                                                        <h6>Upload Images</h6>
                                                        <p>Drag & drop or click to upload</p>
                                                    </button>
                                                </div>
                                            </div>
                                            <input type="hidden" name="image[0][]" id="image[]">
                                        </div>
                                    </div>
                                    <div class="col-md-6 create_client_cus_row">
                                        <div class="client_upload_img custom_img_margin">
                                            <div class="image-input" data-kt-image-input="true">
                                                <!--begin::Image preview wrapper-->

                                                <!--end::Image preview wrapper-->

                                                <!--begin::Edit Button-->
                                                <label class="btn btn-active-color-primary shadow edit_icon custom_append_img" data-kt-image-input-action="change" data-bs-toggle="tooltip" data-bs-dismiss="click">

                                                    {{-- <i class="ki-duotone ki-pencil fs-6"><span class="path1"></span><span class="path2"></span></i> --}}

                                                    {{-- Add Image --}}
                                                    <div class="image-input-wrapper">
                                                        <img class="input_image_field" src="">
                                                    </div>

                                                    {{-- Image Content --}}
                                                    <div class="custom_upload_content">
                                                        <span><i class="fa-solid fa-image"></i></span>
                                                        <h4>Business Card Front</h4>
                                                        <p>Image of the front of your business card</p>
                                                    </div>

                                                    <!--begin::Inputs-->
                                                    <input type="file" name="front_image[0]" accept=".png, .jpg, .jpeg" class="myinput custom_file_input" />
                                                    <input type="hidden" name="avatar_remove" />
                                                    <!--end::Inputs-->

                                                </label>
                                                <!--end::Edit button-->

                                                <!--begin::Cancel button-->
                                                <span class="btn btn-icon btn-circle btn-active-color-primary shadow edit_icon" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" data-bs-dismiss="click" title="Cancel avatar">
                                                    <i class="ki-outline ki-cross fs-3"></i>
                                                </span>
                                                <!--end::Cancel button-->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 create_client_cus_row">
                                        <div class="client_upload_img custom_img_margin">
                                            <div class="image-input" data-kt-image-input="true">
                                                <!--begin::Image preview wrapper-->
                                                <label class="btn btn-active-color-primary shadow edit_icon custom_append_img" data-kt-image-input-action="change" data-bs-toggle="tooltip" data-bs-dismiss="click">
                                                    {{-- <i class="ki-duotone ki-pencil fs-6"><span class="path1"></span><span class="path2"></span></i> --}}

                                                    {{-- Add Image --}}
                                                    <div class="image-input-wrapper">
                                                        <img class="input_image_field" src="" data-original-src="{{ asset('website') }}/assets/images/create_client_img_plus_sign.png">
                                                    </div>

                                                    {{-- Image Content --}}

                                                    <div class="custom_upload_content">
                                                        <span><i class="fa-solid fa-image"></i></span>
                                                        <h4>Business Card Back</h4>
                                                        <p>Image of the back of your business card</p>
                                                    </div>

                                                    <!--begin::Inputs-->
                                                    <input type="file" name="back_image[0]" accept=".png, .jpg, .jpeg" class="myinput custom_file_input" />
                                                    <input type="hidden" name="avatar_remove" />
                                                    <!--end::Inputs-->
                                                </label>
                                                <!--end::Edit button-->

                                                <!--begin::Cancel button-->
                                                <span class="btn btn-icon btn-circle btn-active-color-primary shadow edit_icon" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" data-bs-dismiss="click" title="Cancel avatar">
                                                    <i class="ki-outline ki-cross fs-3"></i>
                                                </span>
                                                <!--end::Cancel button-->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 create_client_cus_row">
                                        <div class="branch_checkbox">
                                            <label class="custom-checkbox-label">
                                                <input type="checkbox" name="branch" id="branchCheckbox" />
                                                <span class="custom-checkbox"></span>
                                                Do you have branch?
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-12 address-container">
                                        <div class="address-group mb-4">
                                            <div class="row create_client_cus_row">
                                                <div class="col-md-12 d-flex align-items-center mb-2">
                                                    <h4 class="branch_title" style="margin:0px 0px 0px">Branch #01</h4>
                                                    <button type="button" id="add-address-btn" class="btn btn-primary btn-add-address " style="margin-left: 15px">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-remove-address" style="display: none; margin-left: 15px">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                </div>
                                                <div class="col-md-12 general_info_container">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <h4>General Information</h4>
                                                        </div>
                                                        <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                            <div class="form-floating txt_field">
                                                                <input type="text" class="form-control" name="name[1]" id="client_name" placeholder="" required>
                                                                <label for="client_name">Client Name *</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                            <div class=" form-floating txt_field mb-3">
                                                                <select name="client_type[1]" id="" class="form-select" required>
                                                                    <option value="" selected disabled>Client Type
                                                                    </option>
                                                                    <option value="residential">Residential</option>
                                                                    <option value="commercial">Commercial</option>
                                                                </select>
                                                                <label for="">Client Type *</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                            <div class="txt_field form-floating mb-3">
                                                                <select name="payment_type[1]" id="" class="form-select" required>
                                                                    <option value="" selected disabled>Payment Type
                                                                    </option>
                                                                    <option value="cash">Cash</option>
                                                                    <option value="invoice">Invoice</option>
                                                                </select>
                                                                <label for="">Payment Type *</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                            <div class="form-floating txt_field">
                                                                <input type="number" class="form-control commission_percentage" name="commission_percentage[1]" id="commission_percentage" placeholder="" required value="50">
                                                                <label for="client_name">Commission Percentage *</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                            <div class=" d-flex align-items-start mb-3">
                                                                <div class="form-floating txt_field flex-grow-1 me-2">
                                                                    <input type="date" class="form-control startDate" name="start_date[1]" placeholder="" required>
                                                                    <label for="">Start Date *</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4 select_frequency">
                                                            <div class="txt_field form-floating">
                                                                <select class="form-select note-type-select" name="service_frequency[1]" id="" required>
                                                                    <option value="normalWeek">Weekly</option>
                                                                    <option value="biMonthly">biMonthly</option>
                                                                    <option value="monthly">Monthly</option>
                                                                    {{-- <option value="eightWeek">8 Weeks</option>
                                                                    <option value="quarterly">12 Weeks</option>
                                                                    <option value="biAnnually">24 Weeks</option> --}}
                                                                    <option value="annually">52 Weeks</option>
                                                                </select>
                                                                <label for="">Frequency</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4 second_start_date" style="display: none">
                                                            <div class=" d-flex align-items-start mb-3">
                                                                <div class="form-floating txt_field flex-grow-1 me-2">
                                                                    <input type="date" class="form-control startDateSecond" name="start_date_second[1]" placeholder="" required>
                                                                    <label for="">Second Starting Date *</label>
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
                                                                <input type="text" class="form-control" name="house_no[1]" id="" placeholder="">
                                                                <label for="">Number</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4 mb-5">
                                                            <div class="form-floating txt_field">
                                                                <input type="text" class="form-control" name="street[1]" id="" placeholder="">
                                                                <label for="">Street </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                            <div class="form-floating txt_field">
                                                                <input type="text" class="form-control" name="city[1]" id="" placeholder="">
                                                                <label for="">City</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                            <div class="form-floating txt_field">
                                                                <input type="text" class="form-control" name="state[1]" id="" placeholder="">
                                                                <label for="">State</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                            <div class="form-floating txt_field">
                                                                <input type="number" class="form-control" name="postal[1]" id="" placeholder="">
                                                                <label for="">Zip Code</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 contact_info_container" id="contact_info_container">
                                                    <div class="contact_info">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <h4>Contact Information</h4>
                                                            </div>
                                                            <div id="contact_name_container" class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                                <div class="phone-group d-flex align-items-start mb-3">
                                                                    <div class="form-floating txt_field flex-grow-1 me-2">
                                                                        <input type="text" class="form-control" name="contact_name[1][]" id="contact_name" placeholder="">
                                                                        <label for="contact_name">Contact Name</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div id="phone-container" class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                                <div class="phone-group d-flex align-items-start mb-3">
                                                                    <div class="form-floating txt_field flex-grow-1 me-2">
                                                                        <input type="text" class="form-control" name="phone[1][]" id="phone_number" placeholder="">
                                                                        <label for="phone_number">Phone Number</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div id="position-container" class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                                <div class="phone-group d-flex align-items-start mb-3">
                                                                    <div class="form-floating txt_field flex-grow-1 me-2">
                                                                        <input type="text" class="form-control" name="positions[1][]" id="positions" placeholder="">
                                                                        <label for="positions">Position In Company</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div id="email-container" class="col-xxl-3 col-xl-6 col-lg-6 col-md-6">
                                                                <div class="email-group d-flex align-items-start mb-3">
                                                                    <div class="form-floating txt_field flex-grow-1 me-2">
                                                                        <input type="email" class="form-control" name="email[1][]" id="email-1-0" placeholder="">
                                                                        <label for="email-1-0">Email</label>
                                                                    </div>
                                                                    <div class="form-check d-flex align-items-center ms-2" style="margin-top: 15px;">
                                                                        <input class="form-check-input" type="checkbox" name="invoice_email_parent[1][]" value="0" checked>
                                                                        <label class="form-check-label ms-1" title="Send invoice to this email">
                                                                            <i class="fas fa-envelope"></i>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6">
                                                                <div class="d-flex mb-3">
                                                                    <div class="form-floating txt_field flex-grow-1 me-2">
                                                                        <input type="text" class="form-control" name="note[1][]" id="" placeholder="">
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
                                                                <textarea rows="4" class="form-control" name="additional_note[1]" id="additional_note_1" placeholder="Additional Notes" style="height: 100px"></textarea>
                                                                <label for="additional_note_1">Notes (misc info about this client or job)</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 price_list_custom_row">
                                                    <h4>Price</h4>
                                                    <div class="row price_list_wrapper" data-first-index="1">
                                                        <!-- Predefined Entries -->
                                                        <div class="col-xxl-4 col-xl-6 col-lg-6 col-md-6">
                                                            <div class="price_list editable_field">
                                                                <i class="fa-solid fa-pen-to-square edit_icon"></i>
                                                                <div class="input_text_filed_price_list">
                                                                    <input type="text" class="form-control" value="Interior" name="prices[1][0][side]">
                                                                </div>
                                                                <div class="txt_field price_list_icon">
                                                                    <i class="price_list_icon_doller fa-solid fa-dollar-sign"></i>
                                                                    <input type="number" class="form-control" value="0" name="prices[1][0][number]">
                                                                    <button type="button" class="btn_red btn_global delete_price_list">
                                                                        <i class="fa-solid fa-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xxl-4 col-xl-6 col-lg-6 col-md-6">
                                                            <div class="price_list editable_field">
                                                                <i class="fa-solid fa-pen-to-square edit_icon"></i>
                                                                <div class="input_text_filed_price_list">
                                                                    <input type="text" class="form-control" value="Exterior" name="prices[1][1][side]">
                                                                </div>
                                                                <div class="txt_field price_list_icon">
                                                                    <i class="price_list_icon_doller fa-solid fa-dollar-sign"></i>
                                                                    <input type="number" class="form-control" value="0" name="prices[1][1][number]">
                                                                    <button type="button" class="btn_red btn_global delete_price_list">
                                                                        <i class="fa-solid fa-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xxl-4 col-xl-6 col-lg-6 col-md-6">
                                                            <div class="price_list editable_field">
                                                                <i class="fa-solid fa-pen-to-square edit_icon"></i>
                                                                <div class="input_text_filed_price_list">
                                                                    <input type="text" class="form-control" value="Interior & Exterior" name="prices[1][2][side]">
                                                                </div>
                                                                <div class="txt_field price_list_icon">
                                                                    <i class="price_list_icon_doller fa-solid fa-dollar-sign"></i>
                                                                    <input type="number" class="form-control" value="0" name="prices[1][2][number]">
                                                                    <button type="button" class="btn_red btn_global delete_price_list">
                                                                        <i class="fa-solid fa-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                    <button type="button" class="btn_global btn_blue add_more_price_list">Add Custom<i class="fa-solid fa-plus"></i></button>
                                                </div>
                                                <div class="col-md-6 select_two_field">
                                                    <h4>Closed</h4>
                                                    <div class="txt_field form-floating">
                                                        <div class="custom_multi_select">
                                                            <select multiple class="multiselect form-select note-type-select" name="unavail_day[1][]">
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

                                                <div class="col-md-12">
                                                    <div class="cycle_frequency_wrapper">
                                                        <div class="row create_client_cus_row">
                                                            <div class="col-md-6 row append_service_time">
                                                                <div class="col-md-6 mt-2">
                                                                    <div class="form-floating txt_field custom_dates">
                                                                        <input type="time" class="form-control" name="best_time[1][0][start_hour]" id="startHour" placeholder="">
                                                                        <label for="startDate">Starting Hour</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6 mt-2">
                                                                    <div class="button_field_align">
                                                                        <div class="form-floating txt_field custom_dates">
                                                                            <input type="time" class="form-control" name="best_time[1][0][end_hour]" id="endHour" placeholder="">
                                                                            <label for="endDate">Ending Hour</label>
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
                                                                    <select class="form-select" name="route_id[]" aria-label="Default select">
                                                                        <option disabled selected>Select Route</option>
                                                                        @foreach ($route as $item)
                                                                            <option value="{{ $item->id }}">
                                                                                {{ $item->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <label for="">Assign Route</label>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>

                                                <div class="col-md-12 create_client_cus_row">
                                                    <div class="client_upload_img">
                                                        <div class="dropzone dz-clickable client_dropzone_image" id="">
                                                            <div class="dz-default dz-message">
                                                                <button class="dz-button" type="button">
                                                                    <i class="fa-solid fa-image"></i>
                                                                    <h6>Upload Images</h6>
                                                                    <p>Drag & drop or click to upload</p>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <input type="hidden" name="image[1][]" id="image[]">
                                                    </div>
                                                </div>
                                                <div class="col-md-6 create_client_cus_row">
                                                    <div class="client_upload_img custom_img_margin">
                                                        <div class="image-input" data-kt-image-input="true">
                                                            <!--begin::Image preview wrapper-->

                                                            <!--end::Image preview wrapper-->

                                                            <!--begin::Edit Button-->
                                                            <label class="btn btn-active-color-primary shadow edit_icon custom_append_img" data-kt-image-input-action="change" data-bs-toggle="tooltip" data-bs-dismiss="click">

                                                                {{-- <i class="ki-duotone ki-pencil fs-6"><span class="path1"></span><span class="path2"></span></i> --}}

                                                                {{-- Add Image --}}
                                                                <div class="image-input-wrapper">
                                                                    <img class="input_image_field" src="">
                                                                </div>

                                                                {{-- Image Content --}}
                                                                <div class="custom_upload_content">
                                                                    <span><i class="fa-solid fa-image"></i></span>
                                                                    <h4>Business Card Front</h4>
                                                                    <p>Image of the front of your business card</p>
                                                                </div>

                                                                <!--begin::Inputs-->
                                                                <input type="file" name="front_image[1]" accept=".png, .jpg, .jpeg" class="myinput custom_file_input" />
                                                                <input type="hidden" name="avatar_remove" />
                                                                <!--end::Inputs-->

                                                            </label>
                                                            <!--end::Edit button-->

                                                            <!--begin::Cancel button-->
                                                            <span class="btn btn-icon btn-circle btn-active-color-primary shadow edit_icon" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" data-bs-dismiss="click" title="Cancel avatar">
                                                                <i class="ki-outline ki-cross fs-3"></i>
                                                            </span>
                                                            <!--end::Cancel button-->
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 create_client_cus_row">
                                                    <div class="client_upload_img custom_img_margin">
                                                        <div class="image-input" data-kt-image-input="true">
                                                            <!--begin::Image preview wrapper-->
                                                            <label class="btn btn-active-color-primary shadow edit_icon custom_append_img" data-kt-image-input-action="change" data-bs-toggle="tooltip" data-bs-dismiss="click">
                                                                {{-- <i class="ki-duotone ki-pencil fs-6"><span class="path1"></span><span class="path2"></span></i> --}}

                                                                {{-- Add Image --}}
                                                                <div class="image-input-wrapper">
                                                                    <img class="input_image_field" src="" data-original-src="{{ asset('website') }}/assets/images/create_client_img_plus_sign.png">
                                                                </div>

                                                                {{-- Image Content --}}

                                                                <div class="custom_upload_content">
                                                                    <span><i class="fa-solid fa-image"></i></span>
                                                                    <h4>Business Card Back</h4>
                                                                    <p>Image of the back of your business card</p>
                                                                </div>

                                                                <!--begin::Inputs-->
                                                                <input type="file" name="back_image[1]" accept=".png, .jpg, .jpeg" class="myinput custom_file_input" />
                                                                <input type="hidden" name="avatar_remove" />
                                                                <!--end::Inputs-->
                                                            </label>
                                                            <!--end::Edit button-->

                                                            <!--begin::Cancel button-->
                                                            <span class="btn btn-icon btn-circle btn-active-color-primary shadow edit_icon" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" data-bs-dismiss="click" title="Cancel avatar">
                                                                <i class="ki-outline ki-cross fs-3"></i>
                                                            </span>
                                                            <!--end::Cancel button-->
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 create_client_cus_row">
                                        <div class="d-flex gap-3">
                                            <input type="hidden" name="action" id="form_action" value="create">
                                            <button type="submit" data-action="create" id="client-save-btn" class="btn_global btn_blue submitButton">
                                                Save Client<i class="fa-solid fa-floppy-disk ms-2"></i>
                                            </button>
                                            <button type="submit" data-action="create_and_schedule" id="client-save-schedule" class="btn_global btn_green submitButton">
                                                Save & Schedule<i class="fa-solid fa-calendar-plus ms-2"></i>
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
    @elseif(auth()->user()->hasRole('staff'))
        <section class="create_clients_sec custom_clients_section">
            <div class="container-fluid custom_container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="create_clients_wrapper shadow_box_wrapper">
                            <form method="post" action="{{ route('clients.store') }}" class="form-horizontal" id="clientValidate" enctype="multipart/form-data">
                                @csrf

                                <div class="col-md-12">
                                    <h4 class="main_heading mb-0">Parent Company</h4>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 general_info_container">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h4>General Information</h4>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="name[0]" id="client_name" placeholder="" required>
                                                    <label for="client_name">Client Name *</label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                <div class=" form-floating txt_field mb-3">
                                                    <select name="client_type[0]" id="" class="form-select">
                                                        <option value="" selected disabled>Client Type</option>
                                                        <option value="residential">Residential</option>
                                                        <option value="commercial">Commercial</option>
                                                    </select>
                                                    <label for="">Client Type</label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                <div class="txt_field form-floating mb-3">
                                                    <select name="payment_type[0]" id="" class="form-select">
                                                        <option value="" selected disabled>Payment Type</option>
                                                        <option value="cash">Cash</option>
                                                        <option value="invoice">Invoice</option>
                                                    </select>
                                                    <label for="">Payment Type</label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                <div class="form-floating txt_field">
                                                    <input type="number" class="form-control" name="commission_percentage[0]" id="commission_percentage" placeholder="" required value="50">
                                                    <label for="client_name">Commission Percentage</label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                <div class=" d-flex align-items-start mb-3">
                                                    <div class="form-floating txt_field flex-grow-1 me-2">
                                                        <input type="date" class="form-control startDate" name="start_date[0]" id="startDate" placeholder="">
                                                        <label for="">Start Date *</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4 select_frequency">
                                                <div class="txt_field form-floating">
                                                    <select class="form-select note-type-select" name="service_frequency[0]" id="">
                                                        {{--                                                        <option value="" disabled selected>Frequency *</option> --}}
                                                        <option value="normalWeek">Weekly</option>
                                                        <option value="biMonthly">biMonthly</option>
                                                        <option value="monthly">Monthly</option>
                                                        {{-- <option value="eightWeek">8 Weeks</option>
                                                        <option value="quarterly">12 Weeks</option>
                                                        <option value="biAnnually">24 Weeks</option> --}}
                                                        <option value="annually">52 Weeks</option>
                                                    </select>
                                                    <label for="">Frequency</label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4 second_start_date" style="display: none">
                                                <div class=" d-flex align-items-start mb-3">
                                                    <div class="form-floating txt_field flex-grow-1 me-2">
                                                        <input type="date" class="form-control startDateSecond" name="start_date_second[0]" placeholder="">
                                                        <label for="">Second Starting Date *</label>
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
                                                    <input type="text" class="form-control" name="house_no[0]" id="" placeholder="">
                                                    <label for="">Number</label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4 mb-5">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="street[0]" id="" placeholder="">
                                                    <label for="">Street </label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="city[0]" id="" placeholder="">
                                                    <label for="">City</label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                <div class="form-floating txt_field">
                                                    <input type="text" class="form-control" name="state[0]" id="" placeholder="">
                                                    <label for="">State</label>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                <div class="form-floating txt_field">
                                                    <input type="number" class="form-control" name="postal[0]" id="" placeholder="">
                                                    <label for="">Zip Code</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 contact_info_container" id="contact_info_container">
                                        <div class="contact_info">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <h4>Contact Information</h4>
                                                </div>
                                                <div id="contact_name_container" class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                    <div class="phone-group d-flex align-items-start mb-3">
                                                        <div class="form-floating txt_field flex-grow-1 me-2">
                                                            <input type="text" class="form-control" name="contact_name[0][]" id="contact_name" placeholder="">
                                                            <label for="contact_name">Contact Name</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="phone-container" class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                    <div class="phone-group d-flex align-items-start mb-3">
                                                        <div class="form-floating txt_field flex-grow-1 me-2">
                                                            <input type="text" class="form-control" name="phone[0][]" id="phone_number" placeholder="">
                                                            <label for="phone_number">Phone Number</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="position-container" class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                    <div class="phone-group d-flex align-items-start mb-3">
                                                        <div class="form-floating txt_field flex-grow-1 me-2">
                                                            <input type="text" class="form-control" name="positions[0][]" id="positions" placeholder="">
                                                            <label for="positions">Position In Company</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="email-container" class="col-xxl-3 col-xl-6 col-lg-6 col-md-6">
                                                    <div class="email-group d-flex align-items-start mb-3">
                                                        <div class="form-floating txt_field flex-grow-1 me-2">
                                                            <input type="email" class="form-control" name="email[0][]" id="email-0-staff-0" placeholder="">
                                                            <label for="email-0-staff-0">Email</label>
                                                        </div>
                                                        <div class="form-check d-flex align-items-center ms-2" style="margin-top: 15px;">
                                                            <input class="form-check-input" type="checkbox" name="invoice_email_parent[0][]" value="0" checked>
                                                            <label class="form-check-label ms-1" title="Send invoice to this email">
                                                                <i class="fas fa-envelope"></i>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6">
                                                    <div class="d-flex mb-3">
                                                        <div class="form-floating txt_field flex-grow-1 me-2">
                                                            <input type="text" class="form-control" name="note[0][]" id="" placeholder="">
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
                                                    <textarea rows="4" class="form-control" name="additional_note[0]" id="additional_note_0" placeholder="Additional Notes" style="height: 100px"></textarea>
                                                    <label for="additional_note_0">Notes (misc info about this client or job)</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 price_list_custom_row">
                                        <h4>Price</h4>
                                        <div class="row price_list_wrapper" data-first-index="0">
                                            <!-- Predefined Entries -->
                                            <div class="col-xxl-4 col-xl-6 col-lg-6 col-md-6">
                                                <div class="price_list editable_field">
                                                    <i class="fa-solid fa-pen-to-square edit_icon"></i>
                                                    <div class="input_text_filed_price_list">
                                                        <input type="text" class="form-control" value="Interior" name="prices[0][0][side]">
                                                    </div>
                                                    <div class="txt_field price_list_icon">
                                                        <i class="price_list_icon_doller fa-solid fa-dollar-sign"></i>
                                                        <input type="number" class="form-control" value="0" name="prices[0][0][number]">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xxl-4 col-xl-6 col-lg-6 col-md-6">
                                                <div class="price_list editable_field">
                                                    <i class="fa-solid fa-pen-to-square edit_icon"></i>
                                                    <div class="input_text_filed_price_list">
                                                        <input type="text" class="form-control" value="Exterior" name="prices[0][1][side]">
                                                    </div>
                                                    <div class="txt_field price_list_icon">
                                                        <i class="price_list_icon_doller fa-solid fa-dollar-sign"></i>
                                                        <input type="number" class="form-control" value="0" name="prices[0][1][number]">
                                                        <button type="button" class="btn_red btn_global delete_price_list">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xxl-4 col-xl-6 col-lg-6 col-md-6">
                                                <div class="price_list editable_field">
                                                    <i class="fa-solid fa-pen-to-square edit_icon"></i>
                                                    <div class="input_text_filed_price_list">
                                                        <input type="text" class="form-control" value="Interior & Exterior" name="prices[0][2][side]">
                                                    </div>
                                                    <div class="txt_field price_list_icon">
                                                        <i class="price_list_icon_doller fa-solid fa-dollar-sign"></i>
                                                        <input type="number" class="form-control" value="0" name="prices[0][2][number]">
                                                        <button type="button" class="btn_red btn_global delete_price_list">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                        <button type="button" class="btn_global btn_blue add_more_price_list">Add
                                            Custom<i class="fa-solid fa-plus"></i></button>
                                    </div>
                                    <div class="col-md-6 select_two_field">
                                        <h4>Closed</h4>
                                        <div class="txt_field form-floating">
                                            <div class="custom_multi_select">
                                                <select multiple class="multiselect form-select note-type-select" name="unavail_day[0][]">
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

                                    <div class="col-md-12">
                                        <div class="cycle_frequency_wrapper">
                                            <div class="row create_client_cus_row">

                                                <div class="col-md-6 row append_service_time">
                                                    <div class="col-md-6 mt-2">
                                                        <div class="form-floating txt_field custom_dates">
                                                            <input type="time" class="form-control" name="best_time[0][0][start_hour]" id="startHour" placeholder="">
                                                            <label for="startDate">Starting Hour</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mt-2">
                                                        <div class="button_field_align">
                                                            <div class="form-floating txt_field custom_dates">
                                                                <input type="time" class="form-control" name="best_time[0][0][end_hour]" id="endHour" placeholder="">
                                                                <label for="endDate">Ending Hour</label>
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
                                                        <select class="form-select" name="route_id[]" aria-label="Default select">
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

                                    <div class="col-md-12 create_client_cus_row">
                                        <div class="client_upload_img">
                                            <div class="dropzone dz-clickable client_dropzone_image" id="">
                                                <div class="dz-default dz-message">
                                                    <button class="dz-button" type="button">
                                                        <i class="fa-solid fa-image"></i>
                                                        <h6>Upload Images</h6>
                                                        <p>Drag & drop or click to upload</p>
                                                    </button>
                                                </div>
                                            </div>
                                            <input type="hidden" name="image[0][]" id="image[]">
                                        </div>
                                    </div>
                                    <div class="col-md-6 create_client_cus_row">
                                        <div class="client_upload_img custom_img_margin">
                                            <div class="image-input" data-kt-image-input="true">
                                                <!--begin::Image preview wrapper-->

                                                <!--end::Image preview wrapper-->

                                                <!--begin::Edit Button-->
                                                <label class="btn btn-active-color-primary shadow edit_icon custom_append_img" data-kt-image-input-action="change" data-bs-toggle="tooltip" data-bs-dismiss="click">

                                                    {{-- <i class="ki-duotone ki-pencil fs-6"><span class="path1"></span><span class="path2"></span></i> --}}

                                                    {{-- Add Image --}}
                                                    <div class="image-input-wrapper">
                                                        <img class="input_image_field" src="">
                                                    </div>

                                                    {{-- Image Content --}}
                                                    <div class="custom_upload_content">
                                                        <span><i class="fa-solid fa-image"></i></span>
                                                        <h4>Business Card Front</h4>
                                                        <p>Image of the front of your business card</p>
                                                    </div>

                                                    <!--begin::Inputs-->
                                                    <input type="file" name="front_image[0]" accept=".png, .jpg, .jpeg" class="myinput custom_file_input" />
                                                    <input type="hidden" name="avatar_remove" />
                                                    <!--end::Inputs-->

                                                </label>
                                                <!--end::Edit button-->

                                                <!--begin::Cancel button-->
                                                <span class="btn btn-icon btn-circle btn-active-color-primary shadow edit_icon" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" data-bs-dismiss="click" title="Cancel avatar">
                                                    <i class="ki-outline ki-cross fs-3"></i>
                                                </span>
                                                <!--end::Cancel button-->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 create_client_cus_row">
                                        <div class="client_upload_img custom_img_margin">
                                            <div class="image-input" data-kt-image-input="true">
                                                <!--begin::Image preview wrapper-->
                                                <label class="btn btn-active-color-primary shadow edit_icon custom_append_img" data-kt-image-input-action="change" data-bs-toggle="tooltip" data-bs-dismiss="click">
                                                    {{-- <i class="ki-duotone ki-pencil fs-6"><span class="path1"></span><span class="path2"></span></i> --}}

                                                    {{-- Add Image --}}
                                                    <div class="image-input-wrapper">
                                                        <img class="input_image_field" src="" data-original-src="{{ asset('website') }}/assets/images/create_client_img_plus_sign.png">
                                                    </div>

                                                    {{-- Image Content --}}

                                                    <div class="custom_upload_content">
                                                        <span><i class="fa-solid fa-image"></i></span>
                                                        <h4>Business Card Back</h4>
                                                        <p>Image of the back of your business card</p>
                                                    </div>

                                                    <!--begin::Inputs-->
                                                    <input type="file" name="back_image[0]" accept=".png, .jpg, .jpeg" class="myinput custom_file_input" />
                                                    <input type="hidden" name="avatar_remove" />
                                                    <!--end::Inputs-->
                                                </label>
                                                <!--end::Edit button-->

                                                <!--begin::Cancel button-->
                                                <span class="btn btn-icon btn-circle btn-active-color-primary shadow edit_icon" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" data-bs-dismiss="click" title="Cancel avatar">
                                                    <i class="ki-outline ki-cross fs-3"></i>
                                                </span>
                                                <!--end::Cancel button-->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 create_client_cus_row">
                                        <div class="branch_checkbox">
                                            <label class="custom-checkbox-label">
                                                <input type="checkbox" name="branch" id="branchCheckbox" />
                                                <span class="custom-checkbox"></span>
                                                Do you have branch?
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-12 address-container">
                                        <div class="address-group mb-4">
                                            <div class="row create_client_cus_row">
                                                <div class="col-md-12 d-flex align-items-center mb-2">
                                                    <h4 class="branch_title" style="margin:0px 0px 0px">Branch #01</h4>
                                                    <button type="button" id="add-address-btn" class="btn btn-primary btn-add-address " style="margin-left: 15px">
                                                        <i class="fas fa-plus"></i>
                                                    </button>

                                                    <button type="button" class="btn btn-danger btn-remove-address" style="display: none; margin-left: 15px">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                </div>
                                                <div class="col-md-12 general_info_container">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <h4>General Information</h4>
                                                        </div>
                                                        <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                            <div class="form-floating txt_field">
                                                                <input type="text" class="form-control" name="name[1]" id="client_name" placeholder="" required>
                                                                <label for="client_name">Client Name *</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                            <div class=" form-floating txt_field mb-3">
                                                                <select name="client_type[1]" id="" class="form-select">
                                                                    <option value="" selected disabled>Client Type
                                                                    </option>
                                                                    <option value="residential">Residential</option>
                                                                    <option value="commercial">Commercial</option>
                                                                </select>
                                                                <label for="">Client Type</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                            <div class="txt_field form-floating mb-3">
                                                                <select name="payment_type[1]" id="" class="form-select">
                                                                    <option value="" selected disabled>Payment Type
                                                                    </option>
                                                                    <option value="cash">Cash</option>
                                                                    <option value="invoice">Invoice</option>
                                                                </select>
                                                                <label for="">Payment Type</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                            <div class="form-floating txt_field">
                                                                <input type="number" class="form-control" name="commission_percentage[1]" id="commission_percentage" placeholder="" required value="50">
                                                                <label for="client_name">Commission Percentage</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                            <div class=" d-flex align-items-start mb-3">
                                                                <div class="form-floating txt_field flex-grow-1 me-2">
                                                                    <input type="date" class="form-control startDate" name="start_date[1]" id="startDate" placeholder="">
                                                                    <label for="">Start Date</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4 select_frequency">
                                                            <div class="txt_field form-floating">
                                                                <select class="form-select note-type-select" name="service_frequency[1]" id="">
                                                                    {{--                                                                    <option value="" disabled selected>Frequency --}}
                                                                    {{--                                                                    </option> --}}
                                                                    <option value="normalWeek">Weekly</option>
                                                                    <option value="biMonthly">biMonthly</option>
                                                                    <option value="monthly">Monthly</option>
                                                                    {{-- <option value="eightWeek">8 Weeks</option>
                                                                    <option value="quarterly">12 Weeks</option>
                                                                    <option value="biAnnually">24 Weeks</option> --}}
                                                                    <option value="annually">52 Weeks</option>
                                                                </select>
                                                                <label for="">Frequency</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4 second_start_date" style="display: none">
                                                            <div class=" d-flex align-items-start mb-3">
                                                                <div class="form-floating txt_field flex-grow-1 me-2">
                                                                    <input type="date" class="form-control startDateSecond" name="start_date_second[1]" placeholder="">
                                                                    <label for="">Second Starting Date *</label>
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
                                                                <input type="text" class="form-control" name="house_no[1]" id="" placeholder="">
                                                                <label for="">Number</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4 mb-5">
                                                            <div class="form-floating txt_field">
                                                                <input type="text" class="form-control" name="street[1]" id="" placeholder="">
                                                                <label for="">Street </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                            <div class="form-floating txt_field">
                                                                <input type="text" class="form-control" name="city[1]" id="" placeholder="">
                                                                <label for="">City</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                            <div class="form-floating txt_field">
                                                                <input type="text" class="form-control" name="state[1]" id="" placeholder="">
                                                                <label for="">State</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                            <div class="form-floating txt_field">
                                                                <input type="number" class="form-control" name="postal[1]" id="" placeholder="">
                                                                <label for="">Zip Code</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 contact_info_container" id="contact_info_container">
                                                    <div class="contact_info">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <h4>Contact Information</h4>
                                                            </div>
                                                            <div id="contact_name_container" class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                                <div class="phone-group d-flex align-items-start mb-3">
                                                                    <div class="form-floating txt_field flex-grow-1 me-2">
                                                                        <input type="text" class="form-control" name="contact_name[1][]" id="contact_name" placeholder="">
                                                                        <label for="contact_name">Contact Name</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div id="phone-container" class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                                <div class="phone-group d-flex align-items-start mb-3">
                                                                    <div class="form-floating txt_field flex-grow-1 me-2">
                                                                        <input type="text" class="form-control" name="phone[1][]" id="phone_number" placeholder="">
                                                                        <label for="phone_number">Phone Number</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div id="position-container" class="col-xxl-2 col-xl-4 col-lg-4 col-md-4">
                                                                <div class="phone-group d-flex align-items-start mb-3">
                                                                    <div class="form-floating txt_field flex-grow-1 me-2">
                                                                        <input type="text" class="form-control" name="positions[1][]" id="positions" placeholder="">
                                                                        <label for="positions">Position In Company</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div id="email-container" class="col-xxl-3 col-xl-6 col-lg-6 col-md-6">
                                                                <div class="email-group d-flex align-items-start mb-3">
                                                                    <div class="form-floating txt_field flex-grow-1 me-2">
                                                                        <input type="email" class="form-control email-field" name="email[1][]" id="email-1-staff-0" placeholder="">
                                                                        <label for="email-1-staff-0">Email</label>
                                                                    </div>
                                                                    <div class="form-check d-flex align-items-center ms-2" style="margin-top: 15px;">
                                                                        <input class="form-check-input" type="checkbox" name="invoice_email_parent[1][]" value="0" checked>
                                                                        <label class="form-check-label ms-1" title="Send invoice to this email">
                                                                            <i class="fas fa-envelope"></i>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6">
                                                                <div class="d-flex mb-3">
                                                                    <div class="form-floating txt_field flex-grow-1 me-2">
                                                                        <input type="text" class="form-control" name="note[1][]" id="" placeholder="">
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
                                                                <textarea rows="4" class="form-control" name="additional_note[1]" id="additional_note_1" placeholder="Additional Notes" style="height: 100px"></textarea>
                                                                <label for="additional_note_1">Notes (misc info about this client or job)</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 price_list_custom_row">
                                                    <h4>Price</h4>
                                                    <div class="row price_list_wrapper" data-first-index="1">
                                                        <!-- Predefined Entries -->
                                                        <div class="col-xxl-4 col-xl-6 col-lg-6 col-md-6">
                                                            <div class="price_list editable_field">
                                                                <i class="fa-solid fa-pen-to-square edit_icon"></i>
                                                                <div class="input_text_filed_price_list">
                                                                    <input type="text" class="form-control" value="Interior" name="prices[1][0][side]">
                                                                </div>
                                                                <div class="txt_field price_list_icon">
                                                                    <i class="price_list_icon_doller fa-solid fa-dollar-sign"></i>
                                                                    <input type="number" class="form-control" value="0" name="prices[1][0][number]">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xxl-4 col-xl-6 col-lg-6 col-md-6">
                                                            <div class="price_list editable_field">
                                                                <i class="fa-solid fa-pen-to-square edit_icon"></i>
                                                                <div class="input_text_filed_price_list">
                                                                    <input type="text" class="form-control" value="Exterior" name="prices[1][1][side]">
                                                                </div>
                                                                <div class="txt_field price_list_icon">
                                                                    <i class="price_list_icon_doller fa-solid fa-dollar-sign"></i>
                                                                    <input type="number" class="form-control" value="0" name="prices[1][1][number]">
                                                                    <button type="button" class="btn_red btn_global delete_price_list">
                                                                        <i class="fa-solid fa-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xxl-4 col-xl-6 col-lg-6 col-md-6">
                                                            <div class="price_list editable_field">
                                                                <i class="fa-solid fa-pen-to-square edit_icon"></i>
                                                                <div class="input_text_filed_price_list">
                                                                    <input type="text" class="form-control" value="Interior & Exterior" name="prices[1][2][side]">
                                                                </div>
                                                                <div class="txt_field price_list_icon">
                                                                    <i class="price_list_icon_doller fa-solid fa-dollar-sign"></i>
                                                                    <input type="number" class="form-control" value="0" name="prices[1][2][number]">
                                                                    <button type="button" class="btn_red btn_global delete_price_list">
                                                                        <i class="fa-solid fa-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                    <button type="button" class="btn_global btn_blue add_more_price_list">Add Custom<i class="fa-solid fa-plus"></i></button>
                                                </div>
                                                <div class="col-md-6 select_two_field">
                                                    <h4>Closed</h4>
                                                    <div class="txt_field form-floating">
                                                        <div class="custom_multi_select">
                                                            <select multiple class="multiselect form-select note-type-select" name="unavail_day[1][]">
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

                                                <div class="col-md-12">
                                                    <div class="cycle_frequency_wrapper">
                                                        <div class="row create_client_cus_row">
                                                            <div class="col-md-6 row append_service_time">
                                                                <div class="col-md-6 mt-2">
                                                                    <div class="form-floating txt_field custom_dates">
                                                                        <input type="time" class="form-control" name="best_time[1][0][start_hour]" id="startHour" placeholder="">
                                                                        <label for="startDate">Starting Hour</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6 mt-2">
                                                                    <div class="button_field_align">
                                                                        <div class="form-floating txt_field custom_dates">
                                                                            <input type="time" class="form-control" name="best_time[1][0][end_hour]" id="endHour" placeholder="">
                                                                            <label for="endDate">Ending Hour</label>
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
                                                                    <select class="form-select" name="route_id[]" aria-label="Default select">
                                                                        <option disabled selected>Select Route</option>
                                                                        @foreach ($route as $item)
                                                                            <option value="{{ $item->id }}">
                                                                                {{ $item->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <label for="">Assign Route</label>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>

                                                <div class="col-md-12 create_client_cus_row">
                                                    <div class="client_upload_img">
                                                        <div class="dropzone dz-clickable client_dropzone_image" id="">
                                                            <div class="dz-default dz-message">
                                                                <button class="dz-button" type="button">
                                                                    <i class="fa-solid fa-image"></i>
                                                                    <h6>Upload Images</h6>
                                                                    <p>Drag & drop or click to upload</p>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <input type="hidden" name="image[1][]" id="image[]">
                                                    </div>
                                                </div>
                                                <div class="col-md-6 create_client_cus_row">
                                                    <div class="client_upload_img custom_img_margin">
                                                        <div class="image-input" data-kt-image-input="true">
                                                            <!--begin::Image preview wrapper-->

                                                            <!--end::Image preview wrapper-->

                                                            <!--begin::Edit Button-->
                                                            <label class="btn btn-active-color-primary shadow edit_icon custom_append_img" data-kt-image-input-action="change" data-bs-toggle="tooltip" data-bs-dismiss="click">

                                                                {{-- <i class="ki-duotone ki-pencil fs-6"><span class="path1"></span><span class="path2"></span></i> --}}

                                                                {{-- Add Image --}}
                                                                <div class="image-input-wrapper">
                                                                    <img class="input_image_field" src="">
                                                                </div>

                                                                {{-- Image Content --}}
                                                                <div class="custom_upload_content">
                                                                    <span><i class="fa-solid fa-image"></i></span>
                                                                    <h4>Business Card Front</h4>
                                                                    <p>Image of the front of your business card</p>
                                                                </div>

                                                                <!--begin::Inputs-->
                                                                <input type="file" name="front_image[1]" accept=".png, .jpg, .jpeg" class="myinput custom_file_input" />
                                                                <input type="hidden" name="avatar_remove" />
                                                                <!--end::Inputs-->

                                                            </label>
                                                            <!--end::Edit button-->

                                                            <!--begin::Cancel button-->
                                                            <span class="btn btn-icon btn-circle btn-active-color-primary shadow edit_icon" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" data-bs-dismiss="click" title="Cancel avatar">
                                                                <i class="ki-outline ki-cross fs-3"></i>
                                                            </span>
                                                            <!--end::Cancel button-->
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 create_client_cus_row">
                                                    <div class="client_upload_img custom_img_margin">
                                                        <div class="image-input" data-kt-image-input="true">
                                                            <!--begin::Image preview wrapper-->
                                                            <label class="btn btn-active-color-primary shadow edit_icon custom_append_img" data-kt-image-input-action="change" data-bs-toggle="tooltip" data-bs-dismiss="click">
                                                                {{-- <i class="ki-duotone ki-pencil fs-6"><span class="path1"></span><span class="path2"></span></i> --}}

                                                                {{-- Add Image --}}
                                                                <div class="image-input-wrapper">
                                                                    <img class="input_image_field" src="" data-original-src="{{ asset('website') }}/assets/images/create_client_img_plus_sign.png">
                                                                </div>

                                                                {{-- Image Content --}}

                                                                <div class="custom_upload_content">
                                                                    <span><i class="fa-solid fa-image"></i></span>
                                                                    <h4>Business Card Back</h4>
                                                                    <p>Image of the back of your business card</p>
                                                                </div>

                                                                <!--begin::Inputs-->
                                                                <input type="file" name="back_image[1]" accept=".png, .jpg, .jpeg" class="myinput custom_file_input" />
                                                                <input type="hidden" name="avatar_remove" />
                                                                <!--end::Inputs-->
                                                            </label>
                                                            <!--end::Edit button-->

                                                            <!--begin::Cancel button-->
                                                            <span class="btn btn-icon btn-circle btn-active-color-primary shadow edit_icon" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" data-bs-dismiss="click" title="Cancel avatar">
                                                                <i class="ki-outline ki-cross fs-3"></i>
                                                            </span>
                                                            <!--end::Cancel button-->
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 create_client_cus_row">
                                        <div class="d-flex gap-3">
                                            <input type="hidden" name="action" id="form_action_staff" value="create">
                                            <button type="submit" data-action="create" class="btn_global btn_blue submitButton">
                                                Save Client<i class="fa-solid fa-floppy-disk ms-2"></i>
                                            </button>
                                            <button type="submit" data-action="create_and_schedule" class="btn_global btn_green submitButton">
                                                Save & Schedule<i class="fa-solid fa-calendar-plus ms-2"></i>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>

    <script>
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
    </script>

    <script>
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

            $(document).on('change', '.select_frequency .note-type-select', function() {
                var selectedValue = $(this).val();

                // Find the corresponding second date field for this specific select
                var secondDateDiv = $(this).closest('.select_frequency').next('.second_start_date');

                // Show only if biMonthly or biAnnually is selected
                if (selectedValue === 'biMonthly' || selectedValue === 'biAnnually') {
                    secondDateDiv.show();
                } else {
                    secondDateDiv.hide();
                    // Optional: Clear the date value when hiding
                    secondDateDiv.find('.startDateSecond').val('');
                }
            });

            // Initial check on page load for all existing selects
            $('.select_frequency .note-type-select').each(function() {
                var selectedValue = $(this).val();
                var secondDateDiv = $(this).closest('.select_frequency').next('.second_start_date');

                if (selectedValue === 'biMonthly' || selectedValue === 'biAnnually') {
                    secondDateDiv.show();
                } else {
                    secondDateDiv.hide();
                }
            });

        });



        let selectedRoutes = [];

        $(document).on('click', '.btn-add-address', function() {
            const container = $(this).closest('.address-container');
            const original = container.find('.address-group').first();

            const newAddressGroup = original.clone(true, true);

            const addressGroups = container.find('.address-group');
            const nextBranchNumber = addressGroups.length + 1;

            newAddressGroup.find('.branch_title').text('Branch #' + String(nextBranchNumber).padStart(2, '0'));
            newAddressGroup.find('input').not(
                '.price_list_wrapper input, .startDate, .startDateSecond, .commission_percentage').val('');
            newAddressGroup.find('select').val('');

            var clonedPriceWrapper = newAddressGroup.find('.price_list_wrapper');
            // Do NOT remove default price items or appended rows when cloning
            // Only remove .col-md-2 if needed for legacy cleanup, but keep .col-xxl-4 etc.
            clonedPriceWrapper.find('.col-md-2').remove();
            // Do NOT remove .price_list_append_row here
            clonedPriceWrapper.removeAttr('data-wrapper-id');

            // Add delete icon to all default price items in the clone
            clonedPriceWrapper.find('.col-xxl-4, .col-xl-6, .col-lg-6, .col-md-6').each(function() {
                var priceIcon = $(this).find('.price_list_icon');
                if (priceIcon.find('.delete_price_list').length === 0) {
                    priceIcon.append('<button type="button" class="btn_red btn_global delete_price_list"><i class="fa-solid fa-trash"></i></button>');
                }
            });

            var clonedFrequencyWrapper = newAddressGroup.find('.cycle_frequency_wrapper');
            console.log("clonedFrequencyWrapper", clonedFrequencyWrapper);
            clonedFrequencyWrapper.find('.add_more_time').show();
            clonedFrequencyWrapper.find('.appended_items').empty();

            var currentFirstIndex = parseInt(clonedPriceWrapper.attr('data-first-index')) || 0;
            var newFirstIndex = currentFirstIndex + 1;

            clonedPriceWrapper.attr('data-first-index', newFirstIndex);

            /// IMPORTANT: Select element ko completely recreate karo
            newAddressGroup.find('select.multiselect').each(function() {
                const $oldSelect = $(this);
                const selectName = $oldSelect.attr('name');
                const selectClasses = $oldSelect.attr('class');

                // Naya fresh select element banao
                const $newSelect = $('<select></select>')
                    .attr('name', selectName)
                    .attr('class', selectClasses)
                    .attr('multiple', 'multiple');

                // Options copy karo
                $oldSelect.find('option').each(function() {
                    const $option = $(this).clone();
                    $option.removeAttr('data-select2-id'); // Remove Select2 IDs
                    $newSelect.append($option);
                });

                // Parent element mein replace karo
                const $parent = $oldSelect.parent();

                // Purana select aur uska Select2 container remove karo
                $parent.find('.select2-container').remove();
                $oldSelect.remove();

                // Naya select add karo
                $parent.append($newSelect);
            });

            // ==========================================
            // DROPZONE CLEANUP - FIXED VERSION
            // ==========================================
            newAddressGroup.find('.client_dropzone_image').each(function() {
                const $oldDropzone = $(this);

                const dropzoneClasses = 'dropzone dz-clickable client_dropzone_image';
                const dropzoneId = $oldDropzone.attr('id');

                const $newDropzone = $('<div></div>')
                    .attr('class', dropzoneClasses);

                if (dropzoneId) {
                    $newDropzone.attr('id', dropzoneId);
                }

                $newDropzone.html(`
                    <div class="dz-default dz-message">
                        <button class="dz-button" type="button">
                            <i class="fa-solid fa-image"></i>
                            <h6>Upload Images</h6>
                            <p>Drag & drop or click to upload</p>
                        </button>
                    </div>
                `);

                const $parent = $oldDropzone.parent();

                $oldDropzone.remove();

                $parent.append($newDropzone);
            });

            // Hidden image inputs bhi remove karo
            newAddressGroup.find('.client_upload_img input[type="hidden"][name^="image"]').remove();


            newAddressGroup.find('.price_list_wrapper').each(function() {
                // Only update if this is NOT the first group (parent)
                if ($(this).attr('data-first-index') != '0') {
                    $(this).find('input, select').each(function() {
                        const name = $(this).attr('name');
                        if (name && name.match(/^prices\[0\]/)) {
                            const updatedName = name.replace(/prices\[0\]/, `prices[${nextBranchNumber}]`);
                            $(this).attr('name', updatedName);
                        }
                    });
                }
            });

            newAddressGroup.find('.btn-remove-address').show();
            newAddressGroup.find('.btn-add-address').hide();

            container.append(newAddressGroup);
            // Initialize Dropzone - fresh element pe
            newAddressGroup.find('.client_dropzone_image').each(function(index) {
                initializeDropzone(this, dropzoneInstances.length + index);
            });
            // Initialize Select2 only on the newly added section
            newAddressGroup.find('.multiselect').select2({
                placeholder: "Closed Day",
                allowClear: true
            });
        });

        $(document).on('click', '.btn-remove-address', function() {
            $(this).closest('.address-group').remove();
        });
    </script>



    <script>
        $(document).ready(function() {

            $('.cycle_frequency_wrapper .custom_dates .form-floating').hide();

            function updateDateInputs() {
                var selectedValue = $('input[name="service_frequency"]:checked').val();

                if (["normalWeek", "quarterly", "eightWeek", "monthly", "biMonthly", "biAnnually", "annually"]
                    .includes(selectedValue)) {
                    $(".cycle_frequency_wrapper .custom_dates input[name='start_date']").attr("disabled", false)
                        .closest('.form-floating').show();

                    if (selectedValue === "biMonthly" || selectedValue === "biAnnually") {
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


            $(document).on("click", ".cycle_frequency_wrapper .add_more_time button", function() {
                var priceWrapper = $(this).closest('.address-group').find('.price_list_custom_row').find(
                    '.price_list_wrapper');
                var firstIndex = priceWrapper.data('first-index') || 0;
                var appendIndex = $(this).closest('.append_service_time').find(
                    '.appended_items .appended_row_time').length + 1;

                if (appendIndex < 3) {
                    $(this).closest(".cycle_frequency_wrapper").find(".append_service_time .appended_items")
                        .append(
                            `<div class="row appended_row_time custom_row" data-index="${appendIndex}">
                            <div class="col-md-6 mt-3">
                                <div class="form-floating txt_field custom_dates">
                                    <input type="time" class="form-control" name="best_time[${firstIndex}][${appendIndex}][start_hour]" placeholder="">
                                    <label>Starting Hour</label>
                                </div>
                            </div>
                            <div class="col-md-6 mt-3">
                               <div class="button_field_align">
                                 <div class="form-floating txt_field custom_dates">
                                    <input type="time" class="form-control" name="best_time[${firstIndex}][${appendIndex}][end_hour]" placeholder="">
                                    <label>Ending Hour</label>
                                </div>
                                 <div class="remove_append_time">
                                    <button type="button" class="btn_global btn_red"><i class="fa-solid fa-trash"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>`
                        );
                    if (appendIndex == 2) {
                        $(this).closest(".add_more_time").hide();
                    }
                }
            });

            $(document).on("click", ".cycle_frequency_wrapper .remove_append_time button", function() {
                $(this).closest(".appended_row_time").remove();

                $(".cycle_frequency_wrapper .add_more_time").show(); // Ensure the button is shown
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
    {{--    //for dropzone --}}
    <script>
        Dropzone.autoDiscover = false;
        let dropzoneInstances = [];

        // Function to initialize a single dropzone
        function initializeDropzone(element, index) {
            // If already initialized, skip it
            if (element.dropzone) {
                return element.dropzone;
            }

            const dropzone = new Dropzone(element, {
                url: "#",
                paramName: "file",
                maxFilesize: 2,
                acceptedFiles: ".jpg,.jpeg,.png,.gif",
                dictDefaultMessage: '<i class="fa-solid fa-image"></i><h6>Upload Images</h6><p>Drag & drop or click to upload</p>',
                addRemoveLinks: true,
                dictRemoveFile: "Remove",
                init: function() {
                    this.on("addedfile", function(file) {
                        convertToBase64(file, this);
                    });
                    this.on("removedfile", function(file) {
                        updateHiddenField(this);
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

            // Store instance
            dropzoneInstances.push(dropzone);
            return dropzone;
        }

        // Initialize existing dropzones on page load
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.client_dropzone_image').forEach((element, index) => {
                if (!element.dropzone) { // Check if not already initialized
                    initializeDropzone(element, index);
                }
            });
        });

        // Function to convert file to base64
        function convertToBase64(file, dropzoneInstance) {
            const reader = new FileReader();
            reader.onloadend = function() {
                const base64String = reader.result;
                file.base64 = base64String;
                updateHiddenField(dropzoneInstance);
            };
            reader.readAsDataURL(file);
        }

        // Function to update hidden field for specific dropzone
        function updateHiddenField(dropzoneInstance) {
            const dropzoneElement = dropzoneInstance.element;
            const parentDiv = dropzoneElement.closest('.client_upload_img');

            // Remove existing hidden inputs for this specific dropzone
            const existingInputs = parentDiv.querySelectorAll('input[type="hidden"][name^="image"]');
            existingInputs.forEach(input => input.remove());

            // Add new hidden inputs
            dropzoneInstance.files.forEach(function(file) {
                if (file.base64) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'image[]';
                    input.value = file.base64;
                    parentDiv.appendChild(input);
                }
            });
        }
    </script>

    <script>
        $(document).ready(function() {
            $("#clientValidate").validate({
                rules: {
                    "name[0]": {
                        required: true
                    },
                    "name[1]": {
                        required: true
                    },
                    "start_date[0]": {
                        required: true
                    },
                    "start_date[1]": {
                        required: true
                    },
                    start_date_second: {
                        required: true
                    },
                    client_type: {
                        required: true
                    },
                    "service_frequency[1]": {
                        required: true
                    },
                    "commission_percentage[0]": {
                        required: true
                    },
                    "commission_percentage[1]": {
                        required: true
                    },
                    "payment_type[0]": {
                        required: true
                    },
                    "payment_type[1]": {
                        required: true
                    },
                    "prices[0][1][number]": {
                        required: true
                    },
                    "prices[0][0][number]": {
                        required: true
                    },
                    "prices[1][0][number]": {
                        required: true
                    },
                    "prices[1][0][number]": {
                        required: true
                    },
                    "prices[0][0][side]": {
                        required: true
                    },
                    "prices[0][1][side]": {
                        required: true
                    },
                    "prices[0][2][number]": {
                        required: true
                    },
                    "prices[1][1][side]": {
                        required: true
                    },
                    "prices[1][2][number]": {
                        required: true
                    },
                    "prices[0][2][side]": {
                        required: true
                    },
                    "client_type[0]": {
                        required: true
                    },
                    "client_type[1]": {
                        required: true
                    }
                },
                messages: {
                    "name[0]": {
                        required: "Please Enter Client Name",
                    },
                    "name[1]": {
                        required: "Please Enter Client Name"
                    },
                    "start_date[0]": {
                        required: "Please Enter Start date"
                    },
                    "start_date[1]": {
                        required: "Please Enter Start date"
                    },
                    "prices[0][0][side]": {
                        required: "Please enter price"
                    },
                    start_date_second: {
                        required: "Please Enter second date"
                    },
                    client_type: {
                        required: "Please enter client type"
                    },
                    "service_frequency[1]": {
                        required: "Please enter service frequency"
                    },
                    "commission_percentage[0]": {
                        required: "Please enter commission percentage"
                    },
                    "commission_percentage[1]": {
                        required: "Please enter commission percentage"
                    },
                    "payment_type[0]": {
                        required: "please enter payment type"
                    },
                    "payment_type[1]": {
                        required: "please enter payment type"
                    },
                    "prices[0][1][number]": {
                        required: "Please enter price"
                    },
                    "prices[0][0][number]": {
                        required: "Please enter price"
                    },
                    "prices[0][1][number]": {
                        required: "Please enter price"
                    },
                    "prices[1][1][number]": {
                        required: "Please enter price"
                    },
                    "prices[1][2][side]": {
                        required: "Please enter price"
                    },
                    "prices[0][2][number]": {
                        required: "Please enter price"
                    },
                    "prices[0][2][side]": {
                        required: "Please enter price"
                    },
                    "client_type[0]": {
                        required: "Please enter client type"
                    },
                    "client_type[1]": {
                        required: "Please enter client type"
                    }
                },
                errorElement: "span",
                errorClass: "text-danger",

                invalidHandler: function(event, validator) {
                    if (validator.numberOfInvalids()) {
                        $('html, body').animate({
                            scrollTop: $(validator.errorList[0].element).offset().top - 100
                        }, 800);
                        // Re-enable submit buttons if validation fails
                        $('.submitButton').prop('disabled', false);
                    }
                },
                submitHandler: function(form) {
                    // Disable all submit buttons after validation passes
                    $('.submitButton').prop('disabled', true);
                    form.submit();
                },

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

            // Handle button clicks to set action value (for both admin and staff forms)
            $('.submitButton').on('click', function(e) {
                const action = $(this).data('action');

                // Set value for both hidden inputs (admin and staff)
                $('#form_action').val(action);
                $('#form_action_staff').val(action);

                console.log('Button clicked, action set to:', action);
            });

            $('input[name="address[]"], select[name="route_id[]"]').on('change', function() {
                $(this).removeClass("is-invalid");
                $(this).siblings('span.text-danger').remove();
            });
        });
    </script>
    <script>
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
    </script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        $(document).ready(function() {
            // Set default date to 01/01/2025
            let defaultStartDate = new Date(2025, 0, 6); // January 1, 2025

            // Calculate second start date (7 days after first start date)
            let defaultSecondStartDate = new Date(2025, 0, 1);
            defaultSecondStartDate.setDate(defaultSecondStartDate.getDate() + 7); // Add 7 days = 01/08/2025

            let startDatePicker = flatpickr(".startDate", {
                dateFormat: "m/d/Y",
                defaultDate: defaultStartDate,
                // minDate: "today",
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
                // minDate: "today"
            });
        });
    </script>
    <script>
        $(document).on('click', '.btn_add_contact_info', function() {
            const container = $(this).closest('.contact_info_container');
            const original = container.find('.contact_info').first();
            const newContactInfoGroup = original.clone();

            const inputFields = newContactInfoGroup.find('input[type="text"], input[type="email"]');
            inputFields.val(''); // Clear all input fields

            // Remove "Contact Information" heading from cloned row
            newContactInfoGroup.find('h4:contains("Contact Information")').remove();

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
    <script>
        $(document).ready(function() {
            var priceCounters = {};

            $(document).on('click', '.add_more_price_list', function(e) {
                e.preventDefault();

                var priceWrapper = $(this).closest('.price_list_custom_row').find('.price_list_wrapper');
                var wrapperId = priceWrapper.attr('data-wrapper-id');

                if (!wrapperId) {
                    wrapperId = 'wrapper_' + Date.now() + '_' + Math.random();
                    priceWrapper.attr('data-wrapper-id', wrapperId);
                    priceCounters[wrapperId] = 0; // Custom counter — A,B,C ke liye
                }

                var customCount = priceCounters[wrapperId];
                var customLabel = 'Custom ' + String.fromCharCode(65 + customCount);
                var firstIndex = priceWrapper.data('first-index') || 0;

                // ✅ Total existing cols count karo — nameIndex overlap nahi hoga
                var nameIndex = priceWrapper.find('[class*="col-"]').length;

                var newRow = `
            <div class="price_list_append_row col-xxl-4 col-xl-6 col-lg-6 col-md-6">
                <div class="price_list editable_field">
                    <i class="fa-solid fa-pen-to-square edit_icon"></i>
                    <div class="input_text_filed_price_list">
                        <input type="text" class="form-control" value="${customLabel}" 
                               name="prices[${firstIndex}][${nameIndex}][side]">
                    </div>
                    <div class="txt_field price_list_icon">
                        <i class="price_list_icon_doller fa-solid fa-dollar-sign"></i>
                        <input type="number" class="form-control" 
                               name="prices[${firstIndex}][${nameIndex}][number]" value="0">
                        <button type="button" class="btn_red btn_global delete_price_list">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>`;

                priceWrapper.append(newRow);
                priceCounters[wrapperId]++;
            });

            $(document).on('click', '.delete_price_list', function() {
                var appendedRow = $(this).closest('.price_list_append_row');
                if (appendedRow.length) {
                    appendedRow.remove();
                } else {
                    $(this).closest('[class*="col-"]').remove();
                }
            });

            if ($('.select2-multiple').length) {
                $('.select2-multiple').select2({
                    placeholder: "Select Route",
                    allowClear: true
                });
            }
        });
    </script>
@endpush
