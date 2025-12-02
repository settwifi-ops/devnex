<?php
// app/Models/UserBinanceAccount.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Crypt;

class UserBinanceAccount extends Model
{
    use HasFactory;

    protected $table = 'user_binance_accounts';

    protected $fillable = [
        'user_id',
        'is_testnet',
        'environment',
        'api_key_encrypted',
        'api_secret_encrypted', 
        'label',
        'is_active',
        'permissions',
        'balance_snapshot',
        'last_verified',
        'verification_status',
        'deleted_at'
    ];

    protected $casts = [
        'is_testnet' => 'boolean',
        'permissions' => 'array',
        'is_active' => 'boolean',
        'balance_snapshot' => 'decimal:8',
        'last_verified' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    protected $appends = [
        'environment_label',
        'environment_badge_color',
        'status_label',
        'is_valid'
    ];

    /**
     * Relationship dengan User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship dengan Portfolio
     */
    public function portfolio()
    {
        return $this->hasOneThrough(
            UserPortfolio::class,
            User::class,
            'id', // Foreign key on User table
            'user_id', // Foreign key on UserPortfolio table
            'user_id', // Local key on UserBinanceAccount table
            'id' // Local key on User table
        );
    }

    /**
     * Scope untuk account yang active
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk account yang verified
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('verification_status', 'verified');
    }

    /**
     * Scope untuk testnet accounts
     */
    public function scopeTestnet(Builder $query): Builder
    {
        return $query->where('is_testnet', true);
    }

    /**
     * Scope untuk mainnet accounts  
     */
    public function scopeMainnet(Builder $query): Builder
    {
        return $query->where('is_testnet', false);
    }

    /**
     * Scope untuk account yang tidak expired
     */
    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->where(function($q) {
            $q->whereNull('last_verified')
              ->orWhere('last_verified', '>=', now()->subDays(1));
        });
    }

    /**
     * Scope untuk account yang terhapus (soft delete)
     */
    public function scopeTrashed(Builder $query): Builder
    {
        return $query->whereNotNull('deleted_at');
    }

    /**
     * Scope untuk account yang tidak terhapus
     */
    public function scopeNotTrashed(Builder $query): Builder
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Check jika account masih valid
     */
    public function getIsValidAttribute(): bool
    {
        return $this->is_active && 
               $this->verification_status === 'verified' &&
               $this->last_verified && 
               $this->last_verified->gt(now()->subDays(1));
    }

    /**
     * Get environment label
     */
    public function getEnvironmentLabelAttribute(): string
    {
        return $this->is_testnet ? 'Testnet' : 'Live Trading';
    }

