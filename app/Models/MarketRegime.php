<?php
// app/Models/MarketRegime.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketRegime extends Model
{
    use HasFactory;

    protected $fillable = [
        'timestamp', 'date', 'symbol', 'price', 'volume', 'market_cap',
        'volatility_24h', 'rsi_14', 'macd', 'bollinger_upper', 'bollinger_lower',
        'regime', 'regime_confidence', 'regime_metadata', 'dominance_score',
        'sentiment_score', 'predicted_trend', 'anomaly_score'
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'date' => 'date',
        'price' => 'decimal:8',
        'volume' => 'decimal:8',
        'market_cap' => 'decimal:2',
        'volatility_24h' => 'decimal:6',
        'rsi_14' => 'decimal:2',
        'macd' => 'decimal:6',
        'bollinger_upper' => 'decimal:8',
        'bollinger_lower' => 'decimal:8',
        'regime_confidence' => 'decimal:4',
        'dominance_score' => 'decimal:2',
        'sentiment_score' => 'decimal:2',
        'predicted_trend' => 'decimal:4',
        'anomaly_score' => 'decimal:4',
        'regime_metadata' => 'array'
    ];

    // Scopes for common queries
    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function scopeForSymbol($query, $symbol)
    {
        return $query->where('symbol', $symbol);
    }

    public function scopeByRegime($query, $regime)
    {
        return $query->where('regime', $regime);
    }

    public function scopeTopDominance($query, $limit = 10)
    {
        return $query->orderBy('dominance_score', 'desc')->limit($limit);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('date', '>=', now()->subDays($days));
    }

    // Relationships
    public function dominanceHistory()
    {
        return $this->hasMany(DominanceHistory::class, 'symbol', 'symbol');
    }
}