<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BetType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'digit_count',
        'description',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function rates(): HasMany
    {
        return $this->hasMany(BetTypeRate::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getRateForLottery(int $lotteryTypeId): ?BetTypeRate
    {
        return $this->rates()->where('lottery_type_id', $lotteryTypeId)->first();
    }
}
