<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSpinHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'spin_reward_id',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function spinReward(): BelongsTo
    {
        return $this->belongsTo(SpinReward::class);
    }
}
