<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRiskProfile extends Model
{
    protected $fillable = [
        'user_id',
        'risk_level',
        'risk_score',
        'current_win_rate',
        'win_rate_override',
        'rate_adjustment_percent',
        'is_auto_adjust',
        'total_bet_amount',
        'total_win_amount',
        'total_deposit',
        'total_tickets',
        'total_wins',
        'consecutive_wins',
        'consecutive_losses',
        'bets_per_minute',
        'today_bet_amount',
        'today_win_amount',
        'today_payout',
        'today_tickets',
        'net_profit_for_system',
        'blocked_numbers',
        'max_bet_per_ticket',
        'max_bet_per_number',
        'max_payout_per_day',
        'max_payout_per_ticket',
        'last_bet_at',
        'last_reviewed_by',
        'last_reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'last_bet_at' => 'datetime',
            'last_reviewed_at' => 'datetime',
            'is_auto_adjust' => 'boolean',
            'total_bet_amount' => 'decimal:2',
            'total_win_amount' => 'decimal:2',
            'net_profit_for_system' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
