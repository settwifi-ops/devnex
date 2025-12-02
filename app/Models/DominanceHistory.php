<?php
// app/Models/DominanceHistory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DominanceHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'date', 'symbol', 'dominance_score', 'rank', 'market_cap',
        'volume_24h', 'price_change_7d', 'rank_change'
    ];

    protected $casts = [
        'date' => 'date',
        'dominance_score' => 'decimal:2',
        'market_cap' => 'decimal:2',
        'volume_24h' => 'decimal:2',
        'price_change_7d' => 'decimal:6'
    ];

    public function scopeTopRanked($query, $limit = 10)
    {
        return $query->where('rank', '<=', $limit)->orderBy('rank');
    }

    public function scopeForSymbol($query, $symbol)
    {
        return $query->where('symbol', $symbol);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('date', '>=', now()->subDays($days));
    }

    // Relationship to MarketRegime
    public function marketRegime()
    {
        return $this->belongsTo(MarketRegime::class, 'symbol', 'symbol');
    }
}