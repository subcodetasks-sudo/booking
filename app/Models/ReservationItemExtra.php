<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class ReservationItemExtra extends Model
{
    protected $fillable = [
        'reservation_item_id',
        'product_extra_id',
        'extra_name',
        'extra_price',
        'quantity',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'extra_price' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function reservationItem(): BelongsTo
    {
        return $this->belongsTo(ReservationItem::class);
    }

    public function productExtra(): BelongsTo
    {
        return $this->belongsTo(ProductExtra::class);
    }
}
