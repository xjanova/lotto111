<?php

namespace App\Models;

use App\Enums\RoundStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LotteryRound extends Model
{
    use HasFactory;

    protected $fillable = [
        'lottery_type_id',
        'round_code',
        'round_number',
        'status',
        'open_at',
        'close_at',
        'result_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => RoundStatus::class,
            'open_at' => 'datetime',
            'close_at' => 'datetime',
            'result_at' => 'datetime',
        ];
    }

    public function lotteryType(): BelongsTo
    {
        return $this->belongsTo(LotteryType::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(LotteryResult::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function betLimits(): HasMany
    {
        return $this->hasMany(BetLimit::class);
    }

    public function numberExposures(): HasMany
    {
        return $this->hasMany(NumberExposure::class);
    }

    public function isOpen(): bool
    {
        return $this->status === RoundStatus::Open;
    }

    public function isClosed(): bool
    {
        return $this->status === RoundStatus::Closed;
    }

    public function hasResult(): bool
    {
        return $this->status === RoundStatus::Resulted;
    }

    public function scopeOpen($query)
    {
        return $query->where('status', RoundStatus::Open);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [RoundStatus::Upcoming, RoundStatus::Open]);
    }

    public function getResultValue(string $resultType): ?string
    {
        return $this->results->where('result_type', $resultType)->first()?->result_value;
    }
}
