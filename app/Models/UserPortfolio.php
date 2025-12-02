<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserPortfolio extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'initial_balance',
        'balance',
        'equity', 
        'realized_pnl',
        'floating_pnl',
        'risk_mode',
        'risk_value',
        'ai_trade_enabled',
        'last_reset_at'
    ];

    protected $casts = [
        'initial_balance' => 'decimal:2',
        'balance' => 'decimal:2',
        'equity' => 'decimal:2',
        'realized_pnl' => 'decimal:2',
        'floating_pnl' => 'decimal:2',
        'risk_value' => 'decimal:2',
        'ai_trade_enabled' => 'boolean',
        'last_reset_at' => 'datetime'
    ];

    protected $appends = ['available_balance', 'total_invested'];

    // ==================== RELATIONS ====================
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(UserPosition::class, 'portfolio_id');
    }

    // ==================== CALCULATED ATTRIBUTES ====================
    public function getAvailableBalanceAttribute(): float
    {
        $totalInvested = (float) $this->positions()
            ->where('status', 'OPEN')
            ->sum('investment');
        
        return max(0, (float) $this->balance - $totalInvested);
    }

    public function getTotalInvestedAttribute(): float
    {
        return (float) $this->positions()
            ->where('status', 'OPEN')
            ->sum('investment');
    }

    // ==================== CORE PORTFOLIO METHODS ====================
    public function calculateEquity(): float
    {
        $floatingPnl = (float) $this->positions()
            ->where('status', 'OPEN')
            ->sum('floating_pnl');
        
        $equity = max(0, (float) $this->balance + $floatingPnl);
        
        // Update without triggering events to avoid double calculation
        $this->withoutEvents(function () use ($equity, $floatingPnl) {
            $this->update([
                'equity' => $equity,
                'floating_pnl' => $floatingPnl
            ]);
        });
        
        return $equity;
    }

    public function syncPortfolioValues(): bool
    {
        return DB::transaction(function () {
            $portfolio = self::lockForUpdate()->find($this->id);
            
            $floatingPnl = (float) $portfolio->positions()
                ->where('status', 'OPEN')
                ->sum('floating_pnl');
            
            $equity = max(0, (float) $portfolio->balance + $floatingPnl);
            
            return $portfolio->update([
                'floating_pnl' => $floatingPnl,
                'equity' => $equity
            ]);
        });
    }

    // ==================== BALANCE & PnL MANAGEMENT ====================
    public function updateBalance(float $newBalance): bool
    {
        $totalInvested = $this->getTotalInvestedAttribute();
        
        // Warning jika balance kurang dari invested, tapi tetap allow untuk flexibility
        if ($newBalance < $totalInvested) {
            Log::warning("Portfolio {$this->id}: Balance \${$newBalance} < Total Invested \${$totalInvested}");
        }
        
        return $this->update([
            'balance' => max(0, $newBalance),
            'equity' => max(0, $newBalance + (float) $this->floating_pnl)
        ]);
    }

    public function addPnl(float $pnl): bool
    {
        return DB::transaction(function () use ($pnl) {
            $portfolio = self::lockForUpdate()->find($this->id);
            
            $newBalance = max(0, (float) $portfolio->balance + $pnl);
            $newRealizedPnl = (float) $portfolio->realized_pnl + $pnl;
            
            $updated = $portfolio->update([
                'balance' => $newBalance,
                'realized_pnl' => $newRealizedPnl,
                'equity' => max(0, $newBalance + (float) $portfolio->floating_pnl)
            ]);

            if ($updated) {
                Log::info("Portfolio {$portfolio->id}: Added PnL \${$pnl}, New Balance: \${$newBalance}");
            }
            
            return $updated;
        });
    }

    public function updateFloatingPnl(float $newFloatingPnl): bool
    {
        return $this->update([
            'floating_pnl' => $newFloatingPnl,
            'equity' => max(0, (float) $this->balance + $newFloatingPnl)
        ]);
    }

    // ==================== RISK MANAGEMENT ====================
    public function calculateRiskAmount(float $confidence = 100): float
    {
        $baseRiskPercent = (float) $this->risk_value / 100;
        $confidenceMultiplier = max(0.5, $confidence / 100);
        $adjustedRiskPercent = $baseRiskPercent * $confidenceMultiplier;
        
        // Risk berdasarkan EQUITY (total kekayaan)
        $riskAmount = (float) $this->equity * $adjustedRiskPercent;
        
        // Tapi tidak boleh melebihi available balance
        $availableBalance = $this->getAvailableBalanceAttribute();
        $maxTrade = min($availableBalance, (float) $this->equity * 0.2); // Max 20% equity
        
        $minTrade = 10;
        $finalAmount = max($minTrade, min($riskAmount, $maxTrade));

        Log::info("Risk Calculation", [
            'portfolio_id' => $this->id,
            'equity' => $this->equity,
            'available_balance' => $availableBalance,
            'risk_amount' => $riskAmount,
            'final_amount' => $finalAmount
        ]);

        return $finalAmount;
    }

    // ==================== TRADING CHECKS ====================
    public function canOpenPosition(float $amount): bool
    {
        $availableBalance = $this->getAvailableBalanceAttribute();
        $canOpen = $availableBalance >= $amount;

        if (!$canOpen) {
            Log::warning("Cannot open position: Required \${$amount}, Available \${$availableBalance}");
        }

        return $canOpen;
    }

    public function canTrade(): bool
    {
        return $this->ai_trade_enabled && 
               (float) $this->equity > 0 && 
               $this->getAvailableBalanceAttribute() > 0;
    }

    // ==================== PORTFOLIO UTILITIES ====================
    public function getOpenPositionsCount(): int
    {
        return $this->positions()->where('status', 'OPEN')->count();
    }

    public function getUtilizationPercentage(): float
    {
        if ((float) $this->balance <= 0) {
            return 0;
        }
        
        $totalInvested = $this->getTotalInvestedAttribute();
        return ($totalInvested / (float) $this->balance) * 100;
    }

    public function getEquityChangePercentage(): float
    {
        if ((float) $this->initial_balance <= 0) {
            return 0;
        }
        
        return (((float) $this->equity - (float) $this->initial_balance) / (float) $this->initial_balance) * 100;
    }

    public function isOverUtilized(): bool
    {
        $utilization = $this->getUtilizationPercentage();
        return $utilization > 80;
    }

    public function getRecommendedPositionSize(): float
    {
        $availableBalance = $this->getAvailableBalanceAttribute();
        return min($availableBalance * 0.1, $this->calculateRiskAmount());
    }

    // ==================== DATA INTEGRITY ====================
    public function checkConsistency(): bool
    {
        $calculatedEquity = max(0, (float) $this->balance + (float) $this->floating_pnl);
        $calculatedFloatingPnl = (float) $this->positions()
            ->where('status', 'OPEN')
            ->sum('floating_pnl');
        
        $availableBalance = $this->getAvailableBalanceAttribute();
        $totalInvested = $this->getTotalInvestedAttribute();
        
        $equityConsistent = abs($calculatedEquity - (float) $this->equity) < 0.01;
        $floatingConsistent = abs($calculatedFloatingPnl - (float) $this->floating_pnl) < 0.01;
        $availableConsistent = abs(((float) $this->balance - $totalInvested) - $availableBalance) < 0.01;

        return $equityConsistent && $floatingConsistent && $availableConsistent;
    }

    public function repairData(): bool
    {
        return DB::transaction(function () {
            $portfolio = self::lockForUpdate()->find($this->id);
            
            $correctFloatingPnl = (float) $portfolio->positions()
                ->where('status', 'OPEN')
                ->sum('floating_pnl');
            
            $correctEquity = max(0, (float) $portfolio->balance + $correctFloatingPnl);
            
            $repaired = $portfolio->update([
                'floating_pnl' => $correctFloatingPnl,
                'equity' => $correctEquity
            ]);

            if ($repaired) {
                Log::info("Portfolio {$portfolio->id} data repaired", [
                    'old_equity' => $this->equity,
                    'new_equity' => $correctEquity,
                    'old_floating_pnl' => $this->floating_pnl,
                    'new_floating_pnl' => $correctFloatingPnl
                ]);
            }
            
            return $repaired;
        });
    }

    // ==================== PORTFOLIO OPERATIONS ====================
    public function reset(): bool
    {
        return DB::transaction(function () {
            $portfolio = self::lockForUpdate()->find($this->id);
            
            $resetData = [
                'balance' => $portfolio->initial_balance,
                'equity' => max(0, (float) $portfolio->initial_balance),
                'realized_pnl' => 0,
                'floating_pnl' => 0,
                'last_reset_at' => now()
            ];

            $reset = $portfolio->update($resetData);

            if ($reset) {
                $portfolio->positions()->delete();
                Log::info("Portfolio {$portfolio->id} reset to initial state");
            }
            
            return $reset;
        });
    }

    // ==================== DASHBOARD & REPORTING ====================
    public function getPortfolioSummary(): array
    {
        return [
            'balance' => (float) $this->balance,
            'equity' => (float) $this->equity,
            'available_balance' => $this->getAvailableBalanceAttribute(),
            'total_invested' => $this->getTotalInvestedAttribute(),
            'realized_pnl' => (float) $this->realized_pnl,
            'floating_pnl' => (float) $this->floating_pnl,
            'open_positions' => $this->getOpenPositionsCount(),
            'utilization_percentage' => round($this->getUtilizationPercentage(), 2),
            'equity_change_percentage' => round($this->getEquityChangePercentage(), 2),
            'risk_mode' => $this->risk_mode,
            'ai_trade_enabled' => $this->ai_trade_enabled,
            'can_trade' => $this->canTrade(),
            'is_over_utilized' => $this->isOverUtilized(),
            'recommended_position_size' => round($this->getRecommendedPositionSize(), 2)
        ];
    }

    // ==================== MODEL BOOT ====================
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($portfolio) {
            // Ensure non-negative financial values
            $portfolio->equity = max(0, (float) $portfolio->equity);
            $portfolio->balance = max(0, (float) $portfolio->balance);
            $portfolio->initial_balance = max(0, (float) $portfolio->initial_balance);
        });
    }
}