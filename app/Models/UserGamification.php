<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserGamification extends Model
{
    protected $fillable = [
        'user_id',
        'xp',
        'login_streak',
        'longest_streak',
        'last_daily_claim',
        'spin_count',
    ];

    protected function casts(): array
    {
        return [
            'last_daily_claim' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
