<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateCommission extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'from_user_id',
        'ticket_id',
        'bet_amount',
        'commission_rate',
        'commission',
        'status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'bet_amount' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'commission' => 'decimal:2',
            'paid_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
