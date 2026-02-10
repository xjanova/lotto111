<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'channels',
        'draw_reminder',
        'result_alert',
        'jackpot_alert',
        'hot_number_alert',
        'friend_activity',
        'promotion',
        'quiet_start',
        'quiet_end',
        'reminder_minutes',
    ];

    protected function casts(): array
    {
        return [
            'draw_reminder' => 'boolean',
            'result_alert' => 'boolean',
            'jackpot_alert' => 'boolean',
            'hot_number_alert' => 'boolean',
            'friend_activity' => 'boolean',
            'promotion' => 'boolean',
            'reminder_minutes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
