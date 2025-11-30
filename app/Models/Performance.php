<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Performance extends Model
{
    use HasFactory;

    protected $fillable = [
        'symbol',
        'performance_since_first',
        'health_score',
        'appearance_count',
        'trend_strength',
        'hours_since_first',
        'momentum_phase',
        'risk_level',
        'last_seen',
        'current_price',
        'is_active',
        'rank',
        'data_timestamp',
        'first_detection_time'
    ];

    protected $casts = [
        'last_seen' => 'datetime',
        'data_timestamp' => 'datetime',
        'first_detection_time' => 'datetime',
        'is_active' => 'boolean',
        'performance_since_first' => 'decimal:2',
        'current_price' => 'decimal:4',
        'hours_since_first' => 'decimal:2'
    ];
}