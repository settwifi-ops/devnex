<?php
// app/Models/User.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'trial_ends_at', 
        'premium_ends_at', 'subscription_tier', 'login_token',
        'country_code', 'is_trial_used', 'real_trading_subscribed' // ✅ TAMBAH INI
    ];

    protected $hidden = [
        'password', 'remember_token', 'login_token'
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'premium_ends_at' => 'datetime',
        'real_trading_subscribed' => 'boolean' // ✅ TAMBAH INI
    ];

    // ==================== RELATIONSHIPS ====================
    
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Relationship to Portfolio model
     */
    public function portfolio()
    {
        return $this->hasOne(UserPortfolio::class);
    }

    /**
     * ✅ RELATIONSHIP DENGAN BINANCE ACCOUNTS
     */
    public function binanceAccounts()
    {
        return $this->hasMany(UserBinanceAccount::class);
    }

    /**
     * ✅ RELATIONSHIP DENGAN POSITIONS
     */
    public function positions()
    {
        return $this->hasMany(UserPosition::class);
    }

    /**
     * ✅ RELATIONSHIP DENGAN TRADE HISTORY
     */
    public function tradeHistory()
    {
        return $this->hasMany(TradeHistory::class);
    }

    /**
     * ✅ RELATIONSHIP DENGAN PENDING ORDERS
     */
    public function pendingOrders()
    {
        return $this->hasMany(PendingOrder::class);
    }

    // ==================== REAL TRADING METHODS ====================

    /**
     * ✅ GET ACTIVE BINANCE ACCOUNT
     */
    public function getActiveBinanceAccountAttribute()
    {
        return $this->binanceAccounts()
            ->where('is_active', true)
            ->where('verification_status', 'verified')
            ->first();
    }

    /**
     * ✅ CHECK IF USER CAN REAL TRADE
     */
    public function canRealTrade()
    {
        return $this->real_trading_subscribed && 
               $this->portfolio &&
               $this->portfolio->canRealTrade() &&
               $this->getActiveBinanceAccountAttribute();
    }

    /**
     * ✅ GET REAL TRADING STATUS
     */
    public function getRealTradingStatusAttribute()
    {
        if (!$this->real_trading_subscribed) {
            return 'not_subscribed';
        }

        if (!$this->portfolio || !$this->portfolio->real_trading_active) {
            return 'not_connected';
        }

        if (!$this->portfolio->real_trading_enabled) {
            return 'paused';
        }

        return 'active';
    }

    /**
     * ✅ GET OPEN POSITIONS COUNT
     */
    public function getOpenPositionsCountAttribute()
    {
        return $this->positions()
            ->where('status', 'OPEN')
            ->count();
    }

    /**
     * ✅ GET REAL OPEN POSITIONS COUNT
     */
    public function getRealOpenPositionsCountAttribute()
    {
        return $this->positions()
            ->where('status', 'OPEN')
            ->where('is_real_trade', true)
            ->count();
    }

    /**
     * ✅ GET PENDING ORDERS COUNT
     */
    public function getPendingOrdersCountAttribute()
    {
        return $this->pendingOrders()
            ->where('status', 'PENDING')
            ->count();
    }

    /**
     * ✅ GET TOTAL REALIZED PnL
     */
    public function getTotalRealizedPnlAttribute()
    {
        $virtualPnl = $this->portfolio ? (float) $this->portfolio->realized_pnl : 0;
        $realPnl = $this->portfolio ? (float) $this->portfolio->real_realized_pnl : 0;
        
        return [
            'virtual' => $virtualPnl,
            'real' => $realPnl,
            'total' => $virtualPnl + $realPnl
        ];
    }

    /**
     * ✅ GET TRADING SUMMARY
     */
    public function getTradingSummaryAttribute()
    {
        return [
            'virtual' => [
                'balance' => $this->portfolio ? (float) $this->portfolio->balance : 0,
                'equity' => $this->portfolio ? (float) $this->portfolio->equity : 0,
                'open_positions' => $this->getOpenPositionsCountAttribute(),
                'realized_pnl' => $this->portfolio ? (float) $this->portfolio->realized_pnl : 0,
                'can_trade' => $this->portfolio ? $this->portfolio->canTrade() : false
            ],
            'real' => [
                'subscribed' => (bool) $this->real_trading_subscribed,
                'connected' => $this->portfolio ? $this->portfolio->real_trading_active : false,
                'enabled' => $this->portfolio ? $this->portfolio->real_trading_enabled : false,
                'balance' => $this->portfolio ? (float) $this->portfolio->real_balance : 0,
                'equity' => $this->portfolio ? (float) $this->portfolio->real_equity : 0,
                'open_positions' => $this->getRealOpenPositionsCountAttribute(),
                'realized_pnl' => $this->portfolio ? (float) $this->portfolio->real_realized_pnl : 0,
                'pending_orders' => $this->getPendingOrdersCountAttribute(),
                'can_trade' => $this->canRealTrade(),
                'status' => $this->getRealTradingStatusAttribute()
            ]
        ];
    }

    // ==================== TRIAL METHODS ====================
    
    /**
     * Cek apakah user sedang dalam masa trial yang aktif
     */
    public function hasActiveTrial()
    {
        return $this->trial_ends_at && 
               $this->trial_ends_at->isFuture() && 
               $this->subscription_tier === 'trial';
    }

    /**
     * Cek apakah trial user sudah kadaluarsa
     */
    public function hasExpiredTrial()
    {
        return $this->trial_ends_at && 
               $this->trial_ends_at->isPast() && 
               $this->subscription_tier === 'trial';
    }

    /**
     * Cek apakah user pernah menggunakan trial
     */
    public function hasUsedTrial()
    {
        return $this->is_trial_used || $this->trial_ends_at !== null;
    }

    /**
     * Get trial progress percentage
     */
    public function getTrialProgressPercent()
    {
        if (!$this->trial_ends_at) {
            return 0;
        }

        $now = Carbon::now();
        $trialStart = $this->created_at;
        $trialEnd = Carbon::parse($this->trial_ends_at);

        if ($now->greaterThan($trialEnd)) {
            return 100;
        }

        if ($now->lessThan($trialStart)) {
            return 0;
        }

        $totalTrialDuration = $trialStart->diffInSeconds($trialEnd);
        $elapsedDuration = $trialStart->diffInSeconds($now);

        if ($totalTrialDuration > 0) {
            $progress = ($elapsedDuration / $totalTrialDuration) * 100;
            return min(100, max(0, round($progress, 2)));
        }

        return 0;
    }

    /**
     * Get remaining trial days
     */
    public function getRemainingTrialDays()
    {
        if (!$this->trial_ends_at) {
            return 0;
        }

        $now = Carbon::now();
        $trialEnd = Carbon::parse($this->trial_ends_at);

        if ($now->greaterThanOrEqualTo($trialEnd)) {
            return 0;
        }

        return $now->diffInDays($trialEnd, false);
    }

    // ==================== PREMIUM METHODS ====================
    
    /**
     * Cek apakah user memiliki premium aktif
     */
    public function hasActivePremium()
    {
        return $this->subscription_tier === 'premium' && 
               $this->premium_ends_at && 
               $this->premium_ends_at->isFuture();
    }

    /**
     * Cek apakah premium user sudah kadaluarsa
     */
    public function hasExpiredPremium()
    {
        return $this->subscription_tier === 'premium' && 
               $this->premium_ends_at && 
               $this->premium_ends_at->isPast();
    }

    // ==================== ACCESS CONTROL METHODS ====================
    
    /**
     * Method utama untuk cek akses ke konten premium
     */
    public function canAccessPremium()
    {
        if ($this->hasActivePremium()) {
            return true;
        }
        
        if ($this->hasActiveTrial()) {
            return true;
        }
        
        return false;
    }

    /**
     * Cek status user untuk keperluan middleware/message
     */
    public function getAccessStatus()
    {
        if ($this->hasActivePremium()) {
            return 'premium_active';
        }
        
        if ($this->hasActiveTrial()) {
            return 'trial_active';
        }
        
        if ($this->hasExpiredTrial()) {
            return 'trial_expired';
        }
        
        if ($this->hasExpiredPremium()) {
            return 'premium_expired';
        }
        
        return 'free';
    }

    // ==================== SUBSCRIPTION MANAGEMENT ====================
    
    /**
     * Mulai trial untuk user
     */
    public function startTrial($days = 1)
    {
        $this->update([
            'trial_ends_at' => now()->addDays($days),
            'is_trial_used' => true,
            'subscription_tier' => 'trial'
        ]);

        \Log::info('Trial started for user:', [
            'user_id' => $this->id,
            'trial_ends_at' => $this->trial_ends_at
        ]);
    }

    /**
     * Aktifkan premium untuk user
     */
    public function activatePremium($endDate)
    {
        $this->update([
            'subscription_tier' => 'premium',
            'premium_ends_at' => $endDate,
            'trial_ends_at' => null
        ]);
        
        \Log::info('User premium activated:', [
            'user_id' => $this->id,
            'premium_ends_at' => $endDate
        ]);
    }

    /**
     * Turunkan user ke status free
     */
    public function downgradeToFree()
    {
        $this->update([
            'subscription_tier' => 'free',
            'premium_ends_at' => null,
            'trial_ends_at' => null
        ]);
        
        \Log::info('User downgraded to free:', ['user_id' => $this->id]);
    }

    /**
     * Get active subscription
     */
    public function getActiveSubscription()
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where('end_date', '>', now())
            ->first();
    }

    /**
     * Cek apakah user perlu di-redirect ke pricing
     */
    public function shouldRedirectToPricing()
    {
        $status = $this->getAccessStatus();
        return in_array($status, ['trial_expired', 'premium_expired', 'free']);
    }

    /**
     * Get readable status untuk UI
     */
    public function getReadableStatus()
    {
        $status = $this->getAccessStatus();
        
        $statusMap = [
            'premium_active' => 'Premium Aktif',
            'trial_active' => 'Trial Aktif',
            'trial_expired' => 'Trial Habis',
            'premium_expired' => 'Premium Habis',
            'free' => 'Gratis'
        ];
        
        return $statusMap[$status] ?? 'Tidak Diketahui';
    }
}