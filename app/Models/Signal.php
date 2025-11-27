<?php
// app/Models/Signal.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Signal extends Model
{
    use HasFactory;

    protected $fillable = [
        'symbol',
        'name', 
        'category',
        'enhanced_score',
        'smart_confidence',
        'current_price',
        'price_change_1h',
        'price_change_24h',
        'volume_spike_ratio',
        'volume_acceleration',
        'rsi_delta',
        'momentum_regime',
        'momentum_phase',
        'health_score',
        'trend_strength',
        'risk_level',
        'first_detection_time',
        'latest_update',
        'timestamp',
        'appearance_count',
        'open_interest',
        'oi_change',
        'funding_rate',
        'summary',
        'ai_summary',
        'ai_probability',
        'support_level',
        'resistance_level',
        'liquidity_position',
        'market_structure',
        'trend_power',
        'momentum_category',
        'funding_direction',
        'whale_behavior',
        'last_summary_count',
        'is_active_signal'
    ];

    protected $casts = [
        'enhanced_score' => 'decimal:8',
        'smart_confidence' => 'decimal:2',
        'current_price' => 'decimal:8',
        'price_change_1h' => 'decimal:4',
        'price_change_24h' => 'decimal:4',
        'volume_spike_ratio' => 'decimal:4',
        'volume_acceleration' => 'decimal:4',
        'rsi_delta' => 'decimal:4',
        'health_score' => 'decimal:2',
        'trend_strength' => 'decimal:4',
        'performance_since_first' => 'decimal:4',
        'timestamp' => 'datetime',
        'first_detection_time' => 'datetime',
    ];

    // Scope untuk sorting
    public function scopeOrderByColumn($query, $column, $direction = 'desc') // Default direction ke 'desc'
    {
        $allowedColumns = [
            'symbol', 'enhanced_score', 'smart_confidence', 'current_price',
            'price_change_1h', 'price_change_24h', 'volume_spike_ratio',
            'health_score', 'trend_strength', 'appearance_count', 'created_at',
            'first_detection_time', 'timestamp'
        ];

        if (in_array($column, $allowedColumns)) {
            return $query->orderBy($column, $direction);
        }

        // Default ke first_detection_time descending (terbaru di atas)
        return $query->orderBy('first_detection_time', 'desc');
    }
    // Scope untuk filter
    public function scopeFilter($query, $filters)
    {
        if (isset($filters['symbol'])) {
            $query->where('symbol', 'like', '%' . $filters['symbol'] . '%');
        }

        if (isset($filters['risk_level'])) {
            $query->where('risk_level', $filters['risk_level']);
        }

        if (isset($filters['momentum_regime'])) {
            $query->where('momentum_regime', $filters['momentum_regime']);
        }

        return $query;
    }
    public function needsAnalysis()
    {
        if (is_null($this->last_summary_count)) {
            return true;
        }
        return $this->appearance_count > $this->last_summary_count;
    }
}