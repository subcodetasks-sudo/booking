<?php

namespace App\Http\Controllers;

use App\Models\DietaryOption;
use App\Models\Occasion;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\ReservationAddon;
use App\Models\ScheduleDayClosure;
use App\Models\SiteSetting;
use App\Models\TimeSlot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
    public function occasions()
    {
        $options = Occasion::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get(['id', 'name_ar', 'name_en'])
            ->map(fn (Occasion $occasion): array => [
                'id' => $occasion->id,
                'name_ar' => (string) $occasion->name_ar,
                'name_en' => (string) $occasion->name_en,
            ])
            ->all();

        return response()->json([
            'occasions' => $options,
        ]);
    }

    public function reservationAddons()
    {
        $addons = Product::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'name_ar', 'name_en', 'price'])
            ->map(fn (Product $product): array => [
                'id' => (int) $product->id,
                'name_ar' => (string) $product->name_ar,
                'name_en' => (string) $product->name_en,
                'price' => (float) $product->price,
            ])
            ->all();

        return response()->json([
            'addons' => $addons,
        ]);
    }

    public function dietaryOptions()
    {
        $options = DietaryOption::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['key', 'name_ar', 'name_en'])
            ->map(fn (DietaryOption $option): array => [
                'id' => $option->key,
                'ar' => $option->name_ar,
                'en' => $option->name_en,
            ])
            ->all();

        return response()->json([
            'options' => $options,
        ]);
    }

    public function availability(Request $request)
    {
        $data = $request->validate([
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
        ]);

        $date = Carbon::parse($data['date']);
        [$startAt, $endAt] = $this->getBookingWindow();
        $bookingActive = $this->bookingIsActive();
        $dayClosed = $this->dateIsClosed($date);

        if (! $bookingActive || $dayClosed) {
            return response()->json([
                'date' => $date->toDateString(),
                'booking_active' => $bookingActive,
                'day_closed' => $dayClosed,
                'booking_start' => $startAt,
                'booking_end' => $endAt,
                'counts' => [
                    'available' => 0,
                    'booked' => 0,
                    'blocked' => 0,
                    'total' => 0,
                ],
                'slots' => [],
            ]);
        }

        $timeKeys = $this->buildHourlySlots($date, $startAt, $endAt);

        $reservedByTime = Reservation::query()
            ->whereDate('reservation_date', $date->toDateString())
            ->where('status', '!=', 'cancelled')
            ->selectRaw('reservation_time, COUNT(*) as reservations_count')
            ->groupBy('reservation_time')
            ->pluck('reservations_count', 'reservation_time');

        $manualClosedSlots = TimeSlot::query()
            ->whereDate('slot_date', $date->toDateString())
            ->where('is_closed_manually', true)
            ->get(['start_time', 'end_time']);

        $counts = ['available' => 0, 'booked' => 0, 'blocked' => 0, 'total' => 0];

        $slots = collect($timeKeys)->map(function (string $start) use ($reservedByTime, $manualClosedSlots, &$counts): array {
            $reserved = (int) ($reservedByTime[$start.':00'] ?? $reservedByTime[$start] ?? 0);
            $isClosedManually = $this->slotIsManuallyClosed($manualClosedSlots, $start);

            if ($reserved > 0) {
                $status = 'booked';
            } elseif ($isClosedManually) {
                $status = 'blocked';
            } else {
                $status = 'available';
            }

            $counts['total']++;
            $counts[$status]++;

            $isUnavailable = $status !== 'available';

            return [
                'time' => $start,
                'end_time' => Carbon::createFromFormat('H:i', $start)->addHour()->format('H:i'),
                'capacity' => 1,
                'reserved' => $reserved,
                'held' => 0,
                'available' => $isUnavailable ? 0 : 1,
                'is_unavailable' => $isUnavailable,
                'is_closed_manually' => $isClosedManually,
                'status' => $status,
            ];
        })->all();

        return response()->json([
            'date' => $date->toDateString(),
            'booking_active' => true,
            'day_closed' => false,
            'booking_start' => $startAt,
            'booking_end' => $endAt,
            'counts' => $counts,
            'slots' => $slots,
        ]);
    }

    public function closedDates(Request $request)
    {
        $data = $request->validate([
            'from' => ['required', 'date_format:Y-m-d'],
            'to' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $dates = ScheduleDayClosure::query()
            ->whereBetween('closure_date', [$data['from'], $data['to']])
            ->orderBy('closure_date')
            ->pluck('closure_date')
            ->map(fn ($d) => Carbon::parse($d)->toDateString())
            ->values()
            ->all();

        return response()->json([
            'dates' => $dates,
        ]);
    }

    public function store(Request $request)
    {
        $maxGuests = $this->getMaxGuestCount();

        $request->merge([
            'customer_phone' => self::normalizeCustomerPhone((string) $request->input('customer_phone', '')),
            'reservation_time' => self::normalizeTimeToHi((string) $request->input('reservation_time', '')),
        ]);

        $data = $request->validate([
            'reservation_date' => ['required', 'date', 'after_or_equal:today'],
            'reservation_time' => ['required', 'date_format:H:i'],
            'guest_count' => ['required', 'integer', 'min:1', 'max:'.$maxGuests],

            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],

            'occasion_id' => ['nullable', 'integer', 'exists:occasions,id'],
            'occasion' => ['nullable', 'string', 'max:255'],
            'allergies_notes' => ['nullable', 'string', 'max:5000'],
            'reservation_notes' => ['nullable', 'string', 'max:5000'],
            'dietary_self_ids' => ['nullable', 'array'],
            'dietary_self_ids.*' => ['string', Rule::exists('dietary_options', 'key')],
            'dietary_guest_ids' => ['nullable', 'array'],
            'dietary_guest_ids.*' => ['string', Rule::exists('dietary_options', 'key')],

            'addons' => ['nullable', 'array'],
            'addons.*.addon_id' => [
                'nullable',
                'integer',
                Rule::exists('products', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'addons.*.name' => ['nullable', 'string', 'max:255'],
            'addons.*.price' => ['nullable', 'numeric', 'min:0'],
            'addons.*.quantity' => ['nullable', 'integer', 'min:1', 'max:999'],
        ]);

        return DB::transaction(function () use ($data) {
            $date = Carbon::parse($data['reservation_date']);
            if (! $this->bookingIsActive() || $this->dateIsClosed($date)) {
                throw ValidationException::withMessages([
                    'reservation_date' => 'الحجز غير متاح في هذا اليوم.',
                ]);
            }

            [$startAt, $endAt] = $this->getBookingWindow();
            $timeKeys = $this->buildHourlySlots($date, $startAt, $endAt);

            $requestedTime = substr((string) $data['reservation_time'], 0, 5);
            if (! in_array($requestedTime, $timeKeys, true)) {
                throw ValidationException::withMessages([
                    'reservation_time' => 'Selected time is خارج نافذة الحجز.',
                ]);
            }

            $reservedTimes = Reservation::query()
                ->whereDate('reservation_date', $date->toDateString())
                ->where('status', '!=', 'cancelled')
                ->pluck('reservation_time')
                ->map(fn ($v) => substr((string) $v, 0, 5))
                ->all();
            $reservedSet = array_flip($reservedTimes);

            if (isset($reservedSet[$requestedTime])) {
                throw ValidationException::withMessages([
                    'reservation_time' => 'الوقت المختار محجوز. اختر وقتًا متاحًا آخر.',
                ]);
            }

            $manualClosedSlots = TimeSlot::query()
                ->whereDate('slot_date', $date->toDateString())
                ->where('is_closed_manually', true)
                ->get(['start_time', 'end_time']);

            if ($this->slotIsManuallyClosed($manualClosedSlots, $requestedTime)) {
                throw ValidationException::withMessages([
                    'reservation_time' => 'الوقت المختار غير متاح. اختر وقتًا آخر.',
                ]);
            }

            $occasionId = isset($data['occasion_id']) ? (int) $data['occasion_id'] : null;
            $occasionName = trim((string) ($data['occasion'] ?? ''));
            if (! $occasionId && $occasionName !== '') {
                $occasion = Occasion::firstOrCreate(
                    ['name_ar' => $occasionName],
                    ['name_en' => $occasionName, 'is_active' => true],
                );
                $occasionId = $occasion->id;
            }

            $reservation = Reservation::create([
                'reservation_code' => $this->makeReservationCode(),
                'reservation_date' => $data['reservation_date'],
                'reservation_time' => $requestedTime,
                'guest_count' => $data['guest_count'],
                'status' => 'pending',
                'order_status' => 'no_order',
                'occasion_id' => $occasionId,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'] ?? null,
                'allergies_notes' => $data['allergies_notes'] ?? null,
                'reservation_notes' => $data['reservation_notes'] ?? null,
                'addons_total' => 0,
                'items_total' => 0,
                'total_amount' => 0,
            ]);

            $selfKeys = collect($data['dietary_self_ids'] ?? [])->filter()->unique()->values();
            $guestKeys = collect($data['dietary_guest_ids'] ?? [])->filter()->unique()->values();
            $allDietKeys = $selfKeys->merge($guestKeys)->unique()->values();
            if ($allDietKeys->isNotEmpty()) {
                $dietaryOptions = DietaryOption::query()
                    ->whereIn('key', $allDietKeys->all())
                    ->get(['id', 'key', 'name_ar', 'name_en']);

                $mapByKey = $dietaryOptions->keyBy('key');
                $pivotPayload = [];

                foreach ($selfKeys as $key) {
                    $opt = $mapByKey->get($key);
                    if ($opt) {
                        $pivotPayload[$opt->id] = ['scope' => 'self'];
                    }
                }
                foreach ($guestKeys as $key) {
                    $opt = $mapByKey->get($key);
                    if ($opt) {
                        $pivotPayload[$opt->id] = ['scope' => 'guests'];
                    }
                }

                if (! empty($pivotPayload)) {
                    $reservation->dietaryOptions()->sync($pivotPayload);
                }
            }

            $addonsTotal = 0.0;
            foreach (($data['addons'] ?? []) as $addon) {
                $qty = (int) ($addon['quantity'] ?? 0);
                if ($qty <= 0) {
                    continue;
                }

                $addonId = isset($addon['addon_id']) ? (int) $addon['addon_id'] : null;

                if ($addonId) {
                    $product = Product::query()
                        ->whereKey($addonId)
                        ->where('is_active', true)
                        ->first();
                    if (! $product) {
                        continue;
                    }

                    $reservationAddon = ReservationAddon::query()->firstOrCreate(
                        ['product_id' => $product->id],
                        [
                            'name_ar' => $product->name_ar,
                            'name_en' => $product->name_en,
                            'price' => $product->price,
                            'is_active' => true,
                        ],
                    );

                    $reservationAddon->forceFill([
                        'name_ar' => $product->name_ar,
                        'name_en' => $product->name_en,
                        'price' => $product->price,
                        'is_active' => true,
                    ])->save();

                    $price = (float) $reservationAddon->price;
                    $nameForPivot = (string) $reservationAddon->name_ar;
                } else {
                    $name = trim((string) ($addon['name'] ?? ''));
                    if ($name === '') {
                        continue;
                    }
                    $price = (float) ($addon['price'] ?? 0);
                    $reservationAddon = ReservationAddon::firstOrCreate(
                        ['name_ar' => $name],
                        ['name_en' => $name, 'price' => $price, 'is_active' => true],
                    );
                    $nameForPivot = $name;
                }

                $lineTotal = $qty * $price;
                $addonsTotal += $lineTotal;

                $reservation->addons()->syncWithoutDetaching([
                    $reservationAddon->id => [
                        'addon_name' => $nameForPivot,
                        'addon_price' => $price,
                        'quantity' => $qty,
                        'line_total' => $lineTotal,
                    ],
                ]);
            }

            $reservation->update([
                'addons_total' => $addonsTotal,
                'items_total' => 0,
                'total_amount' => $addonsTotal,
            ]);

            return response()->json([
                'ok' => true,
                'reservation_id' => $reservation->id,
                'reservation_code' => $reservation->reservation_code,
            ], 201);
        });
    }

    private function makeReservationCode(): string
    {
        do {
            $code = 'RSV-'.strtoupper(Str::random(8));
        } while (Reservation::query()->where('reservation_code', $code)->exists());

        return $code;
    }

    private function getBookingWindow(): array
    {
        $startAt = (string) SiteSetting::getValue('booking_start_time', '12:00');
        $endAt = (string) SiteSetting::getValue('booking_end_time', '23:00');

        if (! preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $startAt)) {
            $startAt = '12:00';
        }
        if (! preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $endAt)) {
            $endAt = '23:00';
        }

        if ($startAt >= $endAt) {
            $startAt = '12:00';
            $endAt = '23:00';
        }

        return [$startAt, $endAt];
    }

    private function buildHourlySlots(Carbon $date, string $startAt, string $endAt): array
    {
        $cursor = Carbon::parse($date->toDateString().' '.$startAt);
        $close = Carbon::parse($date->toDateString().' '.$endAt);
        $slots = [];

        while ($cursor->lt($close)) {
            $slots[] = $cursor->format('H:i');
            $cursor->addHour();
        }

        return $slots;
    }

    private function bookingIsActive(): bool
    {
        return (bool) SiteSetting::getValue('booking_is_active', true);
    }

    private function dateIsClosed(Carbon $date): bool
    {
        return ScheduleDayClosure::query()
            ->whereDate('closure_date', $date->toDateString())
            ->exists();
    }

    private function slotIsManuallyClosed(iterable $manualClosedSlots, string $slotTime): bool
    {
        $slotStart = $this->timeToMinutes($slotTime);
        $slotEnd = $slotStart + 60;

        foreach ($manualClosedSlots as $slot) {
            $closedStart = $this->timeToMinutes((string) $slot->start_time);
            $closedEnd = $this->timeToMinutes((string) $slot->end_time);

            if ($slotStart >= $closedStart && $slotEnd <= $closedEnd && $closedEnd > $closedStart) {
                return true;
            }
        }

        return false;
    }

    private function timeToMinutes(string $time): int
    {
        $parts = explode(':', substr($time, 0, 5));

        return ((int) ($parts[0] ?? 0)) * 60 + ((int) ($parts[1] ?? 0));
    }

    private function getMaxGuestCount(): int
    {
        $max = (int) SiteSetting::getValue('max_guest_count', 20);

        if ($max < 1) {
            return 20;
        }

        return $max;
    }

    /**
     * Trim, map Arabic/Persian digits to Latin, keep common phone symbols (spaces, dashes, parentheses, leading +).
     */
    private static function normalizeCustomerPhone(string $phone): string
    {
        $phone = trim($phone);
        $from = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩', '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $to = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return str_replace($from, $to, $phone);
    }

    private static function normalizeTimeToHi(string $time): string
    {
        $time = trim($time);
        if (preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $time, $matches)) {
            return sprintf('%02d:%02d', (int) $matches[1], (int) $matches[2]);
        }

        return $time;
    }
}
