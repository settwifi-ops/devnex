<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradeHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ai_decision_id',
        'position_id',
        'symbol',
        'action', 
        'qty',
        'price',
        'amount',
        'pnl',
        'pnl_percentage',
        'notes'
    ];

    protected $casts = [
        'qty' => 'decimal:8',
        'price' => 'decimal:8',
        'amount' => 'decimal:2',
        'pnl' => 'decimal:2',
        'pnl_percentage' => 'decimal:2'
    ];

    // Relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke AI decision
    public function aiDecision()
    {
        return $this->belongsTo(AiDecision::class);
    }

    // Relasi ke position
    public function position()
    {
        return $this->belongsTo(UserPosition::class);
    }

    // Scope untuk BUY trades
    public function scopeBuyTrades($query)
    {
        return $query->where('action', 'BUY');
    }

    // Scope untuk SELL trades
    public function scopeSellTrades($query)
    {
        return $query->where('action', 'SELL');
    }
}