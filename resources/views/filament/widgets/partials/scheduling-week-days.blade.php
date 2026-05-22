@php
    use App\Filament\Resources\Reservations\ReservationResource;
@endphp

<p class="scheduling-days-label">{{ __('panel.dashboard.calendar.days_label') }}</p>
<div class="scheduling-day-tabs" role="tablist" aria-label="{{ __('panel.dashboard.calendar.days_label') }}">
    @foreach ($weekDays as $day)
        <button
            type="button"
            role="tab"
            wire:click="selectDay('{{ $day['date'] }}')"
            @class([
                'scheduling-day-tab',
                'scheduling-day-tab--' . $statusTone,
                'scheduling-day-tab--active' => $selectedDay === $day['date'],
                'scheduling-day-tab--today' => $day['is_today'],
                'scheduling-day-tab--closed' => $day['is_holiday'],
            ])
            aria-selected="{{ $selectedDay === $day['date'] ? 'true' : 'false' }}"
            wire:key="week-day-{{ $day['date'] }}-{{ $statusFilter }}"
        >
            <span class="scheduling-day-tab__name">{{ $day['day_primary'] }}</span>
            <span class="scheduling-day-tab__date">{{ $day['day_secondary'] }}</span>
            <span @class([
                'scheduling-day-tab__counter',
                'scheduling-day-tab__counter--' . $statusTone,
                'scheduling-day-tab__counter--active' => $selectedDay === $day['date'],
                'scheduling-day-tab__counter--zero' => $day['filter_count'] === 0,
            ])>
                {{ $day['filter_count'] }}
            </span>
        </button>
    @endforeach
</div>

