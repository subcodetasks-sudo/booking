<x-filament-widgets::widget>
    <x-filament::section :heading="__('panel.dashboard.slots_heading')">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @forelse ($slots as $slot)
                <div class="rounded-xl border p-4 {{ $slot['is_unavailable'] ? 'border-danger-300 bg-danger-50' : 'border-gray-200 bg-white' }}">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold">{{ $slot['time_range'] }}</h3>
                        <span class="text-xs {{ $slot['is_unavailable'] ? 'text-danger-600' : 'text-success-600' }}">
                            {{ $slot['is_unavailable'] ? __('panel.dashboard.unavailable') : __('panel.dashboard.available') }}
                        </span>
                    </div>

                    <div class="mt-3 space-y-1 text-xs text-gray-600">
                        <p>{{ __('panel.dashboard.capacity') }}: {{ $slot['capacity'] }}</p>
                        <p>{{ __('panel.dashboard.reserved') }}: {{ $slot['reserved'] }}</p>
                        <p>{{ __('panel.dashboard.held') }}: {{ $slot['held'] }}</p>
                        <p class="font-semibold text-gray-800">{{ __('panel.dashboard.remaining') }}: {{ $slot['available'] }}</p>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-xl border border-dashed p-6 text-center text-sm text-gray-500">
                    {{ __('panel.dashboard.no_slots_today') }}
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

