<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutboundDailyStat extends Model
{
    protected $fillable = [
        'date', 'domain', 'context', 'clicks', 'unique_ips',
    ];

    protected $casts = [
        'date' => 'date',
        'clicks' => 'integer',
        'unique_ips' => 'integer',
    ];
}
