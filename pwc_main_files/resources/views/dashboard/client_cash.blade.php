@extends('theme.layout.master')

@push('css')
    <style>
        .report_lock_badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 13px;
            font-family: 'Hellix-SemiBold';
            margin-left: 10px;
            background: #FFF4CC;
            color: #A67C00;
        }
    </style>
@endpush
@section('navbar-title')
    <div class="back_btn_navbar back_btn_navbar_create_staff">
        <a href="javascript:void(0);" id="goBackBtn">
            {{--                <a href="{{url('staffroutes')}}"> --}}
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h2 class="navbar_PageTitle">{{ $client->name ?? '' }} Cash</h2>
    </div>
@endsection
@section('content')
    @if (auth()->user()->hasRole('staff') || auth()->user()->hasRole('admin'))
        <section class="create_clients_sec_staff">
            <div class="container-fluid custom_container">
                <div class="row">
                    <div class="col-md-12">
                        <form method="post" action="{{ route($isEditMode ? 'update_payment' : 'save_payment') }}" class="form-horizontal" enctype="multipart/form-data">
                            @csrf
                            <div class="row custom_row">
                                <div class="col-md-12">
                                    <div class="create_clients_wrapper_staff shadow_box_wrapper">
                                        <div class="custom_justify_between">
                                            <div class="custom_radio_mullerHonda">
                                                {{-- <input class="form-check-input" type="checkbox" value="" id="muler_honda"> --}}
{{--                                                <label class="form-check-label">{{ $client->name ?? '' }}</label>--}}
                                                <input type="hidden" name="payment_type" value="cash">
                                                <input type="hidden" name="client_id" value="{{ $client->id }}">
                                                <input type="hidden" name="schedule_id" value="{{ $clientSchedule->id }}">
                                                <input type="hidden" name="final_price" value="{{ $clientPriceSum ?? '' }}">
                                                <input type="hidden" name="month" value="{{ $selectedMonth ?? '' }}">
{{--                                                <span>(Cash)</span>--}}
                                                @if ($isEditMode)
                                                    <span class="report_lock_badge"><i class="fa-solid fa-pen"></i> Editable for 24 hours</span>
                                                @endif
                                            </div>
                                            {{-- {{ dd($clientPriceSum) }} --}}
                                            <h3 class="pricePlus">
                                                ${{ number_format($clientPriceSum, 2, '.', ',') ?? '' }}</h3>
                                        </div>
{{--                                        <div class="price_list_wrapper appended_price_list">--}}
{{--                                            <div class="row">--}}
{{--                                                <div class="txt_field mt-3 mb-3">--}}
{{--                                                    <label>Price List</label>--}}
{{--                                                </div>--}}
{{--                                                @foreach($client->clientPrice as $price)--}}
{{--                                                    <div class="col-md-3 sortable-item">--}}
{{--                                                        <div class="price_list">--}}
{{--                                                            <div class="price_list_box">--}}
{{--                                                                <div class="table_checkbox">--}}
{{--                                                                    <label>{{ $price->name ?? '' }}</label>--}}
{{--                                                                </div>--}}
{{--                                                                <span>${{ $price->value ?? '' }}</span>--}}
{{--                                                            </div>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                @endforeach--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
                                        <!-- Service Date Input -->
                                        <div class="row custom_row mt-3">
                                            <div class="col-md-6">
                                                <div class="txt_field">
                                                    <label for="service_date">Service Date <span style="color: red;">*</span></label>
                                                    <input class="form-control" type="date" name="service_date" id="service_date" value="{{ $isEditMode ? $clientSchedule->service_date : '' }}" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="custom_partially_changed">
                                            <div class="row custom_row">
                                                <div class="col-md-12 custom_no_change">
                                                    <div class="custom_radio">
                                                        <input class="form-check-input complete_no_change" name="option" type="checkbox" value="completed" id="com_no_change" @if ($isEditMode && $existingPayment->option == 'completed') checked @endif>
                                                        <label class="form-check-label" for="com_no_change">Completed no
                                                            Change</label>
                                                    </div>
                                                </div>


                                                <div class="col-md-12 partially_completed_wrapper">
                                                    <div class="custom_radio">
                                                        <input class="form-check-input completed_but_did_not_class complete_no_change check_uncheck check_show_hide" name="option" type="checkbox" value="no_payment" id="recievedPayment" @if ($isEditMode && $existingPayment->option == 'no_payment') checked @endif>
                                                        <label class="form-check-label" for="recievedPayment">Completed
                                                            but did not receive payment</label>
                                                    </div>
                                                    <div class="row reason_input_fileds_wrapper">
                                                        <div class="col-md-12">
                                                            <div class="txt_field">
                                                                <input class="form-control reason_disabled" type="text" name="reason" placeholder="Reason" disabled="disabled" value="{{ $isEditMode ? ($existingPayment->reason ?? '') : '' }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 partially_completed_wrapper">
                                                    <div class="custom_radio">
                                                        <input class="form-check-input partiallly_completed check_uncheck check_show_hide" name="option_five" type="checkbox" value="partially" id="partiallyCompleted" @if ($isEditMode && $existingPayment->option_five == 'partially') checked @endif>
                                                        <label class="form-check-label" for="partiallyCompleted">Partially
                                                            Completed</label>
                                                    </div>
                                                    <div class="row reason_input_fileds_wrapper">
                                                        <div class="price_list_wrapper appended_price_list">
                                                            <div class="row">
                                                                @foreach($client->clientPrice as $price)
                                                                    <div class="col-md-3 sortable-item">
                                                                        <div class="price_list">
                                                                            <div class="price_list_box">
                                                                                <div class="table_checkbox">
                                                                                    <input class="form-check-input price_checkbox_one" type="checkbox"  data-price="{{ $price->value ?? 0 }}">
                                                                                    <label>{{ $price->name ?? '' }}</label>
                                                                                </div>
                                                                                <span>${{ $price->value ?? '' }}</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="txt_field">
                                                                <input class="form-control reason_disabled" type="text" name="reason" placeholder="Reason" disabled="disabled" value="{{ $isEditMode ? ($existingPayment->reason ?? '') : '' }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="txt_field">
                                                                <input class="form-control reason_disabled" type="text" name="partial_completed_scope" placeholder="Scope Of Work Completed" disabled="disabled" value="{{ $isEditMode ? ($existingPayment->partial_completed_scope ?? '') : '' }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="txt_field">
                                                                <input class="form-control reason_disabled price_charged_one" type="text" name="price_charged_one" placeholder="Price Charged" disabled="disabled" value="{{ $isEditMode ? ($existingPayment->price_charge_one ?? '') : '' }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="custom_radio">
                                                        <input class="form-check-input paid_on_prior_fourth_func check_uncheck" name="option_two" type="checkbox" value="paid_on_prior" id="priorDate" @if ($isEditMode && $existingPayment->option_two == 'paid_on_prior') checked @endif>
                                                        <label class="form-check-label" for="priorDate">Paid on prior
                                                            date of service</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 partially_completed_wrapper">
                                                    <div class="custom_radio">
                                                        <input class="form-check-input paid_on_prior_fourth_func check_uncheck check_show_hide" name="option_three" type="checkbox" value="extra_paid_for_date" id="extraPaid" @if ($isEditMode && $existingPayment->option_three == 'extra_paid_for_date') checked @endif>
                                                        <label class="form-check-label" for="extraPaid">Paid extra
                                                            for<input class="form-check-input date_disbaled" disabled="disabled" name="day_number" type="number" placeholder="#" value="{{ $isEditMode ? ($existingPayment->day_number ?? '') : '' }}">dates</label>
                                                    </div>
                                                    <div class="row reason_input_fileds_wrapper">
                                                        <div class="col-md-6">
                                                            <div class="txt_field">
                                                                <input class="form-control reason_disabled" type="text" name="amount" placeholder="Amount" disabled="disabled" value="{{ $isEditMode ? ($existingPayment->amount ?? '') : '' }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6"></div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 partially_completed_wrapper">
                                                    <div class="custom_radio">
                                                        <input class="form-check-input paid_on_prior_fourth_func check_uncheck check_show_hide" name="option_four" type="checkbox" value="extra_work" id="workCompleted" @if ($isEditMode && $existingPayment->option_four == 'extra_work') checked @endif>
                                                        <label class="form-check-label" for="workCompleted">Extra Work Completed</label>
                                                    </div>
                                                    <div class="row reason_input_fileds_wrapper">
                                                        <div class="price_list_wrapper appended_price_list">
                                                            <div class="row">
                                                                @foreach($client->clientPrice as $price)
                                                                    <div class="col-md-3 sortable-item">
                                                                        <div class="price_list">
                                                                            <div class="price_list_box">
                                                                                <div class="table_checkbox">
                                                                                    <input class="form-check-input price_checkbox_two" type="checkbox"  data-price="{{ $price->value ?? 0 }}">
                                                                                    <label>{{ $price->name ?? '' }}</label>
                                                                                </div>
                                                                                <span>${{ $price->value ?? '' }}</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="txt_field">
                                                                <input class="form-control reason_disabled" type="text" name="scope" placeholder="Scope Of Additional Work Completed" disabled="disabled" value="{{ $isEditMode ? ($existingPayment->scope ?? '') : '' }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="txt_field">
                                                                <input class="form-control reason_disabled price_charged_two" type="text" name="price_charged_two" placeholder="Price Charged" disabled="disabled" value="{{ $isEditMode ? ($existingPayment->price_charge_two ?? '') : '' }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 partially_completed_wrapper">
                                                    <div class="custom_radio">
                                                        <input class="form-check-input check_uncheck check_show_hide" name="option_three" type="checkbox" value="logTime" id="logTime" @if ($isEditMode && $existingPayment->option_three == 'logTime') checked @endif>
                                                        <label class="form-check-label" for="logTime">Log time</label>
                                                    </div>
                                                    <div class="row reason_input_fileds_wrapper">
                                                        <div class="col-md-6">
                                                            <div class="txt_field">
                                                                <label>Start Time</label>
                                                                <input class="form-control reason_disabled" type="time" name="start_time" placeholder="Start Time" disabled="disabled" value="{{ $isEditMode ? ($existingPayment->start_time ?? '') : '' }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="txt_field">
                                                                <label>End Time</label>
                                                                <input class="form-control reason_disabled" type="time" name="end_time" placeholder="End Time" disabled="disabled" value="{{ $isEditMode ? ($existingPayment->end_time ?? '') : '' }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 partially_completed_wrapper">
                                                    <div class="custom_radio">
                                                        <input class="form-check-input omit_class complete_no_change  check_uncheck check_show_hide" name="option" type="checkbox" value="omit" id="omit" @if ($isEditMode && $existingPayment->option == 'omit') checked @endif>
                                                        <label class="form-check-label" for="omit">Omit</label>
                                                    </div>
                                                    <div class="row reason_input_fileds_wrapper">
                                                        <div class="col-md-6">
                                                            <div class="txt_field">
                                                                <input class="form-control reason_disabled" type="text" name="reason" placeholder="Reason" disabled="disabled" value="{{ $isEditMode ? ($existingPayment->reason ?? '') : '' }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="custom_justify_between">
                                        <button type="button" class="btn_global btn_grey" id="goBackBtnCancel">Cancel<i class="fa-solid fa-close"></i></button>
                                        <button type="submit" class="btn_global btn_blue">{{ $isEditMode ? 'Save Changes' : 'Submit' }}<i class="fa-solid {{ $isEditMode ? 'fa-check' : 'fa-plus' }}"></i></button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    @endif
@endsection
@push('js')
    {{-- searchbar functionality --}}
    <script>
        $(document).ready(function() {
            $('.searchInput').on('input', function() {
                var filter = $(this).val().toLowerCase();

                $('.tab-content').each(function() {
                    var h2Text = $(this).find('h2').text().toLowerCase();
                    if (h2Text.includes(filter)) {
                        $(this).show(); // Show the matching element
                    } else {
                        $(this).hide(); // Hide the non-matching element
                    }
                });
            });
        });
    </script>
    {{-- show hide functionality --}}{{-- and check un chack functionality --}}
    <script>
        $(document).ready(function() {
            // Sab reason input fields hide rakhain initially
            $(".reason_input_fileds_wrapper").hide();

            // Show/Hide reason input fields aur enable/disable date input
            $(".check_show_hide").change(function() {
                let parentWrapper = $(this).closest(".partially_completed_wrapper");

                if ($(this).prop("checked")) {
                    parentWrapper.find(".reason_input_fileds_wrapper").slideDown();
                    parentWrapper.find(".date_disbaled").prop("disabled", false);
                } else {
                    parentWrapper.find(".reason_input_fileds_wrapper").slideUp();
                    parentWrapper.find(".date_disbaled").prop("disabled", true);
                    parentWrapper.find(".date_disbaled").val('');

                }
            });
            $(".check_show_hide").change(function() {
                let parentWrapper = $(this).closest(".partially_completed_wrapper");

                if ($(this).prop("checked")) {
                    parentWrapper.find(".reason_input_fileds_wrapper").slideDown();
                    parentWrapper.find(".reason_disabled").prop("disabled", false);
                } else {
                    parentWrapper.find(".reason_input_fileds_wrapper").slideUp();
                    parentWrapper.find(".reason_disabled").prop("disabled", true);
                    parentWrapper.find(".reason_disabled").val('');

                }
            });

            // Mutual Exclusivity: Only ONE checkbox can be checked at a time,
            // except:
            //  - Log time is fully independent - it is never unchecked by any
            //    other checkbox, and checking it never unchecks anything else.
            //  - "Completed but did not receive payment", "Partially Completed"
            //    and "Extra Work Completed" can all be combined together.
            var mainCheckboxes = "#com_no_change, #recievedPayment, #partiallyCompleted, #omit, #priorDate, #extraPaid, #workCompleted";
            var compatibleGroup = "#recievedPayment, #partiallyCompleted, #workCompleted";

            $(mainCheckboxes).change(function() {
                if ($(this).prop("checked")) {
                    var $checked = $(this);
                    // Uncheck all other checkboxes except this one
                    $(mainCheckboxes).not(this).each(function() {
                        var $other = $(this);

                        if ($checked.is(compatibleGroup) && $other.is(compatibleGroup)) {
                            return; // keep the compatible group checked together
                        }

                        $other.prop("checked", false);
                        // Trigger change to hide any related fields
                        if ($other.hasClass("check_show_hide")) {
                            $other.closest(".partially_completed_wrapper").find(".reason_input_fileds_wrapper").slideUp();
                            $other.closest(".partially_completed_wrapper").find(".reason_disabled").prop("disabled", true).val('');
                            $other.closest(".partially_completed_wrapper").find(".date_disbaled").prop("disabled", true).val('');
                        }
                    });
                }
            });

            // Reveal/enable fields for whichever option came pre-checked (edit mode)
            $(".check_show_hide:checked").trigger("change");
        });


        //For Dynamic Pricing
        $(document).ready(function() {
            let originalPrice = parseFloat($('.pricePlus').text().replace('$', '').replace(',', ''));
            let currentPrice = originalPrice;

            function updatePrice() {
                let newPrice = originalPrice; // Start with original price

                // Check if "Partially Completed" is selected
                if ($('#partiallyCompleted').prop('checked')) {
                    let partialPrice = parseFloat($('input[name="price_charged_one"]').val()) || 0;
                    newPrice = partialPrice; // Replace with partial price
                }

                // Add extra work price if selected
                if ($('#workCompleted').prop('checked')) {
                    let extraWorkPrice = parseFloat($('input[name="price_charged_two"]').val()) || 0;
                    newPrice += extraWorkPrice;
                }

                // Add extra paid amount if selected
                if ($('#extraPaid').prop('checked')) {
                    let extraAmount = parseFloat($('input[name="amount"]').val()) || 0;
                    newPrice += extraAmount;
                }

                // Set to 0 if "Omit" is selected
                if ($('#omit').prop('checked')) {
                    newPrice = 0;
                }

                // Update UI
                $('.pricePlus').text('$' + newPrice.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
                $('input[name="final_price"]').val(newPrice);
                currentPrice = newPrice;
            }

            // Listen to checkbox changes
            $('#partiallyCompleted, #workCompleted, #extraPaid, #omit, #com_no_change, #recievedPayment').change(function() {
                updatePrice();
            });

            // Listen to input field changes
            $('input[name="price_charged_one"], input[name="price_charged_two"], input[name="amount"]').on('input', function() {
                let value = $(this).val();

                if (/^\d+(\.\d{0,2})?$/.test(value) || value === "") {
                    updatePrice();
                } else {
                    $(this).val('');
                }
            });

            // Reset to original price when "Completed no Change" is selected
            $('#com_no_change').change(function() {
                if ($(this).prop('checked')) {
                    $('.pricePlus').text('$' + originalPrice.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
                    $('input[name="final_price"]').val(originalPrice);
                }
            });

            // Note: "Completed but did not receive payment" no longer force-resets the
            // price here - it can now be combined with Partially Completed / Extra Work
            // Completed, so updatePrice() (bound above) already computes the right total.

            let totalPriceOne = 0;
            let totalPriceTwo = 0;

            $('.reason_input_fileds_wrapper').hide();
            $('.check_show_hide').click(function() {
                $(this).closest('.partially_completed_wrapper').find('.reason_input_fileds_wrapper').toggle();
            });

            $(".price_checkbox_one").on("change", function() {
                const price = parseFloat($(this).data('price')) || 0;

                if ($(this).prop('checked')) {
                    totalPriceOne += price;
                } else {
                    totalPriceOne -= price;
                }

                $(".price_charged_one").val(totalPriceOne.toFixed(2));
                updatePrice();

            });

            $(".price_checkbox_two").on("change", function() {
                const price = parseFloat($(this).data('price')) || 0;

                if ($(this).prop('checked')) {
                    totalPriceTwo += price;
                } else {
                    totalPriceTwo -= price;
                }

                $(".price_charged_two").val(totalPriceTwo.toFixed(2));
                updatePrice();

            });

            // Re-sync price once initial pre-checked fields have been revealed (edit mode)
            updatePrice();
        });

        $(document).ready(function() {
            $('#goBackBtn, #goBackBtnCancel').on('click', function(e) {
                e.preventDefault();
                window.history.back();
            });
        });

        // Confirm before submitting payment
        $(document).ready(function() {
            $('.form-horizontal').on('submit', function(e) {
                if (!$(this).data('confirmed')) {
                    e.preventDefault();
                    let form = this;
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'Please confirm you want to report this schedule.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Submit',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $(form).data('confirmed', true);
                            form.submit();
                        }
                    });
                }
            });
        });
    </script>
@endpush
