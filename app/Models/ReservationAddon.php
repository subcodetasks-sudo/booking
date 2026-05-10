<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;

class ReservationAddon extends Model
{
    protected $fillable = [
        'product_id',
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'image',
        'price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function reservations(): BelongsToMany
    {
        return $this->belongsToMany(Reservation::class)
            ->withPivot(['addon_name', 'addon_price', 'quantity', 'line_total'])
            ->withTimestamps();
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
