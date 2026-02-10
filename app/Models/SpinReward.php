<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpinReward extends Model
{
    protected $fillable = [
        'name',
        'type',
        'value',
        'probability',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'probability' => 'float',
            'is_active' => 'boolean',
        ];
    }
}
