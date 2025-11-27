<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AISignal extends Model
{
    use HasFactory;

    protected $table = 'ai_signals';

    protected $fillable = [
        'symbol',
        'name',
        'action',
        'confidence',
        'current_price',
        'target_price',        // ✅ TAMBAHKAN
        'signal_score',
        'risk_level',
        'health_score',
        'volume_spike',
        'momentum_regime',
        'rsi_delta',
        'signal_time',
        'metadata',           // ✅ TAMBAHKAN
        'is_read'            // ✅ TAMBAHKAN
    ];

    protected $casts = [
        'current_price' => 'decimal:8',
        'target_price' => 'decimal:8',    // ✅ TAMBAHKAN
        'confidence' => 'decimal:2',      // ✅ SESUAI MIGRATION
        'signal_score' => 'decimal:2',    // ✅ SESUAI MIGRATION
        'health_score' => 'integer',
        'volume_spike' => 'decimal:2',
        'rsi_delta' => 'decimal:4',
        'signal_time' => 'datetime',
        'metadata' => 'array',            // ✅ TAMBAHKAN
        'is_read' => 'boolean'           // ✅ TAMBAHKAN
    ];

    public function userNotifications(): HasMany
    {
        return $this->hasMany(UserNotification::class);
    }

    public function getActionColor()
    {
        return match($this->action) {
            'BUY' => 'text-green-600',
            'SELL' => 'text-red-600',
            default => 'text-yellow-600'
        };
    }

    public function getActionBgColor()
    {
        return match($this->action) {
            'BUY' => 'bg-green-100',
            'SELL' => 'bg-red-100',
            default => 'bg-yellow-100'
        };
    }

    public function getFormattedConfidence()
    {
        return $this->confidence . '%';
    }

    public function getFormattedPrice()
    {
        return number_format($this->current_price, $this->current_price < 1 ? 4 : 2);
    }
}