<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'portfolio_id', // KOLOM BARU YANG PENTING
        'user_id',
        'ai_decision_id',
        'symbol',
        'position_type', // LONG atau SHORT
        'qty',
        'avg_price',
        'current_price',
        'investment',
        'floating_pnl',
        'pnl_percentage',
        'take_profit',
        'stop_loss',
        'status',
        'opened_at',
        'closed_at'
    ];

    protected $casts = [
        'qty' => 'decimal:8',
        'avg_price' => 'decimal:8',
        'current_price' => 'decimal:8',
        'investment' => 'decimal:2',
        'floating_pnl' => 'decimal:2',
        'pnl_percentage' => 'decimal:2',
        'take_profit' => 'decimal:8',
        'stop_loss' => 'decimal:8',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime'
    ];

    // Relasi ke portfolio - RELASI BARU YANG PENTING
    public function portfolio()
    {
        return $this->belongsTo(UserPortfolio::class);
    }

    // Relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke ai_decision
    public function aiDecision()
    {
        return $this->belongsTo(AiDecision::class);
    }

    // Scope untuk posisi open
    public function scopeOpen($query)
    {
        return $query->where('status', 'OPEN');
    }

    // Scope untuk posisi long
    public function scopeLong($query)
    {
        return $query->where('position_type', 'LONG');
    }

    // Scope untuk posisi short
    public function scopeShort($query)
    {
        return $query->where('position_type', 'SHORT');
    }

    // Update floating PnL dengan support LONG/SHORT - DIPERBAIKI
    public function updateFloatingPnl(float $currentPrice): bool
    {
        $this->current_price = $currentPrice;
        
        // Hitung floating PnL berdasarkan position type
        if ($this->position_type === 'LONG') {
            $this->floating_pnl = ($currentPrice - $this->avg_price) * $this->qty;
        } else { // SHORT
            $this->floating_pnl = ($this->avg_price - $currentPrice) * $this->qty;
        }
        
        // Hitung PnL percentage
        if ($this->investment > 0) {
            $this->pnl_percentage = ($this->floating_pnl / $this->investment) * 100;
        }
        
        return $this->save();
    }

    // Close position dengan concurrency protection - DIPERBAIKI
    public function close(float $closePrice = null): bool
    {
        return DB::transaction(function () use ($closePrice) {
            // Lock row untuk mencegah race condition
            $position = self::lockForUpdate()->find($this->id);
            
            if ($position->status === 'CLOSED') {
                return true; // Already closed
            }
            
            $finalPrice = $closePrice ?: $position->current_price;
            
            // Hitung final PnL
            if ($position->position_type === 'LONG') {
                $finalPnl = ($finalPrice - $position->avg_price) * $position->qty;
            } else { // SHORT
                $finalPnl = ($position->avg_price - $finalPrice) * $position->qty;
            }
            
            // Update position status dan final PnL
            $position->update([
                'status' => 'CLOSED',
                'current_price' => $finalPrice,
                'floating_pnl' => $finalPnl,
                'closed_at' => now()
            ]);
            
            // Update portfolio: kembalikan investment + PnL
            if ($position->portfolio) {
                $position->portfolio->returnInvestment($position->investment, $finalPnl);
            }

            
            return true;
        });
    }

    // Check if position hit take profit
    public function isTakeProfitHit(float $currentPrice): bool
    {
        if (!$this->take_profit) return false;
        
        if ($this->position_type === 'LONG') {
            return $currentPrice >= $this->take_profit;
        } else { // SHORT
            return $currentPrice <= $this->take_profit;
        }
    }

    // Check if position hit stop loss
    public function isStopLossHit(float $currentPrice): bool
    {
        if (!$this->stop_loss) return false;
        
        if ($this->position_type === 'LONG') {
            return $currentPrice <= $this->stop_loss;
        } else { // SHORT
            return $currentPrice >= $this->stop_loss;
        }
    }

    // Get current PnL percentage
    public function getCurrentPnlPercentage(): float
    {
        if ($this->investment <= 0) return 0;
        
        return ($this->floating_pnl / $this->investment) * 100;
    }

    // Check if position is profitable
    public function isProfitable(): bool
    {
        return $this->floating_pnl > 0;
    }
}