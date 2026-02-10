<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'phone',
        'otp_code',
        'purpose',
        'is_used',
        'attempts',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'is_used' => 'boolean',
            'expires_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return ! $this->is_used && ! $this->isExpired() && $this->attempts < 5;
    }

    public function markUsed(): void
    {
        $this->update(['is_used' => true]);
    }

    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }

    public function scopeLatestForPhone($query, string $phone, string $purpose)
    {
        return $query->where('phone', $phone)
            ->where('purpose', $purpose)
            ->latest('created_at');
    }
}
