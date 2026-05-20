<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        return Cache::remember("site_setting:{$key}", now()->addMinutes(10), function () use ($key, $default) {
            $row = static::query()->where('key', $key)->first();

            return $row?->value ?? $default;
        });
    }

    public static function setValue(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value === null ? null : (string) $value],
        );

        Cache::forget("site_setting:{$key}");
    }

    /** Digits-only phone from booking settings (used for wa.me links). */
    public static function bookingWhatsAppPhoneDigits(): string
    {
        $phone = (string) self::getValue('booking_whatsapp_phone', '');

        return preg_replace('/\D+/', '', $phone) ?: '';
    }

    /** WhatsApp deep link for booking confirmations — always prefers booking_whatsapp_phone. */
    public static function bookingWhatsAppUrl(): string
    {
        $digits = self::bookingWhatsAppPhoneDigits();

        if ($digits !== '') {
            return 'https://wa.me/'.$digits;
        }

        $fallback = trim((string) self::getValue('social_whatsapp_url', ''));

        return $fallback !== '' ? $fallback : 'https://wa.me/905528255694';
    }
}
