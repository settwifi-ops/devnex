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
        'binance_order_id',   // main order
        'main_order_id',
        'sl_order_id',
        'tp_order_id',

        // pricing
        'limit_price',
        'entry_price',
        'stop_loss_price',
        'take_profit_price',

        // quantity & direction
        'quantity',
        'side',
        'position_type',

        // status
        'status',
        'is_active',
        'is_manual',

        // timing
        'expires_at',
        'filled_at',
        'closed_at',

        'notes',
        'error_message',
    ];

    protected $casts = [
        'limit_price'       => 'decimal:8',
        'entry_price'       => 'decimal:8',
        'stop_loss_price'   => 'decimal:8',
        'take_profit_price' => 'decimal:8',
        'quantity'          => 'decimal:8',

        'expires_at' => 'datetime',
        'filled_at'  => 'datetime',
        'closed_at'  => 'datetime',

        'is_active' => 'boolean',
        'is_manual' => 'boolean',
    ];

    // =============================
    // Relationships
    // =============================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function aiDecision()
    {
        return $this->belongsTo(AiDecision::class);
    }

    // =============================
    // Scopes
    // =============================

    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    public function scopeFilled($query)
    {
        return $query->where('status', 'FILLED');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    // =============================
    // Status Helpers
    // =============================

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isFilled(): bool
    {
        return $this->status === 'FILLED';
    }

    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    public function isClosed(): bool
    {
        return $this->closed_at !== null;
    }

    public function isSlActive(): bool
    {
        return !empty($this->sl_order_id);
    }

    public function isTpActive(): bool
    {
        return !empty($this->tp_order_id);
    }

    // =============================
    // UI Helper
    // =============================

    public function getFormattedStatusAttribute()
    {
        $colors = [
            'PENDING'           => 'orange',
            'FILLED'            => 'green',
            'CANCELLED'         => 'red',
            'EXPIRED'           => 'gray',
            'PARTIALLY_FILLED'  => 'yellow',
        ];

        $status = strtoupper($this->status);

        return [
            'text' => $status,
            'color' => $colors[$status] ?? 'gray',
        ];
    }

    // =============================
    // Time helpers
    // =============================

    public function getRemainingTime()
    {
        if (!$this->expires_at) return 0;
        return now()->diffInSeconds($this->expires_at, false);
    }

    public function getRemainingTimeFormatted()
    {
        $seconds = $this->getRemainingTime();
        if ($seconds <= 0) return 'Expired';

        $m = floor($seconds / 60);
        $s = $seconds % 60;
        return sprintf('%02d:%02d', $m, $s);
    }
}
