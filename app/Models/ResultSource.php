<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResultSource extends Model
{
    protected $fillable = [
        'lottery_type_id',
        'provider',
        'name',
        'mode',
        'source_url',
        'fallback_url',
        'scrape_config',
        'schedule',
        'is_active',
        'priority',
        'retry_count',
        'retry_delay_seconds',
        'timeout_seconds',
        'last_fetched_at',
        'last_status',
        'last_error',
    ];

    protected function casts(): array
    {
        return [
            'scrape_config' => 'array',
            'schedule' => 'array',
            'is_active' => 'boolean',
            'last_fetched_at' => 'datetime',
        ];
    }

    public function lotteryType(): BelongsTo
    {
        return $this->belongsTo(LotteryType::class);
    }

    public function fetchLogs(): HasMany
    {
        return $this->hasMany(ResultFetchLog::class);
    }

    public function isAuto(): bool
    {
        return $this->mode === 'auto';
    }

    public function isManual(): bool
    {
        return $this->mode === 'manual';
    }

    public function markSuccess(): void
    {
        $this->update([
            'last_fetched_at' => now(),
            'last_status' => 'success',
            'last_error' => null,
        ]);
    }

    public function markFailed(string $error): void
    {
        $this->update([
            'last_fetched_at' => now(),
            'last_status' => 'failed',
            'last_error' => $error,
        ]);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAutoMode($query)
    {
        return $query->where('mode', 'auto');
    }

    public function scopeByProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    public function getConfig(string $key, mixed $default = null): mixed
    {
        return data_get($this->scrape_config, $key, $default);
    }
}
