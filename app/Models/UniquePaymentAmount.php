<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UniquePaymentAmount extends Model
{
    protected $fillable = [
        'base_amount',
        'unique_amount',
        'transaction_id',
        'transaction_type',
        'status',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'base_amount' => 'decimal:2',
            'unique_amount' => 'decimal:2',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Generate a unique payment amount (stub implementation)
     */
    public static function generate(float $baseAmount, int $transactionId, string $transactionType, int $expiryMinutes = 30): ?self
    {
        // In production, this generates a unique decimal suffix
        $suffix = rand(1, 99) / 100;
        $uniqueAmount = $baseAmount + $suffix;

        return static::create([
            'base_amount' => $baseAmount,
            'unique_amount' => $uniqueAmount,
            'transaction_id' => $transactionId,
            'transaction_type' => $transactionType,
            'status' => 'reserved',
            'expires_at' => now()->addMinutes($expiryMinutes),
        ]);
    }
}
