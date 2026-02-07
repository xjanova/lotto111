<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsPaymentNotification extends Model
{
    protected $fillable = [
        'device_id',
        'bank',
        'type',
        'amount',
        'reference_number',
        'message',
        'raw_data',
        'status',
        'matched_transaction_id',
        'nonce',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }
}
