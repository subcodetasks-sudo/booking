<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatingHour extends Model
{
    protected $fillable = [
        'day_of_week',
        'open_time',
        'close_time',
        'slot_duration_minutes',
        'capacity_per_slot',
        'is_open',
    ];

    protected function casts(): array
    {
        return [
            'is_open' => 'boolean',
        ];
    }
}
