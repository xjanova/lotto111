<?php

namespace App\Models;

use App\Enums\LotteryCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LotteryType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'country',
        'icon',
        'is_active',
        'sort_order',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'category' => LotteryCategory::class,
            'is_active' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(LotteryRound::class);
    }

    public function betTypeRates(): HasMany
    {
        return $this->hasMany(BetTypeRate::class);
    }

    public function activeRounds(): HasMany
    {
        return $this->rounds()->where('status', 'open');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, LotteryCategory $category)
    {
        return $query->where('category', $category);
    }
}
