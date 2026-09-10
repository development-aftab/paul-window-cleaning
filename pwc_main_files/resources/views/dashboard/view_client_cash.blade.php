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
            background: var(--dark_blue);
            color: #FFFFFF;
        }

        .create_clients_sec_staff .price_list_wrapper .price_list_flex {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }

        .create_clients_sec_staff .price_list_wrapper .price_list_item {
            flex: 0 0 auto;
            max-width: 100%;
        }

        .create_clients_sec_staff .price_list_wrapper .price_list_flex .price_list {
            background: #F5F6FA;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 0;
        }

        .create_clients_sec_staff .price_list_wrapper .price_list_flex .price_list_box {
            display: flex;
            flex-wrap: nowrap;
            align-items: center;
            justify-content: flex-start;
            gap: 10px;
        }

        .create_clients_sec_staff .price_list_wrapper .price_list_flex .table_checkbox {
            display: flex;
            flex-wrap: nowrap;
            align-items: center;
            gap: 8px;
            margin: 0;
        }

        .create_clients_sec_staff .price_list_wrapper .price_list_flex .table_checkbox input[type="checkbox"] {
            width: 18px;
            height: 18px;
            border-radius: 4px;
            border: 1.5px solid #C9CDD3;
            margin: 0;
            flex-shrink: 0;
        }

        .create_clients_sec_staff .price_list_wrapper .price_list_flex .table_checkbox label {
            font-size: 14px;
            color: #1D1F2C;
            margin: 0;
            white-space: nowrap;
        }

        .create_clients_sec_staff .price_list_wrapper .price_list_flex .price_list span {
            font-size: 14px;
            color: #667085;
            font-weight: 500;
            white-space: nowrap;
        }
    </style>
@endpush
@section('navbar-title')

    <div class="back_btn_navbar back_btn_navbar_create_staff">
        <a href="javascript:void(0);" id="goBackBtn">
            {{--                <a href="{{url('staffroutes')}}">--}}
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h2 class="navbar_PageTitle">Client Cash</h2>
    </div>

