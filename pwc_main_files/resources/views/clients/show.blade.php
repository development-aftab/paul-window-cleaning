@extends('theme.layout.master')

@push('css')
    <style>
        :root {
            --cd-accent: #2f7bf6;
            --cd-accent-soft: #EAF1FE;
            --cd-bg: #f4f6f9;
            --cd-radius: 14px;
        }

        .cd-page {
            font-family: 'Hellix-Regular', sans-serif;
            background: var(--cd-bg);
            padding: 4px 0 24px;
        }

        .cd-not-set {
            color: #9CA5B4;
            font-style: italic;
        }

        /* ===== Assigned staff bar ===== */
        .cd-staff-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            background: #FFF;
            border: 1px solid #EEF1F5;
            border-radius: var(--cd-radius);
            padding: 10px 16px;
            margin-bottom: 12px;
        }

        .cd-staff-bar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .cd-staff-bar-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #9CA5B4;
            margin-bottom: 2px;
        }

        .cd-staff-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            background: var(--cd-accent-soft);
        }

        .cd-staff-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cd-staff-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .cd-staff-meta span {
            font-size: 12px;
            color: #6B7280;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .cd-staff-meta i {
            color: var(--cd-accent);
        }

        /* ===== Identity hero ===== */
        .cd-hero {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            background: #FFF;
            border-radius: var(--cd-radius);
            box-shadow: 0 2px 16px 0 rgba(20, 40, 80, 0.06);
            padding: 20px;
            margin-bottom: 14px;
        }

        .cd-hero-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .cd-hero-avatar {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: var(--cd-accent-soft);
            color: var(--cd-accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-family: 'Hellix-Bold';
            flex-shrink: 0;
        }

        .cd-hero-name {
            font-size: 23px;
            font-family: 'Hellix-Bold';
            color: var(--dark_blue);
            margin-bottom: 4px;
        }

        .cd-hero-contact {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            font-size: 13px;
            color: #6B7280;
            margin-bottom: 10px;
        }

        .cd-hero-contact span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .cd-hero-contact i {
            color: var(--cd-accent);
        }

        .cd-hero-badges {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .cd-btn-accent {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--cd-accent);
            color: #FFF;
            border: none;
            border-radius: 9px;
            padding: 10px 18px;
            font-size: 13px;
            font-family: 'Hellix-SemiBold';
            text-decoration: none;
            transition: filter .15s ease;
        }

        .cd-btn-accent:hover {
            filter: brightness(1.08);
            color: #FFF;
        }

        /* ===== Meta strip ===== */
        .cd-meta-strip {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 14px;
        }

        @media (max-width: 991px) {
            .cd-meta-strip {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .cd-meta-strip {
                grid-template-columns: 1fr;
            }
        }

        .cd-meta-item {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #FFF;
            border-radius: 12px;
            box-shadow: 0 2px 10px 0 rgba(20, 40, 80, 0.05);
            padding: 14px 16px;
        }

        .cd-meta-icon {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: var(--cd-accent-soft);
            color: var(--cd-accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }

        .cd-meta-label {
            font-size: 11px;
            color: #9CA5B4;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: 2px;
        }

        .cd-meta-value {
            font-size: 14px;
            font-family: 'Hellix-SemiBold';
            color: var(--dark_blue);
            line-height: 1.2;
        }

        /* ===== Card grid ===== */
        .cd-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
            align-items: start;
            margin-bottom: 14px;
        }

        @media (max-width: 767px) {
            .cd-grid {
                grid-template-columns: 1fr;
            }
        }

        .cd-grid-full {
            grid-column: 1 / -1;
        }

        .cd-card {
            background: #FFF;
            border-radius: var(--cd-radius);
            box-shadow: 0 2px 16px 0 rgba(20, 40, 80, 0.06);
            padding: 18px;
        }

        .cd-card-title {
            display: flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 14px;
            padding-bottom: 12px;
            border-bottom: 1px solid #F0F2F5;
        }

        .cd-card-title .cd-title-icon {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: var(--cd-accent-soft);
            color: var(--cd-accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .cd-card-title h3 {
            font-size: 14px;
            letter-spacing: .01em;
        }

        .cd-card-title .cd-count-badge {
            margin-left: auto;
            font-size: 10px;
            font-family: 'Hellix-SemiBold';
            color: #9CA5B4;
            background: #F4F6F9;
            border-radius: 20px;
            padding: 2px 9px;
        }

        /* info grid (location) */
        .cd-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px 14px;
        }

        .cd-info-item label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            color: #9CA5B4;
            text-transform: uppercase;
            letter-spacing: .03em;
            margin-bottom: 3px;
        }

        .cd-info-item label i {
            color: var(--cd-accent);
            font-size: 11px;
        }

        .cd-info-item span {
            font-size: 13px;
            font-family: 'Hellix-Medium';
            color: var(--dark_blue);
        }

        /* service detail rows */
        .cd-detail-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .cd-detail-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }

        .cd-detail-row label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: #6B7280;
        }

        .cd-detail-row label i {
            color: var(--cd-accent);
            width: 16px;
            text-align: center;
        }

        .cd-detail-row .cd-detail-value {
            font-size: 13px;
            font-family: 'Hellix-Medium';
            color: var(--dark_blue);
            text-align: right;
        }

        /* pill badges (used everywhere: identity, service details, availability) */
        .cd-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 11px;
            border-radius: 20px;
            font-size: 11px;
            font-family: 'Hellix-SemiBold';
            white-space: nowrap;
        }

        .cd-pill-blue {
            background: #E4EEFE;
            color: #1E5FC7;
        }

        .cd-pill-gold {
            background: #FCEFD3;
            color: #97690B;
        }

        .cd-pill-green {
            background: #DFF5E6;
            color: #1E8E4F;
        }

        .cd-pill-red {
            background: #FBE3E3;
            color: #C6322F;
        }

        /* pricing list */
        .cd-price-scroll {
            max-height: 220px;
            overflow-y: auto;
            padding-right: 6px;
        }

        .cd-price-scroll::-webkit-scrollbar {
            width: 6px;
        }

        .cd-price-scroll::-webkit-scrollbar-thumb {
            background: var(--grey_clr);
            border-radius: 3px;
        }

        .cd-price-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0 18px;
        }

        @media (max-width: 1199px) {
            .cd-price-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 575px) {
            .cd-price-grid {
                grid-template-columns: 1fr;
            }
        }

        .cd-price-row {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 10px;
            padding: 6px 2px;
            border-bottom: 1px solid #F0F2F5;
        }

        .cd-price-name {
            font-size: 12px;
            font-family: 'Hellix-Regular';
            color: #4A4A4A;
        }

        .cd-price-value {
            font-size: 12px;
            font-family: 'Hellix-SemiBold';
            color: var(--dark_blue);
            white-space: nowrap;
        }

        .cd-price-total-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px dashed #D6DCE5;
        }

        .cd-price-total-label {
            font-size: 13px;
            font-family: 'Hellix-SemiBold';
            color: #6B7280;
        }

        .cd-price-total-value {
            font-size: 18px;
            font-family: 'Hellix-Bold';
            color: var(--dark_blue);
        }

        .cd-empty {
            font-size: 13px;
            color: #9CA5B4;
            font-style: italic;
        }

        /* weekly availability grid */
        .cd-week-strip {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
        }

        @media (max-width: 767px) {
            .cd-week-strip {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .cd-day-chip {
            text-align: center;
            padding: 10px 4px;
            border-radius: 10px;
            font-size: 11px;
            font-family: 'Hellix-SemiBold';
        }

        .cd-day-chip small {
            display: block;
            font-size: 8px;
            font-weight: normal;
            font-family: 'Hellix-Regular';
            margin-top: 2px;
            opacity: .8;
        }

        .cd-legend {
            display: flex;
            gap: 14px;
            margin-top: 12px;
            font-size: 10px;
            color: #9CA5B4;
        }

        .cd-legend span {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .cd-legend i {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        .cd-legend .cd-dot-open {
            background: #1E8E4F;
        }

        .cd-legend .cd-dot-closed {
            background: #C6322F;
        }

        /* tabs / bottom section */
        .cd-bottom-card {
            background: #FFF;
            border-radius: var(--cd-radius);
            box-shadow: 0 2px 16px 0 rgba(20, 40, 80, 0.06);
            padding: 18px;
        }

        .cd-bottom-card .clients_tab {
            margin-bottom: 12px;
        }

        /* empty state (images / business card) */
        .cd-empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 8px;
            padding: 36px 16px;
            color: #9CA5B4;
        }

        .cd-empty-state i {
            font-size: 28px;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--cd-accent-soft);
            color: var(--cd-accent);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cd-empty-state p {
            font-size: 13px;
            color: #9CA5B4;
        }
    </style>
@endpush

@section('navbar-title')
    <div class="custom_justify_between create_clients_navbar">
        <a href="{{ url('clients') }}" class="back_btn_navbar">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h2 class="navbar_PageTitle">Client Details</h2>
    </div>
@endsection

@section('content')
    @php
        $isAdmin = auth()->user()->hasRole('admin');
        $isStaff = auth()->user()->hasRole('staff');

        $clientTypeValue = $client->client_type ?? '';
        $paymentTypeValue = $client->payment_type ?? '';

        $frequencyLabels = [
            'normalWeek' => 'Weekly Cycle',
            'monthly' => 'Monthly Cycle',
            'biMonthly' => 'Bi-Monthly Cycle',
            'eightWeek' => 'Eight Weeks Cycle',
            'quarterly' => '12 Weeks Cycle',
            'biAnnually' => 'Bi-Annually Cycle',
            'annually' => 'Annually Cycle',
        ];
        $frequencyLabel = $frequencyLabels[$client->service_frequency] ?? 'Not set';

        $weekDays = [
            'monday' => 'Mon',
            'tuesday' => 'Tue',
            'wednesday' => 'Wed',
            'thursday' => 'Thu',
            'friday' => 'Fri',
            'saturday' => 'Sat',
            'sunday' => 'Sun',
        ];

        $clientName = $client->name ?? (optional($client->user)->name ?? '');
        $initials = collect(preg_split('/\s+/', trim($clientName)))
            ->filter()
            ->map(fn($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->take(2)
            ->implode('') ?: '?';

        $rawPhone = $client->contact_phone ?? '';
        $formattedPhone = $rawPhone && strlen($rawPhone) == 10
            ? substr($rawPhone, 0, 3) . '-' . substr($rawPhone, 3, 3) . '-' . substr($rawPhone, 6, 4)
            : $rawPhone;

        $priceTotal = $client->clientPrice->sum(fn($schedule) => (float) ($schedule->value ?? 0));
    @endphp

    @if ($isAdmin || $isStaff)
        <section class="client_details cd-page">
            <div class="container-fluid custom_container">

                @if ($isAdmin && $client->staff && $client->staff->id)
                    <!-- Assigned staff bar (kept separate from client identity) -->
                    <div class="cd-staff-bar">
                        <div class="cd-staff-bar-left">
                            <div class="cd-staff-avatar">
                                <img src="{{ asset('website') }}/{{ $client->staff->profile->pic ?? 'users/no_avatar.jpg' }}"
                                    alt="Staff photo">
                            </div>
                            <div>
                                <div class="cd-staff-bar-label">Assigned Staff</div>
                                <div class="cd-staff-meta">
                                    <span>{{ $client->staff->name ?? '' }}</span>
                                    <span><i class="fa-solid fa-envelope"></i>{{ $client->staff->email ?? '' }}</span>
                                    <span><i class="fa-solid fa-phone"></i>{{ $client->staff->profile->phone ?? 'Not set' }}</span>
                                </div>
                            </div>
                        </div>
                        @if ($client->status == 0)
                            <div class="accept_reject_btn custom_flex">
                                <form id="accept-form" action="{{ route('staff_accept_status', $client->id) }}"
                                    method="POST">
                                    @csrf
                                    <input type="hidden" name="commission_percentage" id="commission_percentage"
                                        value="{{ $client->commission_percentage ?? '' }}">
                                    <input type="hidden" name="accept_branches" id="accept_branches" value="0">
                                    <button class="btn_global btn_green" type="button" id="acceptBtn">
                                        Accept <i class="fa-solid fa-check"></i>
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Identity hero -->
                <div class="cd-hero">
                    <div class="cd-hero-left">
                        <div class="cd-hero-avatar">{{ $initials }}</div>
                        <div>
                            <div class="cd-hero-name">{{ $clientName ?: 'Not set' }}</div>
                            <div class="cd-hero-contact">
                                <span><i class="fa-solid fa-envelope"></i>{{ $client->contact_email ?: 'Not set' }}</span>
                                <span><i class="fa-solid fa-phone"></i>{{ $formattedPhone ?: 'Not set' }}</span>
                            </div>
                            <div class="cd-hero-badges">
                                <span class="cd-pill cd-pill-blue">{{ $clientTypeValue ? ucfirst($clientTypeValue) : 'Not set' }}</span>
                                <span class="cd-pill cd-pill-gold">{{ $paymentTypeValue ? ucfirst($paymentTypeValue) : 'Not set' }}</span>
                            </div>
                        </div>
                    </div>
                    <a class="cd-btn-accent" href="{{ route('clients.edit', $client->id) }}">
                        Edit Client <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                </div>

                <!-- Meta strip -->
                <div class="cd-meta-strip">
                    <div class="cd-meta-item">
                        <div class="cd-meta-icon"><i class="fa-solid fa-calendar"></i></div>
                        <div>
                            <div class="cd-meta-label">Date Created</div>
                            <div class="cd-meta-value">{{ $client->created_at ? $client->created_at->format('m-d-Y') : 'Not set' }}</div>
                        </div>
                    </div>
                    <div class="cd-meta-item">
                        <div class="cd-meta-icon"><i class="fa-solid fa-percent"></i></div>
                        <div>
                            <div class="cd-meta-label">Commission</div>
                            <div class="cd-meta-value">{{ $client->commission_percentage !== null ? $client->commission_percentage . '%' : 'Not set' }}</div>
                        </div>
                    </div>
                    <div class="cd-meta-item">
                        <div class="cd-meta-icon"><i class="fa-solid fa-rotate"></i></div>
                        <div>
                            <div class="cd-meta-label">Frequency</div>
                            <div class="cd-meta-value">{{ $frequencyLabel }}</div>
                        </div>
                    </div>
                    <div class="cd-meta-item">
                        <div class="cd-meta-icon"><i class="fa-solid fa-route"></i></div>
                        <div>
                            <div class="cd-meta-label">Assigned Route</div>
                            <div class="cd-meta-value">
                                @forelse ($client->clientRoute as $route)
                                    {{ $route->name ?? '' }}
                                @empty
                                    <span class="cd-not-set">Not set</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                @if ($client->parent_id)
                    <div class="cd-card cd-grid-full" style="margin-bottom: 14px;">
                        <div class="cd-card-title">
                            <div class="cd-title-icon"><i class="fa-solid fa-building"></i></div>
                            <h3>Parent Company</h3>
                        </div>
                        <div class="cd-info-grid">
                            <div class="cd-info-item">
                                <label><i class="fa-solid fa-building"></i> Parent Name</label>
                                @if (optional($client->parentClient)->name)
                                    <span>{{ $client->parentClient->name }}</span>
                                @else
                                    <span class="cd-not-set">Not set</span>
                                @endif
                            </div>
                            <div class="cd-info-item">
                                <label><i class="fa-solid fa-phone"></i> Parent Phone</label>
                                @if (optional($client->parentClient)->contact_phone)
                                    <span>{{ $client->parentClient->contact_phone }}</span>
                                @else
                                    <span class="cd-not-set">Not set</span>
                                @endif
                            </div>
                            <div class="cd-info-item">
                                <label><i class="fa-solid fa-envelope"></i> Parent Email</label>
                                @if (optional($client->parentClient)->contact_email)
                                    <span>{{ $client->parentClient->contact_email }}</span>
                                @else
                                    <span class="cd-not-set">Not set</span>
                                @endif
                            </div>
                            <div class="cd-info-item">
                                <label><i class="fa-solid fa-location-dot"></i> Parent Address</label>
                                @if (optional($client->parentClient)->address)
                                    <span>{{ $client->parentClient->address }}</span>
                                @else
                                    <span class="cd-not-set">Not set</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Location + Service details -->
                <div class="cd-grid">
                    <div class="cd-card">
                        <div class="cd-card-title">
                            <div class="cd-title-icon"><i class="fa-solid fa-location-dot"></i></div>
                            <h3>Location</h3>
                        </div>
                        <div class="cd-info-grid">
                            <div class="cd-info-item" style="grid-column: 1 / -1;">
                                <label><i class="fa-solid fa-map"></i> Address</label>
                                @if ($client->address)
                                    <span>{{ trim(($client->house_no ?? '') . ' ' . $client->address) }}</span>
                                @else
                                    <span class="cd-not-set">Not set</span>
                                @endif
                            </div>
                            <div class="cd-info-item">
                                <label><i class="fa-solid fa-flag"></i> State</label>
                                @if ($client->state)
                                    <span>{{ $client->state }}</span>
                                @else
                                    <span class="cd-not-set">Not set</span>
                                @endif
                            </div>
                            <div class="cd-info-item">
                                <label><i class="fa-solid fa-city"></i> City</label>
                                @if ($client->city)
                                    <span>{{ $client->city }}</span>
                                @else
                                    <span class="cd-not-set">Not set</span>
                                @endif
                            </div>
                            <div class="cd-info-item">
                                <label><i class="fa-solid fa-hashtag"></i> ZipCode</label>
                                @if ($client->postal)
                                    <span>{{ $client->postal }}</span>
                                @else
                                    <span class="cd-not-set">Not set</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="cd-card">
                        <div class="cd-card-title">
                            <div class="cd-title-icon"><i class="fa-solid fa-list-check"></i></div>
                            <h3>Service Details</h3>
                        </div>
                        <div class="cd-detail-list">
                            <div class="cd-detail-row">
                                <label><i class="fa-solid fa-clock"></i> Best Time to Service</label>
                                <span class="cd-detail-value">
                                    @forelse ($client->clientHour as $time)
                                        {{ $time->start_hour ?? 'Not set' }} - {{ $time->end_hour ?? 'Not set' }}
                                    @empty
                                        <span class="cd-not-set">Not set</span>
                                    @endforelse
                                </span>
                            </div>
                            <div class="cd-detail-row">
                                <label><i class="fa-solid fa-store"></i> Client Type</label>
                                <span class="cd-pill cd-pill-blue">{{ $clientTypeValue ? ucfirst($clientTypeValue) : 'Not set' }}</span>
                            </div>
                            <div class="cd-detail-row">
                                <label><i class="fa-solid fa-credit-card"></i> Payment Type</label>
                                <span class="cd-pill cd-pill-gold">{{ $paymentTypeValue ? ucfirst($paymentTypeValue) : 'Not set' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Price -->
                <div class="cd-card cd-grid-full" style="margin-bottom: 14px;">
                    <div class="cd-card-title">
                        <div class="cd-title-icon"><i class="fa-solid fa-dollar-sign"></i></div>
                        <h3>Price</h3>
                        <span class="cd-count-badge">{{ $client->clientPrice->count() }} items</span>
                    </div>
                    @forelse($client->clientPrice as $schedule)
                        @if ($loop->first)
                            <div class="cd-price-scroll">
                                <div class="cd-price-grid">
                        @endif
                                    <div class="cd-price-row">
                                        <span class="cd-price-name">{{ $schedule->name ?? '' }}</span>
                                        <span class="cd-price-value">${{ $schedule->value ?? '' }}</span>
                                    </div>
                        @if ($loop->last)
                                </div>
                            </div>
                        @endif
                    @empty
                        <span class="cd-empty">No pricing data available</span>
                    @endforelse
                </div>

                <!-- Availability -->
                <div class="cd-card cd-grid-full" style="margin-bottom: 14px;">
                    <div class="cd-card-title">
                        <div class="cd-title-icon"><i class="fa-solid fa-calendar-days"></i></div>
                        <h3>Availability</h3>
                    </div>
                    <div class="cd-week-strip">
                        @foreach ($weekDays as $day => $short)
                            @php $isClosed = $client->clientDay->contains('day', $day); @endphp
                            <div class="cd-day-chip {{ $isClosed ? 'cd-pill-red' : 'cd-pill-green' }}">
                                {{ $short }}
                                <small>{{ $isClosed ? 'Closed' : 'Open' }}</small>
                            </div>
                        @endforeach
                    </div>
                    <div class="cd-legend">
                        <span><i class="cd-dot-open"></i> Open</span>
                        <span><i class="cd-dot-closed"></i> Closed</span>
                    </div>
                </div>

                <!-- Images / Business card tabs -->
                <div class="cd-bottom-card">
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
                            @if ($client->clientImage->count())
                                <div class="clients_detail_images">
                                    @foreach ($client->clientImage as $image)
                                        <div class="custom_images">
                                            <img src="{{ asset('website') }}/{{ $image->name ?? 'no-image.jpg' }}">
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="cd-empty-state">
                                    <i class="fa-solid fa-image"></i>
                                    <p>No image uploaded yet</p>
                                    <a class="cd-btn-accent" href="{{ route('clients.edit', $client->id) }}">
                                        Upload Image <i class="fa-solid fa-upload"></i>
                                    </a>
                                </div>
                            @endif
                        </div>
                        <div class="tab-pane fade" id="pills-businessCard" role="tabpanel"
                            aria-labelledby="pills-businessCard-tab" tabindex="0">
                            <div class="business_card">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="business_card_images">
                                            <h4>Business Card Front</h4>
                                            @if ($client->front_image)
                                                <div class="custom_images">
                                                    <img src="{{ asset('website') }}/{{ $client->front_image }}">
                                                </div>
                                            @else
                                                <div class="cd-empty-state">
                                                    <i class="fa-solid fa-id-card"></i>
                                                    <p>No image uploaded yet</p>
                                                    <a class="cd-btn-accent" href="{{ route('clients.edit', $client->id) }}">
                                                        Upload Image <i class="fa-solid fa-upload"></i>
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="business_card_images">
                                            <h4>Business Card Back</h4>
                                            @if ($client->back_image)
                                                <div class="custom_images">
                                                    <img src="{{ asset('website') }}/{{ $client->back_image }}">
                                                </div>
                                            @else
                                                <div class="cd-empty-state">
                                                    <i class="fa-solid fa-id-card"></i>
                                                    <p>No image uploaded yet</p>
                                                    <a class="cd-btn-accent" href="{{ route('clients.edit', $client->id) }}">
                                                        Upload Image <i class="fa-solid fa-upload"></i>
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($isAdmin)
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
            @endif
        </section>
    @endif
@endsection

@if(auth()->user()->hasRole('admin'))
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
                    if (!$('#commission_percentage').val()) {
                        $('#commissionModal').modal('show');
                    } else {
                        handleAccept();
                    }
                });

                $('#submitCommission').click(function() {
                    var commissionValue = $('#commissionInput').val();
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
@endif
