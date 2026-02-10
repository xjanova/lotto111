<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mission extends Model
{
    protected $fillable = [
        'title',
        'description',
        'type',
        'condition_type',
        'condition_value',
        'reward_xp',
        'reward_credit',
        'reward_spins',
        'reward_badge_id',
        'is_active',
        'start_at',
        'end_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'reward_xp' => 'integer',
            'reward_credit' => 'decimal:2',
            'reward_spins' => 'integer',
            'condition_value' => 'float',
        ];
    }
}
