<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'ticket_id',
        'bet_type_id',
        'number',
        'amount',
        'rate',
        'win_amount',
        'is_won',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'rate' => 'decimal:2',
            'win_amount' => 'decimal:2',
            'is_won' => 'boolean',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function betType(): BelongsTo
    {
        return $this->belongsTo(BetType::class);
    }
}
