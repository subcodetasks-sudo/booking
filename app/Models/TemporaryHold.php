<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class TemporaryHold extends Model
{
    protected $fillable = [
        'time_slot_id',
        'reservation_date',
        'reservation_time',
        'guest_count',
        'session_key',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'reservation_date' => 'date',
            'expires_at' => 'datetime',
        ];
    }

    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }
}
