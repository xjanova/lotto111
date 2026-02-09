<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LotteryResult extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'lottery_round_id',
        'result_type',
        'result_value',
    ];

    public function round(): BelongsTo
    {
        return $this->belongsTo(LotteryRound::class, 'lottery_round_id');
    }
}