    /**
     * Get environment badge color
     */
    public function getEnvironmentBadgeColorAttribute(): string
    {
        return $this->is_testnet ? 'warning' : 'danger';
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        if (!$this->is_active) {
            return 'Inactive';
        }
        
        if ($this->verification_status !== 'verified') {
            return ucfirst($this->verification_status);
        }
        
        if (!$this->last_verified || $this->last_verified->lt(now()->subDays(1))) {
            return 'Expired';
        }
        
        return 'Active';
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeColorAttribute(): string
    {
        $status = $this->status_label;
        
        switch ($status) {
            case 'Active':
                return 'success';
            case 'Inactive':
                return 'secondary';
            case 'Expired':
                return 'warning';
            case 'Pending':
                return 'info';
            case 'Rejected':
            case 'Failed':
                return 'danger';
            default:
                return 'light';
        }
    }

    /**
     * Accessor untuk decrypted API key
     */
    public function getApiKeyAttribute(): ?string
    {
        try {
            if (empty($this->api_key_encrypted)) {
                return null;
            }
            
            return Crypt::decryptString($this->api_key_encrypted);
        } catch (\Exception $e) {
            \Log::error("Failed to decrypt API key for account {$this->id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Accessor untuk decrypted API secret
     */
    public function getApiSecretAttribute(): ?string
    {
        try {
            if (empty($this->api_secret_encrypted)) {
                return null;
            }
            
            return Crypt::decryptString($this->api_secret_encrypted);
        } catch (\Exception $e) {
            \Log::error("Failed to decrypt API secret for account {$this->id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Mutator untuk encrypt API key
     */
    public function setApiKeyAttribute($value): void
    {
        if (!empty($value)) {
            $this->attributes['api_key_encrypted'] = Crypt::encryptString($value);
        }
    }

    /**
     * Mutator untuk encrypt API secret
     */
    public function setApiSecretAttribute($value): void
    {
        if (!empty($value)) {
            $this->attributes['api_secret_encrypted'] = Crypt::encryptString($value);
        }
    }

    /**
     * Get balance in formatted string
     */
    public function getBalanceFormattedAttribute(): string
    {
        if (!$this->balance_snapshot) {
            return '$0.00';
        }
        
        return '$' . number_format($this->balance_snapshot, 2);
    }

    /**
     * Get last verified time in human readable format
     */
    public function getLastVerifiedHumanAttribute(): string
    {
        if (!$this->last_verified) {
            return 'Never';
        }
        
        return $this->last_verified->diffForHumans();
    }

    /**
     * Get permissions as array with labels
     */
    public function getPermissionsFormattedAttribute(): array
    {
        $permissions = $this->permissions ?? [];
        $labels = [];
        
        if (($permissions['canTrade'] ?? false) === true) {
            $labels[] = 'Trading';
        }
        
        if (($permissions['canTradeFutures'] ?? false) === true) {
            $labels[] = 'Futures Trading';
        }
        
        if (($permissions['canWithdraw'] ?? false) === true) {
            $labels[] = 'Withdrawal';
        }
        
        return $labels;
    }

    /**
     * Mark account as verified
     */
    public function markAsVerified(array $permissions = []): bool
    {
        return $this->update([
            'verification_status' => 'verified',
            'permissions' => $permissions ?: $this->permissions,
            'last_verified' => now(),
            'is_active' => true
        ]);
    }

    /**
     * Mark account as pending
     */
    public function markAsPending(): bool
    {
        return $this->update([
            'verification_status' => 'pending',
            'is_active' => false
        ]);
    }

    /**
     * Mark account as rejected
     */
    public function markAsRejected(): bool
    {
        return $this->update([
            'verification_status' => 'rejected',
            'is_active' => false
        ]);
    }

    /**
     * Activate account
     */
    public function activate(): bool
    {
        return $this->update([
            'is_active' => true,
            'last_verified' => now()
        ]);
    }

    /**
     * Deactivate account
     */
    public function deactivate(): bool
    {
        return $this->update([
            'is_active' => false
        ]);
    }

    /**
     * Soft delete account
     */
    public function softDelete(): bool
    {
        return $this->update([
            'is_active' => false,
            'verification_status' => 'deleted',
            'deleted_at' => now()
        ]);
    }

    /**
     * Restore soft deleted account
     */
    public function restore(): bool
    {
        return $this->update([
            'deleted_at' => null
        ]);
    }

    /**
     * Update balance snapshot
     */
    public function updateBalanceSnapshot(float $balance): bool
    {
        return $this->update([
            'balance_snapshot' => $balance,
            'last_verified' => now()
        ]);
    }

//     * Test API connection
  //   */
    public function testConnection(): array
    {
        try {
            if (!$this->is_valid) {
                return [
                    'success' => false,
                    'message' => 'Account is not valid or expired'
                ];
            }
            
            $apiKey = $this->api_key;
            $apiSecret = $this->api_secret;
            
            if (!$apiKey || !$apiSecret) {
                return [
                    'success' => false,
                    'message' => 'API credentials are missing or corrupted'
                ];
            }
            
            // Test dengan membuat instance Binance
            $binance = new \Binance\API($apiKey, $apiSecret, [
                'useServerTime' => true,
                'testnet' => $this->is_testnet
            ]);
            
            // Test dengan price() (method yang tersedia)
            $price = $binance->price('BTCUSDT');
            
            // Test dengan time() - method ini mengembalikan array
            $timeData = $binance->time();
            
            // Ekstrak server time dari array
            $serverTime = isset($timeData['serverTime']) ? $timeData['serverTime'] : null;
            
            return [
                'success' => true,
                'message' => 'Connection successful',
                'testnet' => $this->is_testnet,
                'btc_price' => $price,
                'server_time' => $serverTime ? date('Y-m-d H:i:s', $serverTime / 1000) : 'N/A',
                'timestamp' => now()->toDateTimeString()
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
                'testnet' => $this->is_testnet
            ];
        }
    }

    /**
     * Get account summary for display
     */
    public function getSummaryAttribute(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'environment' => $this->environment_label,
            'status' => $this->status_label,
            'balance' => $this->balance_formatted,
            'last_verified' => $this->last_verified_human,
            'permissions' => $this->permissions_formatted,
            'is_testnet' => $this->is_testnet,
            'is_valid' => $this->is_valid,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'api_key_preview' => $this->api_key ? substr($this->api_key, 0, 8) . '...' : 'N/A'
        ];
    }

    /**
     * Check if account has trading permission
     */
    public function canTrade(): bool
    {
        return ($this->permissions['canTrade'] ?? false) === true;
    }

    /**
     * Check if account has futures trading permission
     */
    public function canTradeFutures(): bool
    {
        return ($this->permissions['canTradeFutures'] ?? false) === true;
    }

    /**
     * Check if account can withdraw
     */
    public function canWithdraw(): bool
    {
        return ($this->permissions['canWithdraw'] ?? false) === true;
    }

    /**
     * Get default label if not set
     */
    protected static function boot(): void
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->label)) {
                $model->label = $model->is_testnet ? 'Testnet Account' : 'Live Trading Account';
            }
            
            if (empty($model->environment)) {
                $model->environment = $model->is_testnet ? 'testnet' : 'mainnet';
            }
        });
        
        static::updating(function ($model) {
            // Ensure encrypted data is not accidentally overwritten with plain text
            if (isset($model->api_key_encrypted) && !str_starts_with($model->api_key_encrypted, 'eyJ')) {
                unset($model->api_key_encrypted);
            }
            
            if (isset($model->api_secret_encrypted) && !str_starts_with($model->api_secret_encrypted, 'eyJ')) {
                unset($model->api_secret_encrypted);
            }
        });
    }
}