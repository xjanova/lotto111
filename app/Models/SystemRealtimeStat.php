<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemRealtimeStat extends Model
{
    protected $table = 'system_realtime_stats';

    protected $fillable = [
        'stat_key',
        'stat_value',
    ];
}
