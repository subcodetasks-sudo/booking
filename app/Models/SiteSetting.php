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
}

