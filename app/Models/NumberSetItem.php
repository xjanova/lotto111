<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NumberSetItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'number_set_id',
        'bet_type_id',
        'number',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function numberSet(): BelongsTo
    {
        return $this->belongsTo(NumberSet::class);
    }

    public function betType(): BelongsTo
    {
        return $this->belongsTo(BetType::class);
    }
}
