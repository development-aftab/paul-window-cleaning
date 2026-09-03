@extends('theme.layout.master')
@push('css')
    <style>
        .repeat_every_radio_wrapper {
            border: 2px solid #00ADEE;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .repeat_every_radio_wrapper h4 {
            margin: 0;
        }

        .repeat_every_radio_wrapper .radio_btn_wrapper {
            align-items: center;
        }

        .repeat_every_radio_wrapper .radio_btn_wrapper .form-check {
            margin: 0;
        }

        .sortable-ghost {
            opacity: 0.4;
            background: #f0f0f0;
        }

        .sortable-item {
            cursor: move;
        }

        .price_list:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .toggle_hidden_content {
            display: none;
        }

        .selected-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 20px;
        }

        .tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background-color: #00ADEE;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .tag-close {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 12px;
            transition: background-color 0.2s;
        }

        .tag-close:hover {
            background-color: rgba(255, 255, 255, 0.5);
        }

        .checkbox-input {
            width: 18px;
            height: 18px;
            margin: 0;
            cursor: pointer;
        }

        .checkbox-input:checked {
            background-color: #00ADEE;
            border-color: #00ADEE;
        }

        .note_accordion {
            margin: 10px
        }

        .note_accordion .accordion-button {
            font-weight: 600;
            background-color: #F0F0F0;
            color: #333;
            padding: 15px;
        }

        .note_accordion .accordion-button:not(.collapsed) {
            background: rgba(0, 173, 238, 0.06)
        }

        .note_accordion .accordion-item {
            margin-top: 10px;
        }

        .create_clients_wrapper .shadow_box_wrapper {
            height: unset
        }

        .date-note-label {
            color: #4A4A4A;
            font-family: 'Hellix-SemiBold';
            font-size: 16px;
            font-weight: 600;
            line-height: 24px;
            width: 180px;
        }

        /* Note Reset Button Styles */
        .note-reset-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 14px;
            font-size: 12px;
            font-weight: 600;
            color: #fff;
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            border: none;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(238, 90, 36, 0.3);
            margin-left: auto;
            margin-right: 12px;
            z-index: 2;
            position: relative;
            letter-spacing: 0.3px;
        }

        .note-reset-btn:hover {
            background: linear-gradient(135deg, #fc5c65, #d63031);
            box-shadow: 0 4px 14px rgba(238, 90, 36, 0.45);
            transform: translateY(-1px);
        }

        .note-reset-btn:active {
            transform: translateY(0);
            box-shadow: 0 1px 4px rgba(238, 90, 36, 0.25);
        }

        .note-reset-btn i {
            font-size: 11px;
        }

        .note_accordion .accordion-header {
            position: relative;
        }

        .note_accordion .accordion-header .note-reset-btn {
            position: absolute;
            right: 50px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 5;
        }
    </style>
@endpush
@section('navbar-title')
    <div class="custom_justify_between create_clients_navbar">
        <a href="{{ request()->query('first-time') ? route('clients.edit', $client->id) : url('clients') }}" class="back_btn_navbar">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h2 class="navbar_PageTitle">Schedule</h2>
    </div>
@endsection
@section('content')
    @if (auth()->user()->hasRole('admin'))
        <section class="create_clients_sec schedule_main_sec">
            <div class="container-fluid custom_container">
                <div class="row card_alignment">
                    <div class="col-md-12">
                        <div class="shadow_box_wrapper">
                            <div class="custom_details_wrapper">
                                <div class="client_info">
                                    <div class="row custom_row">
                                        <div class="col-md-10">
                                            <div class="row client_detail_row">
                                                <div class="col-md-12">
                                                    <div class="custom_justify_between">
                                                        <h2>{{ $client->name ?? '-' }}</h2>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="txt_field_wrapper">
                                                        <label>Email :</label>
                                                        <span>{{ $client->contact_email ?? 'Not Available' }}</span>
                                                    </div>
                                                </div>
                                                {{-- <div class="col-md-4">
                                                    <div class="txt_field_wrapper">
                                                        <label>Phone :</label>
                                                        <span>{{ $client->contact_phone ?? 'Not Available' }}</span>
                                                    </div>
                                                </div> --}}
                                                <div class="col-md-4">
                                                    <div class="txt_field_wrapper">
                                                        <label>Phone :</label>
                                                        <span>{{ $client->formatted_phone ?? 'Not Available' }}</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="txt_field_wrapper">
                                                        <label>Date Created :</label>
                                                        <span>
                                                            <td>{{ $client->created_at->format('m/d/Y') ?? 'Not Available' }}
                                                            </td>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

{{--                                        @if ($client->service_frequency != 'normalWeek')--}}
{{--                                            <div class="col-md-2">--}}
{{--                                                <div class="starting_date_box">--}}
{{--                                                    <div class="form-floating txt_field custom_dates">--}}
{{--                                                        <input type="date" class="form-control startDate" value="{{ $client->start_date ?? '' }}" name="start_date" id="startDate" placeholder="">--}}
{{--                                                        <label for="startDate">Starting Date</label>--}}
{{--                                                        <p id="startDateError" style="color: red; display: none;">--}}
{{--                                                            Starting date cannot be today or in the past.--}}
{{--                                                        </p>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                                <br>--}}
{{--                                                <button type="button" style="width: 182px;" class="btn_global btn_blue submitbtn">--}}
{{--                                                    Update Schedule<i class="fa-solid"></i>--}}
{{--                                                </button>--}}
{{--                                            </div>--}}
{{--                                        @endif--}}
                                        <div class="col-md-12" style="display:none;">
                                            <h4>Frequency of Service</h4>
                                            <div class="cycle_frequency_wrapper">
                                                <div class="row create_client_cus_row">
                                                    <div class="col-md-12">
                                                        <div class="radio_btn_wrapper radio_btn_wrapper_info_icon">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" value="normalWeek" name="service_frequency" id="quarterly" {{ ($client->service_frequency ?? '') == 'normalWeek' ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="quarterly">
                                                                    4-Week Cycle
                                                                    <span class="info-icon" style="" data-toggle="tooltip" title="This is the 4-Week Cycle (week1 to week4),4 weeks gap.">
                                                                        <i class="fas fa-info-circle"></i>
                                                                    </span>
                                                                </label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" value="monthly" name="service_frequency" id="monthly" {{ ($client->service_frequency ?? '') == 'monthly' ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="monthly">
                                                                    Monthly Cycle
                                                                    <span class="info-icon" style="" data-toggle="tooltip" title="This is the Monthly Cycle (4 week gap).">
                                                                        <i class="fas fa-info-circle"></i>
                                                                    </span>
                                                                </label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" value="biMonthly" name="service_frequency" id="bi_monthly" {{ ($client->service_frequency ?? '') == 'biMonthly' ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="bi_monthly">
                                                                    Bi-Monthly Cycle
                                                                    <span class="info-icon" style="" data-toggle="tooltip" title="This is the Bi-Monthly Cycle Twice a month.">
                                                                        <i class="fas fa-info-circle"></i>
                                                                    </span>
                                                                </label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" value="eightWeek" name="service_frequency" id="eightWeek" {{ ($client->service_frequency ?? '') == 'eightWeek' ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="eightWeek">
                                                                    8-Week cycle
                                                                    <span class="info-icon" style="" data-toggle="tooltip" title="This is the 8-Week Cycle (week1 to week8),8 weeks gap.">
                                                                        <i class="fas fa-info-circle"></i>
                                                                    </span>
                                                                </label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" value="quarterly" name="service_frequency" id="bi_annually" {{ ($client->service_frequency ?? '') == 'quarterly' ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="bi_annually">
                                                                    Quarterly Cycle
                                                                    <span class="info-icon" style="" data-toggle="tooltip" title="This is the Quarterly Cycle (week1 to week12),12 weeks gap.">
                                                                        <i class="fas fa-info-circle"></i>
                                                                    </span>
                                                                </label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" value="biAnnually" name="service_frequency" id="bi_annually" {{ ($client->service_frequency ?? '') == 'biAnnually' ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="biAnnually">
                                                                    Bi-Annually Cycle
                                                                    <span class="info-icon" style="" data-toggle="tooltip" title="This is the Bi Annually Cycle Twice a year.">
                                                                        <i class="fas fa-info-circle"></i>
                                                                    </span>
                                                                </label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" value="annually" name="service_frequency" id="annually" {{ ($client->service_frequency ?? '') == 'annually' ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="annually">
                                                                    Bi-Annually Cycle
                                                                    <span class="info-icon" style="" data-toggle="tooltip" title="This is the Bi Annually Cycle Twice a year.">
                                                                        <i class="fas fa-info-circle"></i>
                                                                    </span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-10"></div>

                                                    <div class="col-md-2 float-end">
                                                        <div class="form-floating starting_date_box float-end">
                                                            <div class=" txt_field custom_dates">
                                                                <input type="date" class="form-control" value="{{ $client->second_start_date ?? '' }}" name="start_date_second" id="startDateSecond" placeholder="" />
                                                                <label for="endDate">Second Start Date</label>
                                                                <p id="endDateError" style="color: red; display: none;">
                                                                    Ending date must be at least 7 days after the start
                                                                    date.
                                                                </p>
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
                    <div class="col-md-12">
                        <div class="create_clients_wrapper">
                            <form method="post" action="{{ route('client_schedule_save', ['id' => $client->id]) }}" class="form-horizontal" id="clientValidate" enctype="multipart/form-data">
                                @csrf
                                <div class="row create_client_cus_row">
                                    <div class="col-md-12">
                                        @php
                                            $weekNumber = 1;
                                        @endphp
                                        <div class="shadow_box_wrapper services_shadow_wrapper">
                                            <div class="services_week">
                                                <h3>Weeks Of Service</h3>
                                            </div>

                                            <div class="selected-tags" id="selectedTags"></div>
                                        </div>
                                        <div class="monthly_schedule_box">
                                            <div class="custom_checkbox_wrapper assign_week mb-5 input_shadow_wrapper" style="display: flex; flex-wrap: nowrap; overflow-x: auto;">
                                                @foreach ($months as $monthIndex => $month)
                                                    @php
                                                        $skipMonthlyWeek = false;
                                                        if (($client->service_frequency ?? '') === 'monthly' && !empty($client->start_date)) {
                                                            try {
                                                                $clientStartDate = \Carbon\Carbon::createFromFormat('d/m/Y', $client->start_date)->startOfDay();
                                                            } catch (\Exception $e) {
                                                                $clientStartDate = \Carbon\Carbon::parse($client->start_date)->startOfDay();
                                                            }
                                                            $weekStartDate = \Carbon\Carbon::parse($month['start_date'])->startOfDay();
                                                            $weekEndDate = \Carbon\Carbon::parse($month['end_date'])->endOfDay();
                                                            $skipMonthlyWeek = !$clientStartDate->between($weekStartDate, $weekEndDate);
                                                        }
                                                    @endphp
                                                    @if ($skipMonthlyWeek)
                                                        @continue
                                                    @endif
                                                    <div class="monthly_schedule">
                                                        <h4 style="display: none">{{ $month['month'] }}
                                                            {{ $month['year'] }}</h4>
                                                    </div>
                                                    @php
                                                        $isChecked = false;
                                                        if (!empty($client->clientSchedule)) {
                                                            foreach ($client->clientSchedule as $schedule) {
                                                                if ($schedule->week === 'week' . $month['week_index']) {
                                                                    $isChecked = true;
                                                                    break;
                                                                }
                                                            }
                                                        }

                                                        $startDate = \Carbon\Carbon::parse($month['start_date']);
                                                        $endDate = \Carbon\Carbon::parse($month['end_date']);

                                                        if ($startDate->month !== $endDate->month) {
                                                            $startDateFormat = $startDate->format('d F');
                                                            $endDateFormat = $endDate->format('d F');
                                                        } else {
                                                            $startDateFormat = $startDate->format('d F');
                                                            $endDateFormat = $endDate->format('d F');
                                                        }
                                                    @endphp
                                                    <div class="custom_radio custom_clients_schedule_radio checkbox-group" style="margin-right: 15px; margin-bottom: 10px; flex: 0 0 auto;">
                                                        <input class="form-check-input week_checkbox checkbox-input" value="{{ $month['start_date'] }}" type="checkbox" data-id="{{ $month['month'] }}_{{ $month['week_index'] }}" data-end="{{ $month['end_date'] }}" data-week="week{{ $month['week_index'] }}" data-month="{{ $month['month'] }}"
                                                            id="week_{{ $month['month'] }}_{{ $month['week_index'] }}" @if ($isChecked) checked @endif>
                                                        <label class="form-check-label " for="week_{{ $month['month'] }}_{{ $month['week_index'] }}">
                                                            Week {{ $weekNumber }}
                                                        </label>
                                                    </div>

                                                    @php
                                                        $weekNumber++;
                                                    @endphp
                                                @endforeach
                                            </div>
                                            <div class="add_frequency_week_note"></div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="custom_justify_between">
                                                {{--                                                <button type="button" class="btn_global btn_grey">Cancel<i class="fa-solid fa-close"></i></button> --}}
                                                <input type="hidden" name="stay_on_page" id="stayOnPage" value="0">
                                                <button type="submit" class="btn_global btn_blue submitButton">Update<i class="fa-solid fa-plus"></i></button>
                                                <button type="submit" class="btn_global btn_blue submitButtonStay">Update & Stay<i class="fa-solid fa-plus"></i></button>
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
        <section class="create_clients_sec schedule_main_sec">
            <div class="container-fluid custom_container">
                <div class="row card_alignment">
                    <div class="col-md-12">
                        <div class="shadow_box_wrapper">
                            <div class="custom_details_wrapper">
                                <div class="client_info">
                                    <div class="row custom_row">
                                        <div class="col-md-12">
                                            <div class="custom_justify_between">
                                                {{--                                                <h2>{{ \Carbon\Carbon::parse($currentMonthReal)->format('M') ?? '' }}, --}}
                                                {{--                                                    {{ $startOfWeek ?? '' }} thru {{ $endOfWeek ?? '' }}, --}}
                                                {{--                                                    {{ $currentYearReal ?? '' }} (Week {{ $weekNumberReal ?? '' }})</h2> --}}
                                                {{--                                                <button type="button" class="btn_global btn_blue submitbtn"> --}}
                                                {{--                                                    Update Schedule<i class="fa-solid"></i> --}}
                                                {{--                                                </button> --}}
                                            </div>
                                        </div>
                                        <div class="col-md-10">
                                            <div class="row client_detail_row">
                                                <div class="col-md-12">
                                                    <div class="custom_justify_between">
                                                        <h2>
                                                            {{ $client->name ?? 'Not Available' }}
                                                        </h2>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="txt_field_wrapper">
                                                        <label>Email :</label>
                                                        <span>{{ $client->contact_email ?? 'Not Available' }}</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="txt_field_wrapper">
                                                        <label>Phone :</label>
                                                        <span>{{ $client->formatted_phone ?? 'Not Available' }}</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="txt_field_wrapper">
                                                        <label>Date Created :</label>
                                                        <span>
                                                            <td>{{ $client->created_at->format('m/d/Y') ?? 'Not Available' }}
                                                            </td>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- <div class="col-md-2">
                                            <div class="starting_date_box">
                                                <div class="form-floating txt_field custom_dates">
                                                    <input type="date" class="form-control startDate"
                                                        value="{{ $client->start_date ?? '' }}" name="start_date"
                                                        id="startDate" placeholder="" disabled>
                                                    <label for="startDate">Starting Date</label>
                                                </div>
                                            </div>
                                        </div> --}}
                                        {{-- <div class="col-md-2">
                                            <div class="starting_date_box">
                                                <div class="form-floating txt_field custom_dates">
                                                    <input type="date" class="form-control startDate"
                                                        value="{{ $client->start_date ?? '' }}" name="start_date"
                                                        id="startDate" placeholder="">
                                                    <label for="startDate">Starting Date</label>
                                                    <p id="startDateError" style="color: red; display: none;">
                                                        Starting date cannot be today or in the past.
                                                    </p>
                                                </div>
                                            </div>
                                            <br>
                                            <button type="button" style="width: 182px;"
                                                class="btn_global btn_blue submitbtn">
                                                Update Schedule<i class="fa-solid"></i>
                                            </button>
                                        </div> --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="create_clients_wrapper">
                            <form method="post" action="{{ route('client_schedule_save', ['id' => $client->id]) }}" class="form-horizontal" id="clientValidate" enctype="multipart/form-data">
                                @csrf
                                <div class="row create_client_cus_row">
                                    <div class="col-md-12">
                                        @php
                                            $weekNumber = 1;
                                        @endphp
                                        <div class="shadow_box_wrapper services_shadow_wrapper">
                                            <div class="services_week">
                                                <h3>Weeks Of Service</h3>
                                            </div>
                                            <div class="selected-tags" id="selectedTags"></div>
                                        </div>
                                        <div class="monthly_schedule_box">
                                            <div class="custom_checkbox_wrapper assign_week mb-5 input_shadow_wrapper" style="display: flex; flex-wrap: nowrap; overflow-x: auto;">
                                                @foreach ($months as $monthIndex => $month)
                                                    @php
                                                        $skipMonthlyWeek = false;
                                                        if (($client->service_frequency ?? '') === 'monthly' && !empty($client->start_date)) {
                                                            try {
                                                                $clientStartDate = \Carbon\Carbon::createFromFormat('d/m/Y', $client->start_date)->startOfDay();
                                                            } catch (\Exception $e) {
                                                                $clientStartDate = \Carbon\Carbon::parse($client->start_date)->startOfDay();
                                                            }
                                                            $weekStartDate = \Carbon\Carbon::parse($month['start_date'])->startOfDay();
                                                            $weekEndDate = \Carbon\Carbon::parse($month['end_date'])->endOfDay();
                                                            $skipMonthlyWeek = !$clientStartDate->between($weekStartDate, $weekEndDate);
                                                        }
                                                    @endphp
                                                    @if ($skipMonthlyWeek)
                                                        @continue
                                                    @endif
                                                    <div class="monthly_schedule">
                                                        <h4 style="display: none">{{ $month['month'] }}
                                                            {{ $month['year'] }}</h4>
                                                    </div>
                                                    @php
                                                        $isChecked = false;
                                                        if (!empty($client->clientSchedule)) {
                                                            foreach ($client->clientSchedule as $schedule) {
                                                                // Match by week index (works for both original and moved schedules)
                                                                if ($schedule->week === 'week' . $month['week_index']) {
                                                                    $isChecked = true;
                                                                    break;
                                                                }
                                                            }
                                                        }

                                                        $startDate = \Carbon\Carbon::parse($month['start_date']);
                                                        $endDate = \Carbon\Carbon::parse($month['end_date']);

                                                        if ($startDate->month !== $endDate->month) {
                                                            $startDateFormat = $startDate->format('d F');
                                                            $endDateFormat = $endDate->format('d F');
                                                        } else {
                                                            $startDateFormat = $startDate->format('d F');
                                                            $endDateFormat = $endDate->format('d F');
                                                        }
                                                    @endphp
                                                    <div class="custom_radio custom_clients_schedule_radio checkbox-group" style="margin-right: 15px; margin-bottom: 10px; flex: 0 0 auto;">
                                                        <input class="form-check-input week_checkbox checkbox-input" value="{{ $month['start_date'] }}" type="checkbox" data-id="{{ $month['month'] }}_{{ $month['week_index'] }}" data-end="{{ $month['end_date'] }}" data-week="week{{ $month['week_index'] }}" data-month="{{ $month['month'] }}"
                                                            id="week_{{ $month['month'] }}_{{ $month['week_index'] }}" @if ($isChecked) checked @endif>
                                                        <label class="form-check-label " for="week_{{ $month['month'] }}_{{ $month['week_index'] }}">
                                                            Week {{ $weekNumber }}
                                                        </label>
                                                    </div>

                                                    @php
                                                        $weekNumber++;
                                                    @endphp
                                                @endforeach
                                            </div>
                                            <div class="add_frequency_week_note"></div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="custom_justify_between">
                                                <button type="submit" class="btn_global btn_blue submitButton">Update<i class="fa-solid fa-plus"></i></button>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script>
        let clientScheduleData = @json($client->clientSchedule);
        let client = @json($client->service_frequency);
    </script>

    <script>
        $(document).on('click', '.add-minus-work-btn', function() {
            var section = $(this).closest('.custom_month_content');
            var container = section.parent();

            if (container.find('.custom_month_content').length >= 2) {
                container.find('.add-additional-work-btn').prop('disabled', false);
            }

            $(this).closest('.custom_month_content').remove()
        });
        $(document).on('click', '.add-additional-work-btn', function() {
            var lastSection = $(this).closest('.custom_month_content');
            var wrapper = lastSection.closest('.custom_month_contents_wrapper');
            var newSection = lastSection.clone();

            newSection.find('.add-additional-work-btn').hide();
            newSection.find('.add-minus-work-btn').show();

            newSection.find('textarea').val('');
            newSection.find('input[type="checkbox"]:checked').prop('checked', false);

            newSection.find('.week-no').text('12');

            wrapper.append(newSection);

            if (wrapper.find('.custom_month_content').length >= 2) {
                wrapper.find('.add-additional-work-btn').prop('disabled', true);
            }
        });

        $(document).ready(function() {
            function appendFieldsForCheckedCheckbox(checkbox) {
                var This = $(checkbox);
                var id = This.data("id");
                var openWeek = This.data("week");
                var weekLabel = This.next("label").text();
                var monthName = This.data("month");
                var weekNumber = parseInt(weekLabel.match(/\d+/)[0]);
                var startDate = This.val();
                var endDate = This.data("end");
                // Ensure first week/month uses client start_date
                let clientStartDate = "{{ $client->start_date ?? '' }}";
                if (clientStartDate !== '') {
                    let dateParts = clientStartDate.split('/');
                    clientStartDate = dateParts[2] + '-' + dateParts[1].padStart(2, '0') + '-' + dateParts[0].padStart(2, '0');
                }
                // If this is the first week/month, override startDate with schedule's first start_date
                if (weekNumber === 1 && clientScheduleData.length > 0) {
                    // Find the schedule entry with the earliest start_date
                    let minSchedule = clientScheduleData.reduce((min, curr) => {
                        if (!min || new Date(curr.start_date) < new Date(min.start_date)) {
                            return curr;
                        }
                        return min;
                    }, null);
                    if (minSchedule && minSchedule.start_date) {
                        startDate = minSchedule.start_date;
                        let start = new Date(startDate);
                        let end = new Date(start);
                        end.setDate(start.getDate() + 6); // 7 days range
                        let yyyy = end.getFullYear();
                        let mm = String(end.getMonth() + 1).padStart(2, '0');
                        let dd = String(end.getDate()).padStart(2, '0');
                        endDate = `${yyyy}-${mm}-${dd}`;
                    }
                }

                var scheduleData = clientScheduleData.find(schedule => schedule.week === openWeek && schedule
                    .note_week_no === 0 && (schedule.note_date != null || schedule.note != null));
                var clientFrequencyNote = client === 'normalWeek';
                var note = scheduleData ? scheduleData.note : '';
                var note_type = scheduleData ? scheduleData.note_type : '';
                var note_two = scheduleData ? scheduleData.note_two : '';
                var note_date = scheduleData ? scheduleData.note_date : '';
                var priority = scheduleData ? scheduleData.priority : '';

                var baseNotes = [];
                if (scheduleData && scheduleData.note) baseNotes.push(scheduleData.note);

                extraWorkPrice = [];
                extraWorkNote = [];
                extraWorkNoteNo = [];
                extraWorkNoteType = [];
                extraWorkNoteDate = [];
                extraWorkNotePriority = [];
                clientScheduleData.forEach(function(schedule) {
                    if (schedule.week === openWeek) {
                        // Only check week, not month - as schedules can move to different months
                        if (schedule.note_date != null || schedule.note != null) {
                            if (baseNotes.indexOf(schedule.note) === -1 && extraWorkNote.indexOf(schedule.note) === -1) {
                                extraWorkPrice.push(schedule.extra_work_price_id);
                                extraWorkNoteNo.push(schedule.note_week_no);
                                extraWorkNote.push(schedule.note);
                                extraWorkNoteType.push(schedule.note_type);
                                extraWorkNoteDate.push(schedule.note_date);
                                extraWorkNotePriority.push(schedule.priority);
                            }
                        }
                    }
                });

                let week4Data = null;
                const index12 = extraWorkNoteNo.indexOf(1);
                if (index12 !== -1) {
                    week4Data = {
                        note: extraWorkNote[index12],
                        note_type: extraWorkNoteType[index12],
                        note_date: extraWorkNoteDate[index12],
                        price: extraWorkPrice[index12],
                        priority: extraWorkNotePriority[index12]
                    };
                }

                // Get data for week 8 and 12
                let week8Data = null;
                let week12Data = null;
                let week24Data = null;
                let week52Data = null;
                const index8 = extraWorkNoteNo.indexOf(2);
                const index12_twelve = extraWorkNoteNo.indexOf(3);
                const index24 = extraWorkNoteNo.indexOf(4);
                const index52 = extraWorkNoteNo.indexOf(5);

                if (index8 !== -1) {
                    week8Data = {
                        note: extraWorkNote[index8],
                        note_type: extraWorkNoteType[index8],
                        note_date: extraWorkNoteDate[index8],
                        price: extraWorkPrice[index8],
                        priority: extraWorkNotePriority[index8],
                    };
                }

                if (index12_twelve !== -1) {
                    week12Data = {
                        note: extraWorkNote[index12_twelve],
                        note_type: extraWorkNoteType[index12_twelve],
                        note_date: extraWorkNoteDate[index12_twelve],
                        price: extraWorkPrice[index12_twelve],
                        priority: extraWorkNotePriority[index12_twelve],
                    };
                }

                if (index24 !== -1) {
                    week24Data = {
                        note: extraWorkNote[index24],
                        note_type: extraWorkNoteType[index24],
                        note_date: extraWorkNoteDate[index24],
                        price: extraWorkPrice[index24],
                        priority: extraWorkNotePriority[index24],
                    };
                }

                if (index52 !== -1) {
                    week52Data = {
                        note: extraWorkNote[index52],
                        note_type: extraWorkNoteType[index52],
                        note_date: extraWorkNoteDate[index52],
                        price: extraWorkPrice[index52],
                        priority: extraWorkNotePriority[index52],
                    };
                }

                let html = `<div class="dynamic_weeks_of_services_content shadow_box_wrapper" data-week-number="${weekNumber}">
                                <div class="custom_cost_content week_note_tabs week_${id}" id="week_${id}">
                                    <div class="toggle-btn" style="display: flex; justify-content: space-between; align-items: center;">
                                        <h2>Details for ${weekLabel}</h2>
                                    <div>
                                    <button class="btn btn-primary remove_week_btn">${weekLabel} <i class="fa-solid fa-xmark"></i></button>
                                    <button type="button" class="toggle-icon " onclick="togglePriceList(this)">
                                        <i class="fa-solid fa-chevron-down"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="toggle_hidden_content">
                                <div class="accordion note_accordion" id="accordionExample">
                                <!-- Note 1 Accordion Item -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne-${id}" aria-expanded="true" aria-controls="collapseOne-${id}">
                                            Note 1
                                        </button>
                                        <span class="note-reset-btn" onclick="resetNoteFields(this)" title="Reset Note 1 fields">
                                            <i class="fa-solid fa-rotate-left"></i> Reset
                                        </span>
                                    </h2>
                                    <div id="collapseOne-${id}" class="accordion-collapse collapse show">
                                        <div class="accordion-body">
                                            <div class="week_one_note_detail">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="price_toggle_justify">
                                                            <div class="priority_checkbox">
                                                                <input class="form-check-input" type="checkbox" value="0" name="month[${monthName}][week${id.split('_')[1]}][priority][0]"  ${priority == 1 ? 'checked' : ''} onchange="this.value = this.checked ? 1 : 0;">
                                                                <label for="priority">Coming Soon</label>
                                                            </div>
                                                            ${clientFrequencyNote ? `
                                                                    <div class="form-group d-flex align-items-center">
                                                                        <div class="d-flex align-items-center">
                                                                            <label class="date-note-label">Start Note 1 :</label>
                                                                            <input type="date" class="form-control note-date-input" value="${note_date}" name="month[${monthName}][week${id.split('_')[1]}][note_start_date]" style="margin-right: 20px">
                                                                        </div>
                                                                        <select class="form-select note-type-select" name="month[${monthName}][week${id.split('_')[1]}][note_type]" id="price-select-1-${id}">
                                                                            <option value="" disabled selected>Repeat Every </option>
                                                                            <option ${note_type === "weekly" ? "selected" : ""} value="weekly">Weekly</option>
                                                                            <option ${note_type === "4_weeks" ? "selected" : ""} value="4_weeks">4 Weeks</option>
                                                                            <option ${note_type === "8_weeks" ? "selected" : ""} value="8_weeks">8 Weeks</option>
                                                                            <option ${note_type === "12_weeks" ? "selected" : ""} value="12_weeks">12 Weeks</option>
                                                                            <option ${note_type === "24_weeks" ? "selected" : ""} value="24_weeks">24 Weeks</option>
                                                                            <option ${note_type === "52_weeks" ? "selected" : ""} value="52_weeks">52 Weeks</option>
                                                                        </select>
                                                                    </div>` : `
                                                                        <div class="d-flex align-items-center">
                                                                        <label>
                                                                            ${ !clientFrequencyNote ? `
                                                                            Start Note :
                                                                            ${formatDateRange(startDate, endDate)}
                                                                        ` : ''}
                                                                        </label>
                                                                        </div>
                                                                    `}
                                                        </div>
                                                        <div class="price_list_wrapper appended_price_list">
                                                            <div class="row">
                                                                ${generatePriceList(id, monthName, scheduleData)}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="custom_week_notes">
                                                            <div class="txt_field">
                                                                <label>Note:</label>
                                                                <textarea name="month[${monthName}][week${id.split('_')[1]}][note]" placeholder="Enter Note..." class="form-control note_textarea" rows="4">${note ? note : ''}</textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Note 2 Accordion Item -->
                                ${clientFrequencyNote ? `
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo-${id}" aria-expanded="false" aria-controls="collapseTwo-${id}">
                                                Note 2
                                            </button>
                                            <span class="note-reset-btn" onclick="resetNoteFields(this)" title="Reset Note 2 fields">
                                                <i class="fa-solid fa-rotate-left"></i> Reset
                                            </span>
                                        </h2>
                                        <div id="collapseTwo-${id}" class="accordion-collapse collapse">
                                            <div class="accordion-body">
                                                <div class="week_four_note_detail">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="price_toggle_justify">
                                                                <div class="priority_checkbox">
                                                                    <input class="form-check-input" type="checkbox" value="0" name="month[${monthName}][week${id.split('_')[1]}][priority][1]" ${week4Data?.priority == 1 ? 'checked' : ''} onchange="this.value = this.checked ? 1 : 0;">
                                                                    <label for="priority">Coming Soon</label>
                                                                </div>
                                                                <div class="form-group d-flex align-items-center">
                                                                        <div class="d-flex align-items-center">
                                                                        <label class="date-note-label">Start Note 2 :</label>
                                                                        <input type="date" class="form-control note-date-input" value="${week4Data?.note_date}" name="month[${monthName}][week${id.split('_')[1]}][extra_note_start_date][]" style="margin-right: 20px">
                                                                    </div>
                                                                    <select class="form-select note-type-select" name="month[${monthName}][week${id.split('_')[1]}][extra_note_type][]" id="price-select-2-${id}">
                                                                        <option value="" disabled selected>Repeat Every </option>
                                                                        <option ${week4Data?.note_type === "weekly" ? "selected" : ""} value="weekly">Weekly</option>
                                                                        <option ${week4Data?.note_type === "4_weeks" ? "selected" : ""} value="4_weeks">4 Weeks</option>
                                                                        <option ${week4Data?.note_type === "8_weeks" ? "selected" : ""} value="8_weeks">8 Weeks</option>
                                                                        <option ${week4Data?.note_type === "12_weeks" ? "selected" : ""} value="12_weeks">12 Weeks</option>
                                                                        <option ${week4Data?.note_type === "24_weeks" ? "selected" : ""} value="24_weeks">24 Weeks</option>
                                                                        <option ${week4Data?.note_type === "52_weeks" ? "selected" : ""} value="52_weeks">52 Weeks</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="price_list_wrapper appended_price_list">
                                                                <div class="row">
                                                                    ${generateExtraPriceList(id, monthName, scheduleData, week4Data?.price, 0)}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="custom_week_notes">
                                                                <div class="txt_field">
                                                                    <label>Note:</label>
                                                                    <textarea name="month[${monthName}][week${id.split('_')[1]}][note_two]" placeholder="Enter Note..." class="form-control" rows="4">${note_two ? note_two : (week4Data ? week4Data.note : '')}</textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>` : ''}

                                ${clientFrequencyNote ? `
                                    <!-- Note 3 Accordion Item -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree-${id}" aria-expanded="false" aria-controls="collapseThree-${id}">
                                                Note 3
                                            </button>
                                            <span class="note-reset-btn" onclick="resetNoteFields(this)" title="Reset Note 3 fields">
                                                <i class="fa-solid fa-rotate-left"></i> Reset
                                            </span>
                                        </h2>
                                        <div id="collapseThree-${id}" class="accordion-collapse collapse">
                                            <div class="accordion-body">
                                                <div class="week_eight_note_detail">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="price_toggle_justify">
                                                                <div class="priority_checkbox">
                                                                    <input class="form-check-input" type="checkbox" value="0" name="month[${monthName}][week${id.split('_')[1]}][priority][2]" ${week8Data?.priority == 1 ? 'checked' : ''} onchange="this.value = this.checked ? 1 : 0;">
                                                                    <label for="priority">Coming Soon</label>
                                                                </div>
                                                                <div class="form-group d-flex align-items-center">
                                                                        <div class="d-flex align-items-center">
                                                                        <label class="date-note-label">Start Note 3 :</label>
                                                                        <input type="date" class="form-control note-date-input" value="${week8Data?.note_date}" name="month[${monthName}][week${id.split('_')[1]}][extra_note_start_date][]" style="margin-right: 20px">
                                                                        </div>
                                                                    <select class="form-select note-type-select" name="month[${monthName}][week${id.split('_')[1]}][extra_note_type][]" id="price-select-3-${id}">
                                                                        <option value="" disabled selected>Repeat Every </option>
                                                                        <option ${week8Data?.note_type === "weekly" ? "selected" : ""} value="weekly">Weekly</option>
                                                                        <option ${week8Data?.note_type === "4_weeks" ? "selected" : ""} value="4_weeks">4 Weeks</option>
                                                                        <option ${week8Data?.note_type === "8_weeks" ? "selected" : ""} value="8_weeks">8 Weeks</option>
                                                                        <option ${week8Data?.note_type === "12_weeks" ? "selected" : ""} value="12_weeks">12 Weeks</option>
                                                                        <option ${week8Data?.note_type === "24_weeks" ? "selected" : ""} value="24_weeks">24 Weeks</option>
                                                                        <option ${week8Data?.note_type === "52_weeks" ? "selected" : ""} value="52_weeks">52 Weeks</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="price_list_wrapper appended_price_list">
                                                                <div class="row">
                                        ${generateExtraPriceList(id, monthName, scheduleData, week8Data?.price, 1)}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="custom_week_notes">
                                                                <div class="txt_field">
                                                                    <label>Note:</label>
                                                                    <textarea name="month[${monthName}][week${id.split('_')[1]}][additional_note][]" placeholder="Enter Note..." class="form-control" rows="4">${week8Data ? week8Data.note : ''}</textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Note 4 Accordion Item -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour-${id}" aria-expanded="false" aria-controls="collapseFour-${id}">
                                                Note 4
                                            </button>
                                            <span class="note-reset-btn" onclick="resetNoteFields(this)" title="Reset Note 4 fields">
                                                <i class="fa-solid fa-rotate-left"></i> Reset
                                            </span>
                                        </h2>
                                        <div id="collapseFour-${id}" class="accordion-collapse collapse">
                                            <div class="accordion-body">
                                                <div class="week_twelve_note_detail">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="price_toggle_justify">
                                                                <div class="priority_checkbox">
                                                                    <input class="form-check-input" type="checkbox" value="0" name="month[${monthName}][week${id.split('_')[1]}][priority][3]" ${week12Data?.priority == 1 ? 'checked' : ''} onchange="this.value = this.checked ? 1 : 0;">
                                                                    <label for="priority">Coming Soon</label>
                                                                </div>
                                                                <div class="form-group d-flex align-items-center">
                                                                        <div class="d-flex align-items-center">
                                                                        <label class="date-note-label">Start Note 4 :</label>
                                                                        <input type="date" class="form-control note-date-input" value="${week12Data?.note_date}" name="month[${monthName}][week${id.split('_')[1]}][extra_note_start_date][]" style="margin-right: 20px">
                                                                    </div>
                                                                    <select class="form-select note-type-select" name="month[${monthName}][week${id.split('_')[1]}][extra_note_type][]" id="price-select-4-${id}">
                                                                        <option value="" disabled selected>Repeat Every </option>
                                                                        <option ${week12Data?.note_type === "weekly" ? "selected" : ""} value="weekly">Weekly</option>
                                                                        <option ${week12Data?.note_type === "4_weeks" ? "selected" : ""} value="4_weeks">4 Weeks</option>
                                                                        <option ${week12Data?.note_type === "8_weeks" ? "selected" : ""} value="8_weeks">8 Weeks</option>
                                                                        <option ${week12Data?.note_type === "12_weeks" ? "selected" : ""} value="12_weeks">12 Weeks</option>
                                                                        <option ${week12Data?.note_type === "24_weeks" ? "selected" : ""} value="24_weeks">24 Weeks</option>
                                                                        <option ${week12Data?.note_type === "52_weeks" ? "selected" : ""} value="52_weeks">52 Weeks</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="price_list_wrapper appended_price_list">
                                                                <div class="row">
                                        ${generateExtraPriceList(id, monthName, scheduleData, week12Data?.price, 2)}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="custom_week_notes">
                                                                <div class="txt_field">
                                                                    <label>Note:</label>
                                                                    <textarea name="month[${monthName}][week${id.split('_')[1]}][additional_note][]" placeholder="Enter Note..." class="form-control" rows="4">${week12Data ? week12Data.note : ''}</textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Note 5 Accordion Item -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive-${id}" aria-expanded="false" aria-controls="collapseFive-${id}">
                                                Note 5
                                            </button>
                                            <span class="note-reset-btn" onclick="resetNoteFields(this)" title="Reset Note 5 fields">
                                                <i class="fa-solid fa-rotate-left"></i> Reset
                                            </span>
                                        </h2>
                                        <div id="collapseFive-${id}" class="accordion-collapse collapse">
                                            <div class="accordion-body">
                                                <div class="week_twelve_note_detail">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="price_toggle_justify">
                                                                <div class="priority_checkbox">
                                                                    <input class="form-check-input" type="checkbox" value="0" name="month[${monthName}][week${id.split('_')[1]}][priority][4]" ${week24Data?.priority == 1 ? 'checked' : ''} onchange="this.value = this.checked ? 1 : 0;">
                                                                    <label for="priority">Coming Soon</label>
                                                                </div>
                                                                <div class="form-group d-flex align-items-center">
                                                                        <div class="d-flex align-items-center">
                                                                        <label class="date-note-label">Start Note 5 :</label>
                                                                        <input type="date" class="form-control note-date-input" value="${week24Data?.note_date}" name="month[${monthName}][week${id.split('_')[1]}][extra_note_start_date][]" style="margin-right: 20px">
                                                                    </div>
                                                                    <select class="form-select note-type-select" name="month[${monthName}][week${id.split('_')[1]}][extra_note_type][]" id="price-select-5-${id}">
                                                                        <option value="" disabled selected>Repeat Every </option>
                                                                        <option ${week24Data?.note_type === "weekly" ? "selected" : ""} value="weekly">Weekly</option>
                                                                        <option ${week24Data?.note_type === "4_weeks" ? "selected" : ""} value="4_weeks">4 Weeks</option>
                                                                        <option ${week24Data?.note_type === "8_weeks" ? "selected" : ""} value="8_weeks">8 Weeks</option>
                                                                        <option ${week24Data?.note_type === "12_weeks" ? "selected" : ""} value="12_weeks">12 Weeks</option>
                                                                        <option ${week24Data?.note_type === "24_weeks" ? "selected" : ""} value="24_weeks">24 Weeks</option>
                                                                        <option ${week24Data?.note_type === "52_weeks" ? "selected" : ""} value="52_weeks">52 Weeks</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="price_list_wrapper appended_price_list">
                                                                <div class="row">
                                        ${generateExtraPriceList(id, monthName, scheduleData, week24Data?.price, 3)}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="custom_week_notes">
                                                                <div class="txt_field">
                                                                    <label>Note:</label>
                                                                    <textarea name="month[${monthName}][week${id.split('_')[1]}][additional_note][]" placeholder="Enter Note..." class="form-control" rows="4">${week24Data ? week24Data.note : ''}</textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                        <!-- Note 6 Accordion Item -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix-${id}" aria-expanded="false" aria-controls="collapseSix-${id}">
                                                Note 6
                                            </button>
                                            <span class="note-reset-btn" onclick="resetNoteFields(this)" title="Reset Note 6 fields">
                                                <i class="fa-solid fa-rotate-left"></i> Reset
                                            </span>
                                        </h2>
                                        <div id="collapseSix-${id}" class="accordion-collapse collapse">
                                            <div class="accordion-body">
                                                <div class="week_twelve_note_detail">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="price_toggle_justify">
                                                                <div class="priority_checkbox">
                                                                    <input class="form-check-input" type="checkbox" value="0" name="month[${monthName}][week${id.split('_')[1]}][priority][5]" ${week52Data?.priority == 1 ? 'checked' : ''} onchange="this.value = this.checked ? 1 : 0;">
                                                                    <label for="priority">Coming Soon</label>
                                                                </div>
                                                                <div class="form-group d-flex align-items-center">
                                                                        <div class="d-flex align-items-center">
                                                                        <label class="date-note-label">Start Note 6 :</label>
                                                                        <input type="date" class="form-control note-date-input" value="${week52Data?.note_date}" name="month[${monthName}][week${id.split('_')[1]}][extra_note_start_date][]" style="margin-right: 20px">
                                                                    </div>
                                                                    <select class="form-select note-type-select" name="month[${monthName}][week${id.split('_')[1]}][extra_note_type][]" id="price-select-6-${id}">
                                                                        <option value="" disabled selected>Repeat Every </option>
                                                                        <option ${week52Data?.note_type === "weekly" ? "selected" : ""} value="weekly">Weekly</option>
                                                                        <option ${week52Data?.note_type === "4_weeks" ? "selected" : ""} value="4_weeks">4 Weeks</option>
                                                                        <option ${week52Data?.note_type === "8_weeks" ? "selected" : ""} value="8_weeks">8 Weeks</option>
                                                                        <option ${week52Data?.note_type === "12_weeks" ? "selected" : ""} value="12_weeks">12 Weeks</option>
                                                                        <option ${week52Data?.note_type === "24_weeks" ? "selected" : ""} value="24_weeks">24 Weeks</option>
                                                                        <option ${week52Data?.note_type === "52_weeks" ? "selected" : ""} value="52_weeks">52 Weeks</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="price_list_wrapper appended_price_list">
                                                                <div class="row">
                                        ${generateExtraPriceList(id, monthName, scheduleData, week52Data?.price, 4)}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="custom_week_notes">
                                                                <div class="txt_field">
                                                                    <label>Note:</label>
                                                                    <textarea name="month[${monthName}][week${id.split('_')[1]}][additional_note][]" placeholder="Enter Note..." class="form-control" rows="4">${week52Data ? week52Data.note : ''}</textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    ` : ''}
                            </div>

                                        <div class="row week_note_hidden">
                                            <input type="hidden" name="month[${monthName}][week${id.split('_')[1]}][start_date]" value="${startDate}">
                                            <input type="hidden" name="month[${monthName}][week${id.split('_')[1]}][end_date]" value="${endDate}">
                                        </div>
                                    </div>
                            </div>
                        </div>
                        `;

                var container = This.closest(".monthly_schedule_box").find('.add_frequency_week_note');
                var inserted = false;

                container.children('.dynamic_weeks_of_services_content').each(function() {
                    var existingWeekNumber = parseInt($(this).attr('data-week-number'));
                    if (weekNumber < existingWeekNumber) {
                        $(this).before(html);
                        inserted = true;
                        return false; // Break the loop
                    }
                });

                if (!inserted) {
                    container.append(html);
                }

                // Initialize Sortable after price list is rendered
                container.find('.price_list_wrapper .row').each(function() {
                    initializeSortable(this);
                });

                $('.note-date-input').each(function() {
                    $(this).attr('min', clientStartDate);
                });

            }

            // Remove week button handler
            $(document).on('click', '.remove_week_btn', function() {
                const weekElement = $(this).closest('.dynamic_weeks_of_services_content');
                const weekId = $(this).closest('.custom_cost_content').attr('id').replace('week_', '');

                if (weekElement.length) {
                    $(`#week_${weekId}`).prop('checked', false);
                    $(`.tag .tag-close[data-week="week_${weekId}"]`).closest('.tag').remove();
                    weekElement.remove();
                    updateTags();
                    updateSelectAll();
                }
            });

            function generateExtraPriceList(id, monthName, scheduleData, selectedPrice = null, noteIndex = 0) {
                let prices = @json($client->clientPrice);

                // Sort prices by position
                prices.sort((a, b) => (a.position || 0) - (b.position || 0));

                let schedulePrices = scheduleData && scheduleData.client_schedule_price ? scheduleData.client_schedule_price : [];
                let priceHtml = "";

                let selectedPriceIds = [];
                if (selectedPrice) {
                    if (typeof selectedPrice === 'string' && selectedPrice.startsWith('[')) {
                        try {
                            selectedPriceIds = JSON.parse(selectedPrice);
                        } catch (e) {
                            selectedPriceIds = [selectedPrice];
                        }
                    } else {
                        selectedPriceIds = [selectedPrice];
                    }
                }

                prices.forEach(price => {
                    let isChecked = selectedPriceIds.includes(price.id);

                    priceHtml += `
            <div class="col-md-3 sortable-item" data-price-id="${price.id}" data-position="${price.position || 0}">
              <div class="price_list">
                  <div class="price_list_box">
                    <div class="table_checkbox">
                          <input class="form-check-input" type="checkbox" value="${price.id}" name="month[${monthName}][week${id.split('_')[1]}][extra_prices][${noteIndex}][]" data-name="${price.name}" data-value="${price.value}" ${isChecked ? 'checked' : ''}>
                          <label>${price.name ?? ''}</label>
                     </div><span>$${price.value ?? ''}</span></div>
            </div>
            </div>
        `;
                });

                if (selectedPriceIds.length === 0) {
                    priceHtml += `
            <input class="form-check-input" type="hidden" value="0" name="month[${monthName}][week${id.split('_')[1]}][extra_prices][${noteIndex}][]" >
        `
                }

                return priceHtml;
            }

            function generatePriceList(id, monthName, scheduleData) {
                let prices = @json($client->clientPrice);

                // Sort prices by position
                prices.sort((a, b) => (a.position || 0) - (b.position || 0));

                let schedulePrices = scheduleData && scheduleData.client_schedule_price ? scheduleData.client_schedule_price : [];
                let priceHtml = "";

                prices.forEach(price => {
                    let isChecked = schedulePrices.some(schedule => schedule.price_id == price.id);

                    priceHtml += `
           <div class="col-md-3 sortable-item" data-price-id="${price.id}" data-position="${price.position || 0}">
             <div class="price_list">
               <div class="price_list_box">
                 <div class="table_checkbox">
                    <input class="form-check-input" type="checkbox" value="${price.id}" name="month[${monthName}][week${id.split('_')[1]}][prices][]" data-name="${price.name}" data-value="${price.value}" ${isChecked ? 'checked' : ''}>
                    <label>${price.name ?? ''}</label>
                 </div>
                 <span>$${price.value ?? ''}</span>
             </div>
            </div>
           </div>
        `;
                });

                return priceHtml;
            }

            $(document).on('change', 'input[type="checkbox"][name^="month"][name$="[extra_prices][]"]', function() {
                let selectedPrice = $(this).val();
                let priceInput = $(this).closest('.price_list_wrapper').find('input[type="hidden"]');

                if (selectedPrice !== "0") {
                    priceInput.remove('');
                }
            });

            function formatDateRange(startDate, endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);

                const options = {
                    year: 'numeric',
                    month: 'short',
                    day: '2-digit'
                };

                const startFormatted = start.toLocaleDateString('en-US', options).replace(',', '');
                const endFormatted = end.toLocaleDateString('en-US', options).replace(',', '');

                const startMonth = start.toLocaleDateString('en-US', {
                    month: 'short'
                });
                const endMonth = end.toLocaleDateString('en-US', {
                    month: 'short'
                });

                return `${startMonth} ${startFormatted.slice(4)} thru ${endMonth} ${endFormatted.slice(4)}`;
            }


            $(".assign_week input[type='checkbox']:checked").each(function() {
                appendFieldsForCheckedCheckbox(this);
            });

            $(document).on("change", ".assign_week input[type='checkbox']", function() {
                var This = $(this);
                if (This.is(":checked")) {
                    appendFieldsForCheckedCheckbox(This);
                } else {
                    var id = This.data("id");
                    $(".week_note_" + id).remove();
                }
            });
        });


        function initializeSortable(containerOrSelector) {
            let container = null;
            if (typeof containerOrSelector === 'string') {
                container = document.querySelector(containerOrSelector);
            } else {
                container = containerOrSelector;
            }
            if (container) {
                new Sortable(container, {
                    animation: 150,
                    handle: '.price_list', // Drag handle
                    ghostClass: 'sortable-ghost',
                    onEnd: function(evt) {
                        // Save new order to server
                        saveNewOrder(container);
                    }
                });
            }
        }

        // Save updated positions to server via AJAX
        function saveNewOrder(container) {
            let newOrder = [];
            container.querySelectorAll('.sortable-item').forEach((item, index) => {
                newOrder.push({
                    id: item.getAttribute('data-price-id'),
                    position: index + 1
                });
            });

            // AJAX call to update positions using jQuery
            $.ajax({
                url: '{{ route('update.price.positions') }}',
                type: 'POST',
                data: {
                    positions: newOrder,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log('Positions updated successfully');
                },
                error: function(xhr, status, error) {
                    console.error('Error updating positions:', error);
                }
            });
        }

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

                    if (selectedValue === "eightWeek" || selectedValue === "quarterly") {
                        $(".cycle_frequency_wrapper .custom_dates input[name='start_date']").attr("disabled", true)
                            .val("").closest('.form-floating').hide();
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
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        $(document).ready(function() {
            let startDate = "{{ $client->start_date ?? 'today' }}";
            if (startDate !== 'today' && startDate !== '') {
                let dateParts = startDate.split('/');
                startDate = dateParts[1] + '-' + dateParts[0] + '-' + dateParts[2];
            }

            let secondStartDate = "{{ $client->second_start_date ?? '' }}";
            if (secondStartDate !== '' && secondStartDate !== 'today') {
                let dateParts = secondStartDate.split('/');
                secondStartDate = dateParts[1] + '-' + dateParts[0] + '-' + dateParts[2];
            }

            // Initialize second date picker first (if element exists)
            let startDateSecondPicker = null;
            if (document.getElementById('startDateSecond')) {
                startDateSecondPicker = flatpickr("#startDateSecond", {
                    dateFormat: "m-d-Y",
                    // minDate: secondStartDate || '',
                    defaultDate: secondStartDate || '',
                });
            }

            // Initialize first date picker (Admin)
            if (document.getElementById('startDate')) {
                let startDatePicker = flatpickr("#startDate", {
                    dateFormat: "m-d-Y",
                    // minDate: startDate || 'today',
                    defaultDate: startDate || '',
                    onChange: function(selectedDates) {
                        if (selectedDates.length > 0 && startDateSecondPicker) {
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
            }

            // Initialize first date picker (Staff)
            if (document.getElementById('startDateStaff')) {
                let startDatePickerStaff = flatpickr("#startDateStaff", {
                    dateFormat: "m-d-Y",
                    defaultDate: startDate || '',
                });
            }
        });
    </script>

    <script>
        $(document).ready(function() {
            // Get start_date from either admin (#startDate) or staff (#startDateStaff) input
            let startDateVal = $("#startDate").val() || $("#startDateStaff").val() ||
                "{{ $client->start_date ?? '' }}";

            let formData = {
                id: "{{ $client->id }}",
                service_frequency: $("input[name='service_frequency']:checked").val() ||
                    "{{ $client->service_frequency ?? '' }}",
                start_date: startDateVal,
                start_date_second: $("#startDateSecond").val() || "{{ $client->second_start_date ?? '' }}"
            };

            $("input[name='service_frequency']").on('change', function() {
                formData.service_frequency = $("input[name='service_frequency']:checked").val();

                if (formData.service_frequency !== 'biMonthly') {
                    formData.start_date_second = null;
                    $("#startDateSecond").val("");
                } else if (formData.service_frequency === 'quarterly' || formData.service_frequency ===
                    'eightWeek') {
                    formData.start_date = null;
                    $(".startDate").val("");
                }

                checkError();
            });

            // Listen for changes on both admin and staff date inputs
            $("#startDate, #startDateStaff").on('change', function() {
                formData.start_date = $(this).val();
                checkError();
            });

            $("#startDateSecond").on('change', function() {
                formData.start_date_second = $(this).val();
                checkError();
            });

            $(document).on('click', '.submitbtn', function(e) {
                e.preventDefault();
                e.stopPropagation();

                console.log('Update Schedule clicked');
                console.log('formData:', formData);

                const today = new Date().toISOString().split('T')[0];

                if (!formData.service_frequency) {
                    swal.fire("Please select a service frequency.");
                    return;
                }

                if (!formData.start_date) {
                    swal.fire("Please select a starting date.");
                    return;
                }

                if (formData.service_frequency == 'biMonthly' && formData.start_date && !formData
                    .start_date_second) {
                    $('#endDateError').show().text(
                        "Second start date is required when 'Bi-Monthly' cycle is selected.");
                    return;
                } else {
                    $('#endDateError').hide();
                }

                $.ajax({
                    url: "{{ route('update_client_schedule') }}",
                    type: "POST",
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: formData.id,
                        service_frequency: formData.service_frequency,
                        start_date: formData.start_date,
                        start_date_second: formData.start_date_second
                    },
                    success: function(response) {
                        swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Schedule updated successfully.',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(function() {
                            location.reload();
                        });
                    },
                    error: function(xhr, status, error) {
                        swal.fire("An error occurred while updating the schedule.");
                    }
                });

            });

            function checkError() {
                if (formData.service_frequency == 'biMonthly' && formData.start_date && !formData
                    .start_date_second) {
                    $('#endDateError').show().text(
                        "Second start date is required when 'Bi-Monthly' cycle is selected.");
                } else {
                    $('#endDateError').hide();
                }
            }
        });
    </script>

    <script>
        $(document).ready(function() {
            var serviceFrequency = "{{ $client->service_frequency }}";

            if (serviceFrequency === 'quarterly' || serviceFrequency === 'monthly' || serviceFrequency === 'eightWeek') {
                $('.week_checkbox').each(function() {
                    if ($(this).prop('checked')) {
                        $('.week_checkbox').prop('disabled', true);
                        $(this).prop('disabled', false);
                    }
                });

                $('.week_checkbox').on('change', function() {
                    var selectedWeekId = $(this).attr('id');
                    if ($(this).prop('checked')) {
                        $('.week_checkbox').prop('disabled', true);
                        $('#' + selectedWeekId).prop('disabled', false);
                    } else {
                        $('.week_checkbox').prop('disabled', false);
                    }
                });

            }
        });
    </script>

    {{--    New Structure scritp --}}
    <script>
        $(document).ready(function() {
            function updateTags() {
                const $tags = $('#selectedTags');
                $tags.empty();

                $('.checkbox-input:checked').each(function() {
                    const week = $(this).attr('id');
                    const weekLabel = $(this).next("label").text();
                    $tags.append(
                        `<div class="tag">${weekLabel}<span class="tag-close" data-week="${week}">×</span></div>`
                    );
                });

                $('.checkbox-input:not(:checked)').each(function() {
                    const week = $(this).attr('id');
                    const weekElement = $('.add_frequency_week_note').find(`#${week}`).parent();
                    if (weekElement.length) {
                        weekElement.remove();
                    }
                });
            }

            function updateSelectAll() {
                const $checkboxes = $('.checkbox-input');
                const total = $checkboxes.length;
                const checked = $checkboxes.filter(':checked').length;

                $('#selectAll').prop('checked', checked === total).prop('indeterminate', checked > 0 && checked <
                    total);
            }

            $(document).on('click', '.remove_week_btn', function() {
                const weekElement = $(this).closest('.dynamic_weeks_of_services_content');
                const weekId = $(this).closest('.custom_cost_content').attr('id').replace('week_', '');

                if (weekElement.length) {
                    $(`#week_${weekId}`).prop('checked', false);

                    $(`.tag .tag-close[data-week="week_${weekId}"]`).closest('.tag').remove();

                    weekElement.remove();

                    updateTags();
                    updateSelectAll();
                }
            });
            // Handle checkbox changes
            $('.checkbox-input').change(function() {
                updateTags();
                updateSelectAll();
            });

            // Handle select all
            $('#selectAll').change(function() {
                $('.checkbox-input').prop('checked', $(this).is(':checked'));
                updateTags();
            });

            // Handle tag close buttons
            $(document).on('click', '.tag-close', function() {
                const week = $(this).data('week');
                $(`#${week}`).prop('checked', false);
                console.log("week", week);
                const weekElement = $('.add_frequency_week_note').find(`#${week}`).parent();
                if (weekElement.length) {
                    weekElement.remove(); // Remove the corresponding week content
                }

                updateTags();
                updateSelectAll();
            });

            // Initialize
            updateTags();
            updateSelectAll();
        });

        $(".submitButton").on("click", function(e) {
            e.preventDefault();
            let isValid = true;
            let errorMessage = "";
            let firstInvalidElement = null;

            // Validate at least one week selection
            if ($(".assign_week input[type='checkbox']:checked").length === 0) {
                isValid = false;
                errorMessage = "Please select at least one week.";
                firstInvalidElement = $(".assign_week input[type='checkbox']").first();
            }

            // Check for duplicate notes within each week (not across weeks)
            $(".dynamic_weeks_of_services_content").each(function() {
                let weekNotes = [];
                let duplicateFound = false;

                let note1 = $(this).find("textarea[name*='[note]']").val();
                let note2 = $(this).find("textarea[name*='[note_two]']").val();

                note1 = note1 ? note1.trim() : "";
                note2 = note2 ? note2.trim() : "";

                if (note1 !== "") {
                    weekNotes.push(note1);
                }

                if (note2 !== "") {
                    if (weekNotes.includes(note2)) {
                        isValid = false;
                        errorMessage =
                            "Notes cannot be the same within the same week. Please use unique notes.";
                        firstInvalidElement = firstInvalidElement || $(this).find(
                            "textarea[name*='[note_two]']");
                        duplicateFound = true;
                    } else {
                        weekNotes.push(note2);
                    }
                }

                // Check additional_note fields within this week
                if (!duplicateFound) {
                    $(this).find("textarea[name*='[additional_note]']").each(function() {
                        let additionalNote = $(this).val();
                        additionalNote = additionalNote ? additionalNote.trim() : "";

                        if (additionalNote !== "") {
                            if (weekNotes.includes(additionalNote)) {
                                isValid = false;
                                errorMessage =
                                    "Notes cannot be the same within the same week. Please use unique notes.";
                                firstInvalidElement = firstInvalidElement || $(this);
                                duplicateFound = true;
                                return false; // Break out of each loop
                            } else {
                                weekNotes.push(additionalNote);
                            }
                        }
                    });
                }

                // Validate at least one Price checkbox
                if ($(this).find(".price_list_wrapper input[type='checkbox']:checked").length === 0) {
                    isValid = false;
                    errorMessage = "Please check at least one selected week's Price.";
                    firstInvalidElement = firstInvalidElement || $(this).find(
                        ".price_list_wrapper input[type='checkbox']").first();
                }
                if ($(this).find(".note-date-input").val() == "") {
                    isValid = false;
                    errorMessage = "Please select a start date";
                    firstInvalidElement = firstInvalidElement || $(this).find(
                        ".note-date-input").first();
                }
                if ($(this).find(".note-type-select").val() == "") {
                    isValid = false;
                    errorMessage = "Please select a repeat type";
                    firstInvalidElement = firstInvalidElement || $(this).find(
                        ".note-type-select").first();
                }
                if ($(this).find(".note-date-input").val() == "" && $(this).find(".note-type-select")
                    .val() == "") {
                    isValid = false;
                    errorMessage = "Please select a start date and repeat type for all notes.";
                }
            });

            // Require date/type only for notes that have content (normalWeek)
            const selectedServiceFrequency = $('input[name="service_frequency"]:checked').val() ||
                "{{ $client->service_frequency }}";
            if (selectedServiceFrequency === 'normalWeek') {
                $(".dynamic_weeks_of_services_content").each(function() {
                    if (!isValid) return;

                    const $week = $(this);
                    const weekNumber = $week.attr('data-week-number') || '';

                    const dateInputs = $week.find(".note-date-input"); // order: Note1, Note2, Note3, Note4
                    const typeSelects = $week.find(
                        ".note-type-select"); // order: Note1, Note2, Note3, Note4

                    const slots = [];
                    const note1 = $week.find("textarea[name$='[note]']").first();
                    if (note1.length) slots.push({
                        textarea: note1,
                        date: dateInputs.eq(0),
                        type: typeSelects.eq(0),
                        label: 1
                    });

                    const note2 = $week.find("textarea[name$='[note_two]']").first();
                    if (note2.length) slots.push({
                        textarea: note2,
                        date: dateInputs.eq(1),
                        type: typeSelects.eq(1),
                        label: 2
                    });

                    const additionalNotes = $week.find("textarea[name*='[additional_note]']");
                    additionalNotes.each(function(i) {
                        slots.push({
                            textarea: $(this),
                            date: dateInputs.eq(2 + i),
                            type: typeSelects.eq(2 + i),
                            label: 3 + i
                        });
                    });

                    for (let i = 0; i < slots.length; i++) {
                        const txt = (slots[i].textarea.val() || "").trim();
                        if (txt !== "") {
                            const d = (slots[i].date.val() || "").trim();
                            if (!d) {
                                isValid = false;
                                errorMessage =
                                    `Please select a Note ${slots[i].label} start date for Week ${weekNumber}.`;
                                firstInvalidElement = firstInvalidElement || slots[i].date;
                                break;
                            }
                            const ty = (slots[i].type.val() || "").trim();
                            if (!ty) {
                                isValid = false;
                                errorMessage =
                                    `Please select a Note ${slots[i].label} repeat type for Week ${weekNumber}.`;
                                firstInvalidElement = firstInvalidElement || slots[i].type;
                                break;
                            }
                        }
                    }
                });
            }

            if (!isValid) {
                Swal.fire({
                    icon: "warning",
                    title: "Warning",
                    text: errorMessage,
                }).then(() => {
                    if (firstInvalidElement) {
                        $('html, body').animate({
                            scrollTop: firstInvalidElement.offset().top - 100
                        }, 500);
                        firstInvalidElement.focus();
                    }
                });
            } else {
                $("#clientValidate").submit();
            }
        });
    </script>

    <script>
        $(document).on('click', '.toggle-btn, toggle-icon', function() {
            var $container = $(this).closest('.dynamic_weeks_of_services_content');
            $container.find('.toggle_hidden_content').toggle();
            $container.find('.add-additional-work-btn').toggle();
            var $btn = $(this);
            var isVisible = $container.find('.toggle_hidden_content').is(':visible');
            if (isVisible) {
                $btn.find('i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
            } else {
                $btn.find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
            }
        });

        // Handle "Update & Stay" button click
        $(document).on('click', '.submitButtonStay', function(e) {
            e.preventDefault();
            e.stopPropagation();

            // Set the stay_on_page flag to 1
            $('#stayOnPage').val('1');

            // Trigger the submitButton handler
            $('.submitButton').first().click();
        });
    </script>

    <script>
        // Reset all fields within a specific Note accordion section
        function resetNoteFields(btn) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This will clear all notes and selections in this section.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#00ADEE',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, reset it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    var $accordionItem = $(btn).closest('.accordion-item');
                    var $body = $accordionItem.find('.accordion-body');

                    // Clear all textareas
                    $body.find('textarea').val('');

                    // Clear date inputs
                    $body.find('input[type="date"]').val('');

                    // Reset select dropdowns to default placeholder
                    $body.find('select').each(function() {
                        $(this).val('');
                        // Re-select the disabled placeholder option
                        $(this).find('option[disabled]').prop('selected', true);
                    });

                    // Uncheck priority checkbox and reset its value
                    $body.find('.priority_checkbox input[type="checkbox"]').prop('checked', false).val('0');

                    // Uncheck all price list checkboxes
                    $body.find('.price_list_wrapper input[type="checkbox"]').prop('checked', false);

                    // Brief visual feedback on the button
                    var $btnNode = $(btn);
                    var originalHtml = $btnNode.html();
                    $btnNode.html('<i class="fa-solid fa-check"></i> Done!');
                    $btnNode.css('background', 'linear-gradient(135deg, #2ed573, #1abc9c)');
                    setTimeout(function() {
                        $btnNode.html(originalHtml);
                        $btnNode.css('background', '');
                    }, 1200);
                }
            });
        }
    </script>
@endpush
