<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BetLimit extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'lottery_round_id',
        'bet_type_id',
        'number',
        'max_amount',
    ];

    protected function casts(): array
    {
        return [
            'max_amount' => 'decimal:2',
        ];
    }

    public function round(): BelongsTo
    {
        return $this->belongsTo(LotteryRound::class, 'lottery_round_id');
    }

    public function betType(): BelongsTo
    {
        return $this->belongsTo(BetType::class);
    }

    public function isBlocked(): bool
    {
        return (float) $this->max_amount === 0.0;
    }
}
