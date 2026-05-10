<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    protected $fillable = [
        'slot_date',
        'start_time',
        'end_time',
        'capacity',
        'reserved_guests',
        'held_guests',
        'is_closed_manually',
    ];

    protected function casts(): array
    {
        return [
            'slot_date' => 'date',
            'is_closed_manually' => 'boolean',
        ];
    }

    public function temporaryHolds(): HasMany
    {
        return $this->hasMany(TemporaryHold::class);
    }
}
