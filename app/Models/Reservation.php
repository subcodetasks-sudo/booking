<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'reservation_code',
        'reservation_date',
        'reservation_time',
        'guest_count',
        'status',
        'order_status',
        'occasion_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'allergies_notes',
        'reservation_notes',
        'addons_total',
        'items_total',
        'total_amount',
        'confirmed_at',
        'cancelled_at',
        'cancel_reason',
    ];

    protected function casts(): array
    {
        return [
            'reservation_date' => 'date',
            'addons_total' => 'decimal:2',
            'items_total' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function occasion(): BelongsTo
    {
        return $this->belongsTo(Occasion::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReservationItem::class);
    }

    public function addons(): BelongsToMany
    {
        return $this->belongsToMany(ReservationAddon::class)
            ->withPivot(['addon_name', 'addon_price', 'quantity', 'line_total'])
            ->withTimestamps();
    }

    public function dietaryOptions(): BelongsToMany
    {
        return $this->belongsToMany(DietaryOption::class)
            ->withPivot(['scope'])
            ->withTimestamps();
    }
}
