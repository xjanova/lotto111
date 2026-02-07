<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NumberExposure extends Model
{
    protected $fillable = [
        'lottery_round_id',
        'bet_type_id',
        'number',
        'total_bet_amount',
        'bet_count',
        'potential_payout',
        'effective_rate',
        'rate_reduction_percent',
        'risk_level',
        'is_blocked',
    ];

    protected function casts(): array
    {
        return [
            'total_bet_amount' => 'decimal:2',
            'potential_payout' => 'decimal:2',
            'effective_rate' => 'decimal:2',
            'is_blocked' => 'boolean',
        ];
    }
}
