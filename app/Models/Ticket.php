<?php

namespace App\Models;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lottery_round_id',
        'bet_type_id',
        'ticket_code',
        'number',
        'amount',
        'rate',
        'total_amount',
        'total_win',
        'win_amount',
        'status',
        'bet_at',
        'result_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'amount' => 'decimal:2',
            'rate' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'total_win' => 'decimal:2',
            'win_amount' => 'decimal:2',
            'bet_at' => 'datetime',
            'result_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function round(): BelongsTo
    {
        return $this->belongsTo(LotteryRound::class, 'lottery_round_id');
    }

    public function betType(): BelongsTo
    {
        return $this->belongsTo(BetType::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TicketItem::class);
    }

    public function isPending(): bool
    {
        return $this->status === TicketStatus::Pending;
    }

    public function isWon(): bool
    {
        return $this->status === TicketStatus::Won;
    }

    public function scopePending($query)
    {
        return $query->where('status', TicketStatus::Pending);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('bet_at', today());
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
