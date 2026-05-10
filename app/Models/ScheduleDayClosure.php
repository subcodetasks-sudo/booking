<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleDayClosure extends Model
{
    protected $fillable = [
        'closure_date',
    ];

    protected function casts(): array
    {
        return [
            'closure_date' => 'date',
        ];
    }
}
