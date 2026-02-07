<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RateAdjustmentLog extends Model
{
    protected $fillable = [
        'target_type',
        'target_id',
        'adjusted_by',
        'admin_id',
        'field_changed',
        'old_value',
        'new_value',
        'reason',
        'context_data',
    ];
}
