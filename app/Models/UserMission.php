<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMission extends Model
{
    protected $fillable = [
        'user_id',
        'mission_id',
        'period_date',
        'progress',
        'is_completed',
        'completed_at',
        'is_claimed',
        'claimed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'is_claimed' => 'boolean',
            'completed_at' => 'datetime',
            'claimed_at' => 'datetime',
            'progress' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }
}
