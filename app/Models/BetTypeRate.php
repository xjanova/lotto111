<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BetTypeRate extends Model
{
    protected $fillable = [
        'lottery_type_id',
        'bet_type_id',
        'rate',
        'min_amount',
        'max_amount',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'min_amount' => 'decimal:2',
            'max_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function lotteryType(): BelongsTo
    {
        return $this->belongsTo(LotteryType::class);
    }

    public function betType(): BelongsTo
    {
        return $this->belongsTo(BetType::class);
    }
}
