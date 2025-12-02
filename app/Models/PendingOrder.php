<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ai_decision_id', 
        'symbol',
        'binance_order_id',
        'limit_price',
        'quantity',
        'side',
        'position_type',
        'expires_at',
        'status',
        'notes'
    ];

    protected $casts = [
        'limit_price' => 'decimal:8',
        'quantity' => 'decimal:8',
        'expires_at' => 'datetime'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function aiDecision()
    {
        return $this->belongsTo(AiDecision::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Helper methods
    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    public function getRemainingTime()
    {
        return now()->diffInSeconds($this->expires_at, false);
    }

    public function getRemainingTimeFormatted()
    {
        $seconds = $this->getRemainingTime();
        
        if ($seconds <= 0) {
            return 'Expired';
        }

        $minutes = floor($seconds / 60);
        $seconds = $seconds % 60;

        return sprintf('%02d:%02d', $minutes, $seconds);
    }
}