@endsection
@section('content')
    <section class="create_clients_sec_staff">
        <div class="container-fluid custom_container">
            <div class="row">
                <div class="col-md-12">
                    <form method="post" action="#" class="form-horizontal"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="row custom_row">
                            <div class="col-md-12">
                                <div class="create_clients_wrapper_staff shadow_box_wrapper">
                                    <div class="custom_justify_between">
                                        <div class="custom_radio_mullerHonda">
                                            <label class="form-check-label">{{$client->user->name??''}}</label>
                                            <span>(Cash)</span>
                                            <span class="report_lock_badge"><i class="fa-solid fa-lock"></i> Locked</span>
                                        </div>
                                        <h3 class="pricePlus">
                                            ${{ number_format($clientPriceSum, 2, '.', ',') ?? '' }}</h3>
                                    </div>
                                    <div class="price_list_wrapper appended_price_list">
                                        <div class="price_list_flex mt-5">
                                            @foreach($client->clientPrice as $price)
                                                <div class="price_list_item">
                                                    <div class="price_list">
                                                        <div class="price_list_box">
                                                            <div class="table_checkbox">
                                                                <input class="form-check-input" type="checkbox" disabled
                                                                    @if(in_array($price->id, $mergedPriceIds)) checked @endif>
                                                                <label>{{ $price->name ?? '' }}</label>
                                                            </div>
                                                            <span>${{ $price->value ?? '' }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="custom_partially_changed">
                                        <div class="row custom_row">
                                            <div class="col-md-12 custom_no_change">
                                                <div class="custom_radio">
                                                    <input class="form-check-input complete_no_change" name="option"
                                                           type="checkbox" value="completed"
                                                           id="com_no_change" disabled
                                                           @if($clientSchedule->clientSchedulePayment->option == 'completed') checked
                                                        @endif>
                                                    <label class="form-check-label" for="com_no_change">Completed no
                                                        Change</label>
                                                </div>
                                            </div>

                                            <div class="col-md-12 partially_completed_wrapper">
                                                <div class="custom_radio">
                                                    <input
                                                        class="form-check-input completed_but_did_not_class complete_no_change check_uncheck check_show_hide"
                                                        name="option" type="checkbox" value="no_payment"
                                                        id="recievedPayment" disabled
                                                        @if($clientSchedule->clientSchedulePayment->option == 'no_payment') checked
                                                        @endif>
                                                    <label class="form-check-label" for="recievedPayment">Completed
                                                        but did not receive payment</label>
                                                </div>
                                                <div class="row reason_input_fileds_wrapper"
                                                     @if($clientSchedule->clientSchedulePayment->option != 'no_payment') hidden
                                                    @endif>
                                                    <div class="price_list_wrapper appended_price_list">
                                                        <div class="price_list_flex">
                                                            @foreach($client->clientPrice as $price)
                                                                <div class="price_list_item">
                                                                    <div class="price_list">
                                                                        <div class="price_list_box">
                                                                            <div class="table_checkbox">
                                                                                <input class="form-check-input" type="checkbox" disabled
                                                                                    @if(in_array($price->id, $mergedPriceIds)) checked @endif>
                                                                                <label>{{ $price->name ?? '' }}</label>
                                                                            </div>
                                                                            <span>${{ $price->value ?? '' }}</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="txt_field">
                                                            <input class="form-control reason_disabled" type="text"
                                                                   name="reason" placeholder="Reason"
                                                                   disabled="disabled"
                                                                   value="{{ $clientSchedule->clientSchedulePayment->reason ?? '' }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12 partially_completed_wrapper">
                                                <div class="custom_radio">
                                                    <input
                                                        class="form-check-input partiallly_completed check_uncheck check_show_hide"
                                                        name="option_five" type="checkbox" value="partially"
                                                        id="partiallyCompleted" disabled
                                                        @if($clientSchedule->clientSchedulePayment->option_five == 'partially') checked
                                                        @endif>
                                                    <label class="form-check-label" for="partiallyCompleted">Partially
                                                        Completed</label>
                                                </div>
                                                <div class="row reason_input_fileds_wrapper"
                                                     @if($clientSchedule->clientSchedulePayment->option_five != 'partially') hidden
                                                    @endif>
                                                    <div class="col-md-6">
                                                        <div class="txt_field">
                                                            <input class="form-control reason_disabled" type="text"
                                                                   name="reason" placeholder="Reason"
                                                                   disabled="disabled"
                                                                   value="{{ $clientSchedule->clientSchedulePayment->reason ?? '' }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="txt_field">
                                                            <input class="form-control reason_disabled" type="text"
                                                                   name="price_charged_one"
                                                                   value="{{ $clientSchedule->clientSchedulePayment->price_charge_one ?? '' }}"
                                                                   placeholder="Price Charged" disabled="disabled">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="custom_radio">
                                                    <input
                                                        class="form-check-input paid_on_prior_fourth_func check_uncheck"
                                                        name="option_two" type="checkbox" value="PaidOnPrior"
                                                        id="priorDate" disabled
                                                        @if($clientSchedule->clientSchedulePayment->option_two == 'PaidOnPrior') checked
                                                        @endif>
                                                    <label class="form-check-label" for="priorDate">Paid on prior
                                                        date of service</label>
                                                </div>
                                            </div>
                                            <div class="col-md-12 partially_completed_wrapper">
                                                <div class="custom_radio">
                                                    <input disabled
                                                           class="form-check-input paid_on_prior_fourth_func check_uncheck check_show_hide"
                                                           name="option_three" type="checkbox" value="paidExtra"
                                                           id="extraPaid"
                                                           @if($clientSchedule->clientSchedulePayment->option_three == 'paidExtra') checked
                                                        @endif>
                                                    <label class="form-check-label" for="extraPaid">Paid extra
                                                        for<input class="form-check-input date_disbaled"
                                                                  disabled="disabled" name="day_number"
                                                                  type="number" placeholder="#"
                                                                  value="{{ $clientSchedule->clientSchedulePayment->day_number ?? '' }}">dates</label>
                                                </div>
                                                <div class="row reason_input_fileds_wrapper"
                                                     @if($clientSchedule->clientSchedulePayment->option_three != 'paidExtra') hidden
                                                    @endif>
                                                    <div class="col-md-6">
                                                        <div class="txt_field">
                                                            {{--<input class="form-check-input" type="checkbox" value="" id="amount">--}}
                                                            <input class="form-control reason_disabled" type="text"
                                                                   name="amount" placeholder="Amount"
                                                                   disabled="disabled"
                                                                   value="{{ $clientSchedule->clientSchedulePayment->amount ?? '' }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6"></div>
                                                </div>
                                            </div>

                                            <div class="col-md-12 partially_completed_wrapper">
                                                <div class="custom_radio">
                                                    <input
                                                        class="form-check-input paid_on_prior_fourth_func check_uncheck check_show_hide"
                                                        name="option_four" type="checkbox" value="extraWork"
                                                        id="workCompleted" disabled
                                                        @if($clientSchedule->clientSchedulePayment->option_four == 'extraWork') checked
                                                        @endif>
                                                    <label class="form-check-label" for="workCompleted">Extra Work
                                                        Completed</label>
                                                </div>
                                                <div class="row reason_input_fileds_wrapper"
                                                     @if($clientSchedule->clientSchedulePayment->option_four != 'extraWork') hidden
                                                    @endif>
                                                    <div class="col-md-6">
                                                        <div class="txt_field">
                                                            {{--<input class="form-check-input" type="checkbox" value="" id="workScope">--}}
                                                            <input class="form-control reason_disabled" type="text"
                                                                   name="scope" placeholder="Scope"
                                                                   disabled="disabled"
                                                                   value="{{ $clientSchedule->clientSchedulePayment->scope ?? '' }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="txt_field">
                                                            {{--<input class="form-check-input" type="checkbox" value="" id="extraPriceCharged">--}}
                                                            <input class="form-control reason_disabled" type="text"
                                                                   name="price_charged_two"
                                                                   value="{{ $clientSchedule->clientSchedulePayment->price_charge_two ?? '' }}"
                                                                   placeholder="Price Charged" disabled="disabled">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12 partially_completed_wrapper">
                                                <div class="custom_radio">
                                                    <input disabled
                                                           class="form-check-input check_uncheck check_show_hide"
                                                           name="option_three" type="checkbox" value="logTime"
                                                           id="logTime"
                                                           @if($clientSchedule->clientSchedulePayment->option_three == 'logTime') checked
                                                        @endif>
                                                    <label class="form-check-label" for="logTime">Log time</label>
                                                </div>
                                                <div class="row reason_input_fileds_wrapper"
                                                     @if($clientSchedule->clientSchedulePayment->option_three != 'logTime') hidden
                                                    @endif>
                                                    <div class="col-md-6">
                                                        <div class="txt_field">
                                                            <input class="form-control reason_disabled" type="time"
                                                                   name="start_time" placeholder="Start Time"
                                                                   disabled="disabled"
                                                                   value="{{ $clientSchedule->clientSchedulePayment->start_time ?? '' }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="txt_field">
                                                            <input class="form-control reason_disabled" type="time"
                                                                   name="end_time" placeholder="End Time"
                                                                   disabled="disabled"
                                                                   value="{{ $clientSchedule->clientSchedulePayment->end_time ?? '' }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12 partially_completed_wrapper">
                                                <div class="custom_radio">
                                                    <input disabled
                                                           class="form-check-input omit_class complete_no_change  check_uncheck check_show_hide"
                                                           name="option" type="checkbox" value="omit" id="omit"
                                                           @if($clientSchedule->clientSchedulePayment->option == 'omit') checked
                                                        @endif>
                                                    <label class="form-check-label" for="omit">Omit</label>
                                                </div>
                                                <div class="row reason_input_fileds_wrapper"
                                                     @if($clientSchedule->clientSchedulePayment->option != 'omit') hidden
                                                    @endif>
                                                    <div class="col-md-6">
                                                        <div class="txt_field">
                                                            {{--<input class="form-check-input" type="checkbox" value="" id="omitReason">--}}
                                                            <input class="form-control reason_disabled" type="text"
                                                                   name="reason" placeholder="Reason"
                                                                   disabled="disabled"
                                                                   value="{{ $clientSchedule->clientSchedulePayment->reason ?? '' }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('js')
    {{--searchbar functionality--}}
    <script>
        $(document).ready(function () {
            $('.searchInput').on('input', function () {
                var filter = $(this).val().toLowerCase();

                $('.tab-content').each(function () {
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

    <script>

        $(document).ready(function () {
            $('#goBackBtn').on('click', function (e) {
                e.preventDefault();
                window.history.back();
            });
        });


    </script>
@endpush
