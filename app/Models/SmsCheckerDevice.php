<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsCheckerDevice extends Model
{
    protected $fillable = [
        'device_id',
        'name',
        'status',
        'last_active_at',
    ];

    protected function casts(): array
    {
        return [
            'last_active_at' => 'datetime',
        ];
    }
}
