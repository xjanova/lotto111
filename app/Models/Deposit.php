<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deposit extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'unique_amount',
        'unique_amount_id',
        'method',
        'status',
        'expires_at',
        'matched_at',
        'credited_at',
        'cancelled_at',
        'sms_notification_id',
        'matched_bank',
        'matched_reference',
        'manual_matched_by',
        'transaction_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'unique_amount' => 'decimal:2',
            'expires_at' => 'datetime',
            'matched_at' => 'datetime',
            'credited_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
