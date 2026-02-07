<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfitSnapshot extends Model
{
    protected $fillable = [
        'period_type',
        'period_start',
        'period_end',
        'total_bet_amount',
        'total_payout',
        'total_deposit',
        'total_withdraw',
        'gross_profit',
        'net_profit',
        'margin_percent',
        'active_users',
        'new_users',
        'total_tickets',
        'total_wins',
        'avg_win_rate',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'datetime',
            'period_end' => 'datetime',
        ];
    }
}
