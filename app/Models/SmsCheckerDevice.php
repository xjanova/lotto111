<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SmsCheckerDevice extends Model
{
    protected $fillable = [
        'device_id',
        'name',
        'status',
        'last_active_at',
        'ip_address',
        'secret_key',
        'api_key',
        'platform',
        'app_version',
    ];

    protected function casts(): array
    {
        return [
            'last_active_at' => 'datetime',
        ];
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(SmsPaymentNotification::class, 'device_id', 'device_id');
    }

    public static function findByApiKey(string $apiKey): ?self
    {
        return static::where('api_key', $apiKey)->first();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public static function generateApiKey(): string
    {
        return 'ak_' . Str::random(32);
    }

    public static function generateSecretKey(): string
    {
        return 'sk_' . Str::random(48);
    }
}
