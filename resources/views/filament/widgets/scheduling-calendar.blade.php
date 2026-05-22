@php
    use App\Filament\Resources\Reservations\ReservationResource;

    $stats = $statistics ?? ['available' => 0, 'booked' => 0, 'unavailable' => 0, 'total' => 0];
    $weekDays = $week_days ?? [];
    $activeDay = $active_day ?? null;
    $statusTone = match ($statusFilter ?? 'available') {
        'booked' => 'danger',
        'unavailable' => 'warning',
        default => 'success',
    };
    $tabs = [
        'available' => [
            'label' => __('panel.dashboard.calendar.filter_available'),
            'count' => $stats['available'] ?? 0,
            'tone' => 'success',
        ],
        'booked' => [
            'label' => __('panel.dashboard.calendar.filter_booked'),
            'count' => $stats['booked'] ?? 0,
            'tone' => 'danger',
        ],
        'unavailable' => [
            'label' => __('panel.dashboard.calendar.filter_unavailable'),
            'count' => $stats['unavailable'] ?? 0,
            'tone' => 'warning',
        ],
    ];
@endphp

<x-filament-widgets::widget>
    <x-filament::section
        class="scheduling-hub"
        dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
    >
        {{-- Header --}}
        <div class="scheduling-hub__header">
            <div class="scheduling-hub__intro">
                <p class="scheduling-hub__eyebrow">{{ __('panel.dashboard.calendar.eyebrow') }}</p>
                <h2 class="scheduling-hub__title">{{ __('panel.dashboard.calendar.heading') }}</h2>
                <p class="scheduling-hub__description">{{ __('panel.dashboard.calendar.description') }}</p>
            </div>

            <div class="scheduling-hub__toolbar">
                <div class="scheduling-hub__week-nav">
                    <x-filament::button
                        color="gray"
                        size="sm"
                        icon="heroicon-m-chevron-right"
                        wire:click="previousWeek"
                        :label="__('panel.dashboard.calendar.prev_week')"
                    />
                    <span class="scheduling-hub__range">{{ $calendar['range_label'] }}</span>
                    <x-filament::button
                        color="gray"
                        size="sm"
                        icon="heroicon-m-chevron-left"
                        wire:click="nextWeek"
                        :label="__('panel.dashboard.calendar.next_week')"
                    />
                    <x-filament::button
                        color="gray"
                        size="sm"
                        outlined
                        wire:click="goToday"
                    >
                        {{ __('panel.dashboard.calendar.today') }}
                    </x-filament::button>
                </div>

                <div class="scheduling-hub__actions">
                    <x-filament::button
                        color="primary"
                        size="sm"
                        icon="heroicon-m-plus"
                        wire:click="createReservation"
                    >
                        {{ __('panel.dashboard.calendar.add_available') }}
                    </x-filament::button>
                
                    <x-filament::button
                        color="gray"
                        size="sm"
                        icon="heroicon-m-cog-6-tooth"
                        wire:click="openAvailabilitySettings"
                    >
                        {{ __('panel.dashboard.calendar.availability_settings') }}
                    </x-filament::button>
                </div>
            </div>
        </div>

        {{-- Statistics --}}
        <div class="scheduling-hub__stats">
            <div class="scheduling-stat scheduling-stat--total">
                <x-filament::icon icon="heroicon-o-calendar-days" class="scheduling-stat__icon" />
                <div>
                    <span class="scheduling-stat__value">{{ $stats['total'] }}</span>
                    <span class="scheduling-stat__label">{{ __('panel.dashboard.calendar.stat_total') }}</span>
                </div>
            </div>
            <div class="scheduling-stat scheduling-stat--success">
                <x-filament::icon icon="heroicon-o-check-circle" class="scheduling-stat__icon" />
                <div>
                    <span class="scheduling-stat__value">{{ $stats['available'] }}</span>
                    <span class="scheduling-stat__label">{{ __('panel.dashboard.calendar.stat_available') }}</span>
                </div>
            </div>
            <div class="scheduling-stat scheduling-stat--danger">
                <x-filament::icon icon="heroicon-o-lock-closed" class="scheduling-stat__icon" />
                <div>
                    <span class="scheduling-stat__value">{{ $stats['booked'] }}</span>
                    <span class="scheduling-stat__label">{{ __('panel.dashboard.calendar.stat_booked') }}</span>
                </div>
            </div>
            <div class="scheduling-stat scheduling-stat--warning">
                <x-filament::icon icon="heroicon-o-no-symbol" class="scheduling-stat__icon" />
                <div>
                    <span class="scheduling-stat__value">{{ $stats['unavailable'] }}</span>
                    <span class="scheduling-stat__label">{{ __('panel.dashboard.calendar.stat_unavailable') }}</span>
                </div>
            </div>
        </div>

        {{-- Status tabs --}}
        <div class="scheduling-tabs" role="tablist" aria-label="{{ __('panel.dashboard.calendar.tabs_label') }}">
            @foreach ($tabs as $key => $tab)
                <button
                    type="button"
                    role="tab"
                    id="scheduling-tab-{{ $key }}"
                    wire:click="$set('statusFilter', '{{ $key }}')"
                    @class([
                        'scheduling-tab',
                        'scheduling-tab--' . $tab['tone'],
                        'scheduling-tab--active' => $statusFilter === $key,
                    ])
                    aria-selected="{{ $statusFilter === $key ? 'true' : 'false' }}"
                    aria-controls="scheduling-tabpanel"
                >
                    <span class="scheduling-tab__label">{{ $tab['label'] }}</span>
                    <span @class([
                        'scheduling-tab__counter',
                        'scheduling-tab__counter--' . $tab['tone'],
                        'scheduling-tab__counter--active' => $statusFilter === $key,
                    ])>
                        {{ $tab['count'] }}
                    </span>
                </button>
            @endforeach
        </div>

        <div
            id="scheduling-tabpanel"
            role="tabpanel"
            aria-labelledby="scheduling-tab-{{ $statusFilter }}"
            class="scheduling-tabpanel"
            wire:key="tabpanel-{{ $statusFilter }}"
        >
        @include('filament.widgets.partials.scheduling-week-days', [
            'weekDays' => $weekDays,
            'activeDay' => $activeDay,
            'statusTone' => $statusTone,
            'selectedDay' => $selectedDay,
            'statusFilter' => $statusFilter,
        ])

        </div>

        {{-- Legend --}}
        <div class="scheduling-hub__legend">
            <span class="scheduling-legend scheduling-legend--success">{{ __('panel.dashboard.calendar.legend_available') }}</span>
            <span class="scheduling-legend scheduling-legend--danger">{{ __('panel.dashboard.calendar.legend_booked') }}</span>
            <span class="scheduling-legend scheduling-legend--warning">{{ __('panel.dashboard.calendar.legend_unavailable') }}</span>
        </div>
    </x-filament::section>

    @assets
        <style>
            .scheduling-hub .fi-section-content { padding: 1.25rem 1.5rem; }

            .scheduling-hub__header {
                display: flex;
                flex-wrap: wrap;
                align-items: flex-start;
                justify-content: space-between;
                gap: 1.25rem;
                margin-bottom: 1.5rem;
            }

            .scheduling-hub__eyebrow {
                font-size: 0.7rem;
                font-weight: 600;
                letter-spacing: 0.06em;
                text-transform: uppercase;
                color: rgb(var(--gray-500));
                margin: 0 0 0.35rem;
            }

            .scheduling-hub__title {
                font-size: 1.35rem;
                font-weight: 700;
                line-height: 1.25;
                color: rgb(var(--gray-950));
                margin: 0 0 0.35rem;
            }

            .dark .scheduling-hub__title { color: rgb(var(--gray-50)); }

            .scheduling-hub__description {
                font-size: 0.875rem;
                line-height: 1.55;
                color: rgb(var(--gray-500));
                margin: 0;
                max-width: 36rem;
            }

            .scheduling-hub__toolbar {
                display: flex;
                flex-direction: column;
                align-items: flex-end;
                gap: 0.75rem;
            }

            [dir="rtl"] .scheduling-hub__toolbar { align-items: flex-start; }

            .scheduling-hub__week-nav {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 0.5rem;
            }

            .scheduling-hub__range {
                font-size: 0.875rem;
                font-weight: 600;
                color: rgb(var(--gray-700));
                padding: 0 0.35rem;
                min-width: 8rem;
                text-align: center;
            }

            .dark .scheduling-hub__range { color: rgb(var(--gray-300)); }

            .scheduling-hub__actions {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .scheduling-hub__stats {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 0.75rem;
                margin-bottom: 1.25rem;
            }

            @media (max-width: 1024px) {
                .scheduling-hub__stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            }

            @media (max-width: 640px) {
                .scheduling-hub__stats { grid-template-columns: 1fr; }
            }

            .scheduling-stat {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                padding: 0.85rem 1rem;
                border-radius: 0.75rem;
                border: 1px solid rgb(var(--gray-200));
                background: rgb(var(--gray-50));
                box-shadow: 0 1px 2px rgb(0 0 0 / 0.04);
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }

            .dark .scheduling-stat {
                border-color: rgb(var(--gray-700));
                background: rgb(var(--gray-900));
            }

            .scheduling-stat:hover {
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgb(0 0 0 / 0.06);
            }

            .scheduling-stat__icon {
                width: 1.5rem;
                height: 1.5rem;
                flex-shrink: 0;
            }

            .scheduling-stat--total .scheduling-stat__icon { color: rgb(var(--primary-600)); }
            .scheduling-stat--success .scheduling-stat__icon { color: rgb(var(--success-600)); }
            .scheduling-stat--danger .scheduling-stat__icon { color: rgb(var(--danger-600)); }
            .scheduling-stat--warning .scheduling-stat__icon { color: rgb(var(--warning-600)); }

            .scheduling-stat__value {
                display: block;
                font-size: 1.35rem;
                font-weight: 700;
                line-height: 1.2;
                color: rgb(var(--gray-950));
            }

            .dark .scheduling-stat__value { color: rgb(var(--gray-50)); }

            .scheduling-stat__label {
                display: block;
                font-size: 0.75rem;
                color: rgb(var(--gray-500));
                line-height: 1.4;
            }

            .scheduling-tabs {
                display: flex;
                gap: 0;
                margin-bottom: 0;
                border-bottom: 1px solid rgb(var(--gray-200));
            }

            .dark .scheduling-tabs { border-color: rgb(var(--gray-700)); }

            .scheduling-tab {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.75rem 1.25rem;
                font-size: 0.875rem;
                font-weight: 600;
                color: rgb(var(--gray-500));
                background: transparent;
                border: none;
                border-bottom: 2px solid transparent;
                margin-bottom: -1px;
                cursor: pointer;
                transition: color 0.2s ease, border-color 0.2s ease, background 0.2s ease;
                white-space: nowrap;
            }

            .scheduling-tab:hover {
                color: rgb(var(--gray-800));
                background: rgb(var(--gray-50));
            }

            .dark .scheduling-tab { color: rgb(var(--gray-400)); }
            .dark .scheduling-tab:hover {
                color: rgb(var(--gray-100));
                background: rgb(var(--gray-800));
            }

            .scheduling-tab--active.scheduling-tab--success {
                color: rgb(var(--success-700));
                border-bottom-color: rgb(var(--success-600));
            }

            .scheduling-tab--active.scheduling-tab--danger {
                color: rgb(var(--danger-700));
                border-bottom-color: rgb(var(--danger-600));
            }

            .scheduling-tab--active.scheduling-tab--warning {
                color: rgb(var(--warning-700));
                border-bottom-color: rgb(var(--warning-600));
            }

            .scheduling-tab__counter {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 1.35rem;
                height: 1.35rem;
                padding: 0 0.4rem;
                font-size: 0.7rem;
                font-weight: 700;
                font-variant-numeric: tabular-nums;
                line-height: 1;
                border-radius: 9999px;
                background: rgb(var(--gray-100));
                color: rgb(var(--gray-600));
                transition: background 0.2s ease, color 0.2s ease;
            }

            .dark .scheduling-tab__counter {
                background: rgb(var(--gray-700));
                color: rgb(var(--gray-300));
            }

            .scheduling-tab__counter--success.scheduling-tab__counter--active {
                background: rgb(var(--success-100));
                color: rgb(var(--success-700));
            }

            .scheduling-tab__counter--danger.scheduling-tab__counter--active {
                background: rgb(var(--danger-100));
                color: rgb(var(--danger-700));
            }

            .scheduling-tab__counter--warning.scheduling-tab__counter--active {
                background: rgb(var(--warning-100));
                color: rgb(var(--warning-700));
            }

            .dark .scheduling-tab__counter--success.scheduling-tab__counter--active {
                background: rgb(var(--success-950));
                color: rgb(var(--success-400));
            }

            .dark .scheduling-tab__counter--danger.scheduling-tab__counter--active {
                background: rgb(var(--danger-950));
                color: rgb(var(--danger-400));
            }

            .dark .scheduling-tab__counter--warning.scheduling-tab__counter--active {
                background: rgb(var(--warning-950));
                color: rgb(var(--warning-400));
            }

            .scheduling-tabpanel {
                padding-top: 1.25rem;
            }

            @media (max-width: 640px) {
                .scheduling-tabs {
                    overflow-x: auto;
                    -webkit-overflow-scrolling: touch;
                }

                .scheduling-tab {
                    flex: 1 1 0;
                    justify-content: center;
                    padding: 0.65rem 0.75rem;
                    font-size: 0.8125rem;
                }
            }

            .scheduling-days-label {
                margin: 1rem 0 0.5rem;
                font-size: 0.75rem;
                font-weight: 600;
                color: rgb(var(--gray-500));
                text-transform: uppercase;
                letter-spacing: 0.04em;
            }

            .scheduling-day-tabs {
                display: grid;
                grid-template-columns: repeat(7, minmax(0, 1fr));
                gap: 0.5rem;
                margin-bottom: 1rem;
            }

            @media (max-width: 1024px) {
                .scheduling-day-tabs {
                    grid-template-columns: repeat(4, minmax(0, 1fr));
                }
            }

            @media (max-width: 640px) {
                .scheduling-day-tabs {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            .scheduling-day-tab {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 0.2rem;
                padding: 0.65rem 0.5rem;
                border: 1px solid rgb(var(--gray-200));
                border-radius: 0.65rem;
                background: white;
                cursor: pointer;
                transition: all 0.2s ease;
                text-align: center;
            }

            .dark .scheduling-day-tab {
                border-color: rgb(var(--gray-700));
                background: rgb(var(--gray-900));
            }

            .scheduling-day-tab:hover {
                border-color: rgb(var(--primary-300));
                box-shadow: 0 2px 8px rgb(0 0 0 / 0.06);
            }

            .scheduling-day-tab--active.scheduling-day-tab--success {
                border-color: rgb(var(--success-500));
                background: rgb(var(--success-50));
            }

            .scheduling-day-tab--active.scheduling-day-tab--danger {
                border-color: rgb(var(--danger-500));
                background: rgb(var(--danger-50));
            }

            .scheduling-day-tab--active.scheduling-day-tab--warning {
                border-color: rgb(var(--warning-500));
                background: rgb(var(--warning-50));
            }

            .scheduling-day-tab--today:not(.scheduling-day-tab--active) {
                box-shadow: inset 0 0 0 1px rgb(var(--primary-400));
            }

            .scheduling-day-tab--closed {
                opacity: 0.65;
            }

            .scheduling-day-tab__name {
                font-size: 0.8125rem;
                font-weight: 700;
                color: rgb(var(--gray-900));
            }

            .dark .scheduling-day-tab__name { color: rgb(var(--gray-100)); }

            .scheduling-day-tab__date {
                font-size: 0.65rem;
                color: rgb(var(--gray-500));
            }

            .scheduling-day-tab__counter {
                margin-top: 0.15rem;
                min-width: 1.25rem;
                height: 1.25rem;
                padding: 0 0.35rem;
                font-size: 0.65rem;
                font-weight: 700;
                border-radius: 9999px;
                background: rgb(var(--gray-100));
                color: rgb(var(--gray-600));
            }

            .scheduling-day-tab__counter--zero {
                opacity: 0.5;
            }

            .scheduling-day-tab__counter--success.scheduling-day-tab__counter--active {
                background: rgb(var(--success-600));
                color: white;
            }

            .scheduling-day-tab__counter--danger.scheduling-day-tab__counter--active {
                background: rgb(var(--danger-600));
                color: white;
            }

            .scheduling-day-tab__counter--warning.scheduling-day-tab__counter--active {
                background: rgb(var(--warning-600));
                color: white;
            }

            .scheduling-day-panel {
                border: 1px solid rgb(var(--gray-200));
                border-radius: 0.875rem;
                overflow: hidden;
                background: white;
                box-shadow: 0 1px 3px rgb(0 0 0 / 0.04);
            }

            .dark .scheduling-day-panel {
                border-color: rgb(var(--gray-700));
                background: rgb(var(--gray-900));
            }

            .scheduling-day-panel__header {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
                padding: 0.85rem 1rem;
                background: rgb(var(--gray-50));
                border-bottom: 1px solid rgb(var(--gray-200));
            }

            .dark .scheduling-day-panel__header {
                background: rgb(var(--gray-800));
                border-color: rgb(var(--gray-700));
            }

            .scheduling-day-panel__title {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 0.5rem;
            }

            .scheduling-day-panel__name {
                margin: 0;
                font-size: 1rem;
                font-weight: 700;
                color: rgb(var(--gray-900));
            }

            .dark .scheduling-day-panel__name { color: rgb(var(--gray-100)); }

            .scheduling-day-panel__date {
                font-size: 0.8125rem;
                color: rgb(var(--gray-500));
            }

            .scheduling-day-panel__actions {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 0.35rem;
            }

            .scheduling-empty--compact {
                padding: 1.5rem 1rem;
                margin: 1rem;
            }

            .scheduling-empty--compact .scheduling-empty__text {
                margin: 0;
            }

            .scheduling-grid {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 0.75rem;
                padding: 1rem;
            }

            .scheduling-grid--in-panel {
                padding: 1rem;
            }

            @media (max-width: 1280px) {
                .scheduling-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            }

            @media (max-width: 1024px) {
                .scheduling-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            }

            @media (max-width: 640px) {
                .scheduling-grid { grid-template-columns: 1fr; }
            }

            .scheduling-card {
                display: flex;
                flex-direction: column;
                gap: 0.65rem;
                padding: 0.85rem;
                border-radius: 0.75rem;
                border: 1px solid rgb(var(--gray-200));
                background: white;
                box-shadow: 0 1px 2px rgb(0 0 0 / 0.04);
                transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
            }

            .dark .scheduling-card {
                border-color: rgb(var(--gray-700));
                background: rgb(var(--gray-800));
            }

            .scheduling-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgb(0 0 0 / 0.08);
            }

            .scheduling-card--available:hover { border-color: rgb(var(--success-300)); }
            .scheduling-card--booked:hover { border-color: rgb(var(--danger-300)); }
            .scheduling-card--unavailable:hover { border-color: rgb(var(--warning-300)); }

            .scheduling-card__top {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 0.35rem;
            }

            .scheduling-card__time { font-variant-numeric: tabular-nums; }

            .scheduling-card__body {
                display: flex;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .scheduling-card__status-icon {
                width: 1.25rem;
                height: 1.25rem;
                flex-shrink: 0;
                margin-top: 0.1rem;
            }

            .scheduling-card--available .scheduling-card__status-icon { color: rgb(var(--success-600)); }
            .scheduling-card--booked .scheduling-card__status-icon { color: rgb(var(--danger-600)); }
            .scheduling-card--unavailable .scheduling-card__status-icon { color: rgb(var(--warning-600)); }

            .scheduling-card__day {
                display: block;
                font-size: 0.7rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.04em;
                color: rgb(var(--gray-400));
            }

            .scheduling-card__detail {
                display: block;
                font-size: 0.8125rem;
                font-weight: 500;
                line-height: 1.45;
                color: rgb(var(--gray-800));
            }

            .dark .scheduling-card__detail { color: rgb(var(--gray-200)); }

            .scheduling-card__detail--muted { color: rgb(var(--gray-500)); font-weight: 400; }

            .scheduling-card__tables-usage {
                display: block;
                font-size: 0.8125rem;
                font-weight: 600;
                color: rgb(var(--gray-700));
                margin-bottom: 0.25rem;
            }

            .dark .scheduling-card__tables-usage { color: rgb(var(--gray-300)); }

            .scheduling-card__capacity {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 0.5rem;
            }

            .scheduling-card__capacity-label {
                font-size: 0.75rem;
                font-weight: 600;
                color: rgb(var(--gray-600));
            }

            .dark .scheduling-card__capacity-label { color: rgb(var(--gray-400)); }

            .scheduling-card__capacity-input {
                width: 4.25rem;
                padding: 0.35rem 0.5rem;
                border: 1px solid rgb(var(--gray-300));
                border-radius: 0.5rem;
                font-size: 0.875rem;
                font-weight: 600;
                font-variant-numeric: tabular-nums;
                background: white;
                color: rgb(var(--gray-900));
            }

            .dark .scheduling-card__capacity-input {
                border-color: rgb(var(--gray-600));
                background: rgb(var(--gray-900));
                color: rgb(var(--gray-100));
            }

            .scheduling-card__actions {
                margin-top: auto;
                padding-top: 0.25rem;
            }

            .scheduling-empty {
                text-align: center;
                padding: 3rem 1.5rem;
                border: 1px dashed rgb(var(--gray-300));
                border-radius: 0.875rem;
                background: rgb(var(--gray-50));
            }

            .dark .scheduling-empty {
                border-color: rgb(var(--gray-600));
                background: rgb(var(--gray-900));
            }

            .scheduling-empty__icon-wrap {
                display: inline-flex;
                padding: 1rem;
                border-radius: 9999px;
                background: rgb(var(--gray-100));
                margin-bottom: 1rem;
            }

            .dark .scheduling-empty__icon-wrap { background: rgb(var(--gray-800)); }

            .scheduling-empty__icon {
                width: 2rem;
                height: 2rem;
                color: rgb(var(--gray-400));
            }

            .scheduling-empty__title {
                font-size: 1.05rem;
                font-weight: 600;
                margin: 0 0 0.35rem;
                color: rgb(var(--gray-900));
            }

            .dark .scheduling-empty__title { color: rgb(var(--gray-100)); }

            .scheduling-empty__text {
                font-size: 0.875rem;
                color: rgb(var(--gray-500));
                margin: 0 0 1rem;
                max-width: 22rem;
                margin-inline: auto;
            }

            .scheduling-hub__legend {
                display: flex;
                flex-wrap: wrap;
                gap: 1rem;
                margin-top: 1.25rem;
                padding-top: 1rem;
                border-top: 1px solid rgb(var(--gray-200));
            }

            .dark .scheduling-hub__legend { border-color: rgb(var(--gray-700)); }

            .scheduling-legend {
                font-size: 0.75rem;
                font-weight: 500;
                display: flex;
                align-items: center;
                gap: 0.35rem;
            }

            .scheduling-legend::before {
                content: '';
                width: 0.5rem;
                height: 0.5rem;
                border-radius: 9999px;
            }

            .scheduling-legend--success::before { background: rgb(var(--success-500)); }
            .scheduling-legend--danger::before { background: rgb(var(--danger-500)); }
            .scheduling-legend--warning::before { background: rgb(var(--warning-500)); }
        </style>
    @endassets
</x-filament-widgets::widget>
