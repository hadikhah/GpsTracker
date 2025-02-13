<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GpsTrack extends Model
{
    protected $fillable = [
        'latitude',
        'longitude',
        'speed',
        'status',
        'date_time',
        'imei',
        'stopped',
        'stoppage_amount',
        'is_start_point',
        'is_end_point'
    ];

    protected $casts = [
        'date_time' => 'datetime',
        'stopped' => 'boolean',
        'is_start_point' => 'boolean',
        'is_end_point' => 'boolean'
    ];
}