@if ($activeDay)
    <section
        class="scheduling-day-panel"
        wire:key="day-panel-{{ $activeDay['date'] }}-{{ $statusFilter }}"
    >
        <header class="scheduling-day-panel__header">
            <div class="scheduling-day-panel__title">
                <h3 class="scheduling-day-panel__name">{{ $activeDay['day_primary'] }}</h3>
                <span class="scheduling-day-panel__date">{{ $activeDay['day_secondary'] }}</span>
                @if ($activeDay['is_holiday'])
                    <x-filament::badge color="danger" size="sm">{{ __('panel.dashboard.calendar.day_off') }}</x-filament::badge>
                @endif
            </div>
            <div class="scheduling-day-panel__actions">
                <x-filament::badge color="success" size="sm">{{ $activeDay['counts']['available'] }}</x-filament::badge>
                <x-filament::badge color="danger" size="sm">{{ $activeDay['counts']['booked'] }}</x-filament::badge>
                <x-filament::badge color="warning" size="sm">{{ $activeDay['counts']['unavailable'] }}</x-filament::badge>
                <x-filament::button
                    color="{{ $activeDay['is_holiday'] ? 'success' : 'danger' }}"
                    size="sm"
                    outlined
                    icon="{{ $activeDay['is_holiday'] ? 'heroicon-m-lock-open' : 'heroicon-m-lock-closed' }}"
                    wire:click="toggleDayClosure('{{ $activeDay['date'] }}')"
                    wire:confirm="{{ __('panel.dashboard.calendar.confirm_toggle_day') }}"
                >
                    {{ $activeDay['is_holiday'] ? __('panel.dashboard.calendar.open_day') : __('panel.dashboard.calendar.close_day') }}
                </x-filament::button>
            </div>
        </header>

        @if ($activeDay['is_holiday'])
            <div class="scheduling-empty scheduling-empty--compact">
                <p class="scheduling-empty__text">{{ __('panel.dashboard.calendar.day_closed_message') }}</p>
            </div>
        @elseif (count($activeDay['slots']) === 0)
            <div class="scheduling-empty scheduling-empty--compact">
                <p class="scheduling-empty__text">{{ __('panel.dashboard.calendar.empty_day') }}</p>
            </div>
        @else
            <div class="scheduling-grid scheduling-grid--in-panel">
                @foreach ($activeDay['slots'] as $slot)
                    @php
                        $status = $slot['status'];
                        $color = match ($status) {
                            'available' => 'success',
                            'booked' => 'danger',
                            'unavailable' => 'warning',
                            default => 'gray',
                        };
                        $icon = match ($status) {
                            'available' => 'heroicon-o-check-circle',
                            'booked' => 'heroicon-o-user',
                            'unavailable' => 'heroicon-o-no-symbol',
                            default => 'heroicon-o-clock',
                        };
                        $statusLabel = match ($status) {
                            'available' => __('panel.dashboard.calendar.legend_available'),
                            'booked' => __('panel.dashboard.calendar.legend_booked'),
                            'unavailable' => __('panel.dashboard.calendar.legend_unavailable'),
                            default => $status,
                        };
                    @endphp
                    <article
                        class="scheduling-card scheduling-card--{{ $status }}"
                        wire:key="slot-{{ $slot['id'] }}-{{ $statusFilter }}"
                    >
                        <div class="scheduling-card__top">
                            <x-filament::badge :color="$color" class="scheduling-card__time">
                                {{ $slot['time_label'] }}
                            </x-filament::badge>
                            <x-filament::badge :color="$color" size="sm">
                                {{ $statusLabel }}
                            </x-filament::badge>
                        </div>

                        <div class="scheduling-card__body">
                            <x-filament::icon :icon="$icon" class="scheduling-card__status-icon" />
                            <div class="scheduling-card__info">
                                <span class="scheduling-card__tables-usage">
                                    {{ __('panel.dashboard.calendar.tables_usage', [
                                        'booked' => $slot['reserved_count'],
                                        'total' => $slot['capacity'],
                                    ]) }}
                                </span>
                                @if ($status === 'booked' && $slot['detail'])
                                    <span class="scheduling-card__detail">{{ $slot['detail'] }}</span>
                                @elseif ($status === 'available')
                                    <span class="scheduling-card__detail scheduling-card__detail--muted">
                                        {{ $slot['detail'] ?: __('panel.dashboard.calendar.ready_to_book') }}
                                    </span>
                                @elseif ($status === 'unavailable')
                                    <span class="scheduling-card__detail scheduling-card__detail--muted">
                                        {{ $slot['detail'] ?: __('panel.dashboard.calendar.manual_close') }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if ($status !== 'unavailable')
                            <div class="scheduling-card__capacity">
                                <label
                                    class="scheduling-card__capacity-label"
                                    for="tables-{{ $slot['id'] }}"
                                >
                                    {{ __('panel.dashboard.calendar.tables_count') }}
                                </label>
                                <input
                                    id="tables-{{ $slot['id'] }}"
                                    class="scheduling-card__capacity-input"
                                    type="number"
                                    min="1"
                                    max="99"
                                    value="{{ max(1, (int) $slot['capacity']) }}"
                                    wire:change="updateHourCapacity('{{ $slot['date'] }}', {{ $slot['hour'] }}, $event.target.value)"
                                />
                            </div>
                        @endif

                        <div class="scheduling-card__actions">
                            @if ($status === 'available')
                                <x-filament::button
                                    color="warning"
                                    size="xs"
                                    outlined
                                    icon="heroicon-m-lock-closed"
                                    wire:click="markHourUnavailable('{{ $slot['date'] }}', {{ $slot['hour'] }})"
                                    wire:confirm="{{ __('panel.dashboard.calendar.confirm_block_hour') }}"
                                >
                                    {{ __('panel.dashboard.calendar.mark_unavailable') }}
                                </x-filament::button>
                            @elseif ($status === 'unavailable')
                                <x-filament::button
                                    color="success"
                                    size="xs"
                                    outlined
                                    icon="heroicon-m-lock-open"
                                    wire:click="markHourAvailable('{{ $slot['date'] }}', {{ $slot['hour'] }})"
                                    wire:confirm="{{ __('panel.dashboard.calendar.confirm_open_hour') }}"
                                >
                                    {{ __('panel.dashboard.calendar.make_available') }}
                                </x-filament::button>
                            @elseif ($status === 'booked' && $slot['reservation_id'])
                                <x-filament::button
                                    color="gray"
                                    size="xs"
                                    outlined
                                    icon="heroicon-m-arrow-top-right-on-square"
                                    tag="a"
                                    :href="ReservationResource::getUrl('edit', ['record' => $slot['reservation_id']], panel: 'admin')"
                                >
                                    {{ __('panel.dashboard.calendar.view_booking') }}
                                </x-filament::button>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endif
