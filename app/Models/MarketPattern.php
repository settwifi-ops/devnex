<?php
// app/Models/MarketPattern.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketPattern extends Model
{
    use HasFactory;

    protected $fillable = [
        'date', 'symbol', 'pattern_type', 'pattern_name', 'pattern_data',
        'confidence', 'direction', 'price_target', 'expiry', 'is_active', 'is_triggered'
    ];

    protected $casts = [
        'date' => 'date',
        'pattern_data' => 'array',
        'confidence' => 'decimal:4',
        'price_target' => 'decimal:8',
        'expiry' => 'datetime',
        'is_active' => 'boolean',
        'is_triggered' => 'boolean'
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForSymbol($query, $symbol)
    {
        return $query->where('symbol', $symbol);
    }

    public function scopeBullish($query)
    {
        return $query->where('direction', 'bullish');
    }

    public function scopeBearish($query)
    {
        return $query->where('direction', 'bearish');
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry', '<', now());
    }

    public function markAsTriggered()
    {
        $this->update(['is_triggered' => true]);
    }

    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }
}