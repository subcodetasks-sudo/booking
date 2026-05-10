<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'ingredients_ar',
        'ingredients_en',
        'image',
        'price',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function extras(): HasMany
    {
        return $this->hasMany(ProductExtra::class);
    }

    public function reservationAddonCatalog(): HasOne
    {
        return $this->hasOne(ReservationAddon::class);
    }
}
