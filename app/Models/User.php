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
        'country_code', 'is_trial_used'
    ];

    protected $hidden = [
        'password', 'remember_token', 'login_token'
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'premium_ends_at' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================
    
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Relationship to Portfolio model
     * SATU user memiliki SATU portfolio
     */
    public function portfolio()
    {
        return $this->hasOne(UserPortfolio::class);
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

    // ✅ METHOD BARU YANG DITAMBAHKAN
    /**
     * Get trial progress percentage
     */
    public function getTrialProgressPercent()
    {
        // Jika tidak ada trial_ends_at, return 0
        if (!$this->trial_ends_at) {
            return 0;
        }

        $now = Carbon::now();
        $trialStart = $this->created_at;
        $trialEnd = Carbon::parse($this->trial_ends_at);

        // Jika trial sudah berakhir
        if ($now->greaterThan($trialEnd)) {
            return 100;
        }

        // Jika trial belum mulai (harusnya tidak mungkin, tapi safety check)
        if ($now->lessThan($trialStart)) {
            return 0;
        }

        // Hitung progress persentase
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

        // Gunakan diffInDays untuk hasil yang lebih user-friendly
        return $now->diffInDays($trialEnd, false); // false = tidak dibulatkan
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
        // Jika punya premium aktif → boleh akses
        if ($this->hasActivePremium()) {
            return true;
        }
        
        // Jika masih trial aktif → boleh akses  
        if ($this->hasActiveTrial()) {
            return true;
        }
        
        // Semua kondisi lainnya → TIDAK boleh akses
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
            'trial_ends_at' => null // Hapus trial jika ada
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