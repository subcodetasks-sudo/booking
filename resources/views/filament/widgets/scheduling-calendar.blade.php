<x-filament-widgets::widget>
    <x-filament::section :heading="__('panel.dashboard.calendar.heading')">
        <div class="space-y-4">
            {{-- Toolbar: independent of calendar direction --}}
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between" dir="rtl">
                <div class="flex flex-wrap items-center gap-2">
                    <x-filament::button
                        wire:click="createReservation"
                        icon="heroicon-m-plus"
                        size="sm"
                    >
                        {{ __('panel.dashboard.calendar.add_available') }}
                    </x-filament::button>
                    <x-filament::button
                        wire:click="placeholderCopySchedule"
                        icon="heroicon-m-document-duplicate"
                        color="gray"
                        outlined
                        size="sm"
                    >
                        {{ __('panel.dashboard.calendar.copy_week') }}
                    </x-filament::button>
                    <x-filament::button
                        wire:click="openAvailabilitySettings"
                        icon="heroicon-m-cog-6-tooth"
                        color="gray"
                        outlined
                        size="sm"
                    >
                        {{ __('panel.dashboard.calendar.availability_settings') }}
                    </x-filament::button>
                </div>

                <div class="flex flex-wrap items-center justify-center gap-2">
                    <x-filament::icon-button
                        wire:click="previousWeek"
                        icon="heroicon-m-chevron-right"
                        :tooltip="__('panel.dashboard.calendar.prev_week')"
                        color="gray"
                    />
                    <span class="min-w-[10rem] text-center text-sm font-semibold text-gray-700 dark:text-gray-200">
                        {{ $calendar['range_label'] }}
                    </span>
                    <x-filament::icon-button
                        wire:click="nextWeek"
                        icon="heroicon-m-chevron-left"
                        :tooltip="__('panel.dashboard.calendar.next_week')"
                        color="gray"
                    />
                    <x-filament::button wire:click="goToday" size="sm" outlined>
                        {{ __('panel.dashboard.calendar.today') }}
                    </x-filament::button>
                </div>
            </div>

            {{--
                شبكة ثابتة بـ LTR حتى تبقى الأعمدة (وقت + 7 أيام) صفوفًا أفقية واضحة.
                النص داخل الخلايا يبقى rtl في الواجهة العربية عبر text-start و dir على المحتوى فقط.
            --}}
            <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm dark:border-white/10">
                <div
                    class="scheduling-calendar-grid grid w-full min-w-[56rem]"
                    dir="ltr"
                    style="grid-template-columns: 3.25rem repeat(7, minmax(5.75rem, 1fr));"
                >
                    {{-- صف الترويسة: زاوية + 7 أيام --}}
                    <div
                        class="sticky left-0 z-[1] border-b border-e border-gray-200 bg-gray-50 dark:border-white/10 dark:bg-gray-900/80"
                        aria-hidden="true"
                    ></div>

                    @foreach ($calendar['days'] as $day)
                        <div
                            class="border-b border-gray-200 bg-gray-50 p-2 text-center dark:border-white/10 dark:bg-white/5"
                            dir="rtl"
                        >
                            <button
                                type="button"
                                wire:click="toggleDayClosure('{{ $day['date'] }}')"
                                wire:confirm="{{ __('panel.dashboard.calendar.confirm_toggle_day') }}"
                                class="w-full rounded-lg px-1 py-1 text-xs font-semibold text-gray-900 hover:bg-white hover:shadow-sm dark:text-white dark:hover:bg-white/10"
                            >
                                <div>{{ $day['header_primary'] }}</div>
                                <div class="mt-0.5 text-[0.65rem] font-normal text-gray-600 dark:text-gray-400">
                                    {{ $day['header_secondary'] }}
                                </div>
                                @if ($day['is_holiday'])
                                    <div class="mt-1 text-[0.6rem] text-amber-700 dark:text-amber-400">
                                        {{ __('panel.dashboard.calendar.day_off') }}
                                    </div>
                                @endif
                            </button>
                        </div>
                    @endforeach

                    {{-- صفوف الساعات --}}
                    @foreach ($calendar['hours'] as $hour)
                        <div
                            class="sticky left-0 z-[1] flex items-start border-b border-e border-gray-100 bg-gray-50/95 px-2 py-2 text-end text-[0.7rem] tabular-nums text-gray-600 dark:border-white/5 dark:bg-gray-900/90 dark:text-gray-400"
                            dir="rtl"
                        >
                            {{ sprintf('%02d:00', $hour) }}
                        </div>

                        @foreach ($calendar['days'] as $day)
                            @php
                                $cell = collect($day['cells'])->firstWhere('hour', $hour);
                                $status = $cell['status'] ?? 'outside';
                            @endphp

                            @php
                                $stripe = in_array($status, ['holiday', 'unavailable', 'outside'], true);
                                $cellBg =
                                    match ($status) {
                                        'available' => 'bg-emerald-50 dark:bg-emerald-950/40',
                                        'booked' => 'bg-amber-50 dark:bg-amber-950/40',
                                        default => 'bg-gray-50 dark:bg-white/5',
                                    };
                            @endphp
                            <div
                                class="relative border-b border-gray-100 p-1.5 dark:border-white/5 {{ $cellBg }}"
                                dir="rtl"
                                @if ($stripe) style="background-image: repeating-linear-gradient(135deg, rgba(0,0,0,0.06) 0, rgba(0,0,0,0.06) 4px, transparent 4px, transparent 8px);" @endif
                            >
                                <div class="flex min-h-[3.25rem] flex-col justify-between gap-1">
                                    @if ($status === 'booked')
                                        <div class="text-[0.65rem] font-semibold text-amber-800 dark:text-amber-200">
                                            {{ __('panel.dashboard.calendar.booked') }}
                                        </div>
                                        <div class="truncate text-[0.65rem] text-gray-700 dark:text-gray-300">
                                            {{ $cell['detail'] ?? '' }}
                                        </div>
                                    @elseif ($status === 'holiday')
                                        <div class="text-[0.6rem] text-gray-600 dark:text-gray-400">
                                            {{ __('panel.dashboard.calendar.closed_day') }}
                                        </div>
                                    @elseif ($status === 'available')
                                        <div class="text-[0.6rem] text-emerald-800 dark:text-emerald-200">
                                            {{ sprintf('%02d:00 – %02d:00', $hour, $hour + 1) }}
                                        </div>
                                        <button
                                            type="button"
                                            wire:click="markHourUnavailable('{{ $day['date'] }}', {{ $hour }})"
                                            wire:confirm="{{ __('panel.dashboard.calendar.confirm_block_hour') }}"
                                            class="mt-auto inline-flex w-full items-center justify-center rounded border border-emerald-200 bg-white/90 px-1 py-0.5 text-[0.65rem] font-medium text-emerald-800 hover:bg-emerald-100 dark:border-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-100 dark:hover:bg-emerald-900"
                                        >
                                            <span class="text-base leading-none">+</span>
                                        </button>
                                    @elseif ($status === 'unavailable')
                                        <div class="text-[0.6rem] text-gray-600 dark:text-gray-400">
                                            {{ __('panel.dashboard.calendar.manual_close') }}
                                        </div>
                                        <button
                                            type="button"
                                            wire:click="markHourAvailable('{{ $day['date'] }}', {{ $hour }})"
                                            wire:confirm="{{ __('panel.dashboard.calendar.confirm_open_hour') }}"
                                            class="mt-auto text-[0.6rem] font-medium text-primary-600 hover:underline dark:text-primary-400"
                                        >
                                            {{ __('panel.dashboard.calendar.make_available') }}
                                        </button>
                                    @elseif ($status === 'outside')
                                        <div class="text-[0.55rem] leading-snug text-gray-500 dark:text-gray-500">
                                            {{ __('panel.dashboard.calendar.outside_hours') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>

            {{-- Legend --}}
            <div
                class="flex flex-wrap items-center gap-4 border-t border-gray-100 pt-3 text-xs text-gray-600 dark:border-white/10 dark:text-gray-400"
                dir="rtl"
            >
                <span class="inline-flex items-center gap-1.5">
                    <span class="h-3 w-3 shrink-0 rounded-full bg-emerald-400"></span>
                    {{ __('panel.dashboard.calendar.legend_available') }}
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="h-3 w-3 shrink-0 rounded-full bg-amber-400"></span>
                    {{ __('panel.dashboard.calendar.legend_booked') }}
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="h-3 w-3 shrink-0 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                    {{ __('panel.dashboard.calendar.legend_unavailable') }}
                </span>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
