<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiskSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'description',
        'group',
    ];
}
