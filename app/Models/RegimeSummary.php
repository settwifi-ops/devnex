<?php
// app/Models/RegimeSummary.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegimeSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'date', 'time', 'total_symbols', 'regime_distribution', 'market_health_score',
        'volatility_index', 'market_sentiment', 'sentiment_score', 'sentiment_indicators',
        'trend_strength', 'reversal_probability', 'next_regime_prediction',
        'prediction_confidence', 'top_dominance', 'dominance_trends'
    ];

    protected $casts = [
        'date' => 'date',
        'time' => 'datetime',
        'regime_distribution' => 'array',
        'market_health_score' => 'decimal:2',
        'volatility_index' => 'decimal:2',
        'sentiment_score' => 'decimal:2',
        'sentiment_indicators' => 'array',
        'trend_strength' => 'decimal:2',
        'reversal_probability' => 'decimal:4',
        'prediction_confidence' => 'decimal:4',
        'top_dominance' => 'array',
        'dominance_trends' => 'array'
    ];

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('date', '>=', now()->subDays($days));
    }

    public function scopeToday($query)
    {
        return $query->where('date', today());
    }

    public function getBullCountAttribute()
    {
        return $this->regime_distribution['bull'] ?? 0;
    }

    public function getBearCountAttribute()
    {
        return $this->regime_distribution['bear'] ?? 0;
    }

    public function getNeutralCountAttribute()
    {
        return $this->regime_distribution['neutral'] ?? 0;
    }

    public function getVolatileCountAttribute()
    {
        return $this->regime_distribution['volatile'] ?? 0;
    }

    public function getRegimePercentagesAttribute()
    {
        $total = $this->total_symbols;
        if ($total === 0) return [];

        return [
            'bull' => round(($this->bull_count / $total) * 100, 2),
            'bear' => round(($this->bear_count / $total) * 100, 2),
            'neutral' => round(($this->neutral_count / $total) * 100, 2),
            'volatile' => round(($this->volatile_count / $total) * 100, 2),
        ];
    }
}