<?php
// app/Jobs/RefreshUserDataJob.php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Cache\TradingCacheService;
use App\Services\BinanceAccountService;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RefreshUserDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userId;
    public $timeout = 300; // 5 menit timeout
    public $tries = 3;
    public $backoff = [60, 300]; // Retry setelah 1 menit, lalu 5 menit

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function handle()
    {
        Log::info("🔄 Starting background data refresh for user {$this->userId}");
        
        try {
            $cache = new TradingCacheService();
            $binanceService = new BinanceAccountService();
            
            // 1. Rate limiting check
            $rateLimit = $cache->limitUserApiCall($this->userId);
            if (!$rateLimit['allowed']) {
                Log::warning("Rate limit exceeded for user {$this->userId}, releasing job");
                $this->release($rateLimit['retry_after'] ?? 60);
                return;
            }
            
            // 2. Mark user as being synced
            $cache->setUserTrading($this->userId, true);
            
            // 3. Get Binance instance
            $binance = $binanceService->getBinanceInstance($this->userId);
            
            // 4. Fetch positions dari Binance
            $positions = $this->fetchBinancePositions($binance);
            $cache->cachePositions($this->userId, $positions);
            
            // 5. Fetch balance
            $balance = $this->fetchBinanceBalance($binance);
            $cache->cacheBalance($this->userId, $balance);
            
            // 6. Update database balance snapshot
            $this->updateDatabaseBalance($balance);
            
            // 7. Mark sync complete
            $cache->setUserTrading($this->userId, false);
            
            Log::info("✅ Background data refresh completed for user {$this->userId}", [
                'positions_count' => count($positions),
                'balance' => $balance['total'] ?? 0
            ]);
            
        } catch (\Exception $e) {
            Log::error("❌ Failed to refresh data for user {$this->userId}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            // Release job untuk dicoba lagi
            $this->release(60); // Coba lagi dalam 60 detik
            throw $e;
        }
    }
    
    /**
     * Fetch positions dari Binance API (compatible dengan jaggedsoft/php-binance-api)
     */
    private function fetchBinancePositions($binance): array
    {
        $positions = [];
        
        try {
            // Method 1: Coba futuresAccount() terlebih dahulu
            if (method_exists($binance, 'futuresAccount')) {
                $accountInfo = $binance->futuresAccount();
                
                if (isset($accountInfo['positions']) && is_array($accountInfo['positions'])) {
                    $positionsData = $accountInfo['positions'];
                } else {
                    // Cari di seluruh array
                    foreach ($accountInfo as $key => $value) {
                        if (is_array($value) && count($value) > 0) {
                            $firstItem = $value[0] ?? null;
                            if (is_array($firstItem) && isset($firstItem['symbol'])) {
                                $positionsData = $value;
                                break;
                            }
                        }
                    }
                }
                
                if (!empty($positionsData)) {
                    foreach ($positionsData as $position) {
                        if (!is_array($position)) continue;
                        
                        // Cek berbagai kemungkinan field untuk amount
                        $positionAmt = 0;
                        if (isset($position['positionAmt'])) {
                            $positionAmt = (float) $position['positionAmt'];
                        } elseif (isset($position['positionAmount'])) {
                            $positionAmt = (float) $position['positionAmount'];
                        } elseif (isset($position['amount'])) {
                            $positionAmt = (float) $position['amount'];
                        }
                        
                        // Skip jika amount 0
                        if ($positionAmt == 0) continue;
                        
                        $symbol = $position['symbol'] ?? '';
                        if (empty($symbol)) continue;
                        
                        $positions[] = [
                            'symbol' => $symbol,
                            'positionAmt' => $positionAmt,
                            'entryPrice' => (float) ($position['entryPrice'] ?? $position['avgPrice'] ?? 0),
                            'markPrice' => (float) ($position['markPrice'] ?? $position['currentPrice'] ?? 0),
                            'unRealizedProfit' => (float) ($position['unRealizedProfit'] ?? $position['unrealizedProfit'] ?? $position['pnl'] ?? 0),
                            'leverage' => (int) ($position['leverage'] ?? 1),
                            'liquidationPrice' => (float) ($position['liquidationPrice'] ?? 0),
                            'marginType' => $position['marginType'] ?? 'isolated',
                            'isolatedMargin' => (float) ($position['isolatedMargin'] ?? 0),
                            'positionSide' => $position['positionSide'] ?? 'BOTH'
                        ];
                    }
                }
            }
            
            // Method 2: Fallback ke account() jika futuresAccount tidak tersedia
            if (empty($positions) && method_exists($binance, 'account')) {
                try {
                    $accountInfo = $binance->account();
                    
                    if (isset($accountInfo['balances']) && is_array($accountInfo['balances'])) {
                        foreach ($accountInfo['balances'] as $balance) {
                            if (isset($balance['asset']) && (float) ($balance['free'] ?? 0) != 0) {
                                $positions[] = [
                                    'symbol' => $balance['asset'] . 'USDT',
                                    'positionAmt' => (float) $balance['free'],
                                    'entryPrice' => 0,
                                    'markPrice' => 0,
                                    'unRealizedProfit' => 0,
                                    'leverage' => 1,
                                    'liquidationPrice' => 0,
                                    'marginType' => 'spot',
                                    'isolatedMargin' => 0,
                                    'positionSide' => 'LONG'
                                ];
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("Fallback account() failed: " . $e->getMessage());
                }
            }
            
            // Format positions untuk cache
            $formattedPositions = [];
            foreach ($positions as $position) {
                $positionAmt = $position['positionAmt'];
                $side = $positionAmt > 0 ? 'BUY' : 'SELL';
                $positionType = $positionAmt > 0 ? 'LONG' : 'SHORT';
                $quantity = abs($positionAmt);
                
                // Ambil current price jika markPrice 0
                $currentPrice = $position['markPrice'];
                if ($currentPrice <= 0) {
                    try {
                        $prices = $binance->prices();
                        $searchSymbol = str_replace('_', '', $position['symbol']);
                        if (isset($prices[$searchSymbol])) {
                            $currentPrice = (float) $prices[$searchSymbol];
                        }
                    } catch (\Exception $e) {
                        $currentPrice = $position['entryPrice'] > 0 ? $position['entryPrice'] : 1;
                    }
                }
                
                // Hitung P&L percentage
                $pnl = $position['unRealizedProfit'];
                $pnlPercentage = 0;
                if ($position['entryPrice'] > 0 && $quantity > 0) {
                    $positionValue = $position['entryPrice'] * $quantity;
                    if ($positionValue > 0) {
                        $pnlPercentage = ($pnl / $positionValue) * 100;
                    }
                }
                
                $formattedPositions[] = [
                    'symbol' => $position['symbol'],
                    'side' => $side,
                    'position_type' => $positionType,
                    'entry_price' => $position['entryPrice'],
                    'mark_price' => $currentPrice,
                    'quantity' => $quantity,
                    'unrealized_pnl' => $pnl,
                    'pnl_percentage' => $pnlPercentage,
                    'leverage' => $position['leverage'],
                    'liquidation_price' => $position['liquidationPrice'],
                    'margin_type' => $position['marginType'],
                    'isolated_margin' => $position['isolatedMargin'],
                    'position_side' => $position['positionSide'],
                    'updated_at' => now()->timestamp
                ];
            }
            
            Log::debug("Fetched {$formattedPositions} positions from Binance for user {$this->userId}");
            return $formattedPositions;
            
        } catch (\Exception $e) {
            Log::error("Failed to fetch Binance positions for user {$this->userId}: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Fetch balance dari Binance
     */
    private function fetchBinanceBalance($binance): array
    {
        try {
            $balanceData = [];
            
            // Coba futuresAccountBalance()
            if (method_exists($binance, 'futuresAccountBalance')) {
                $futuresBalance = $binance->futuresAccountBalance();
                
                if (is_array($futuresBalance)) {
                    foreach ($futuresBalance as $asset) {
                        if (isset($asset['asset']) && isset($asset['balance'])) {
                            $balance = (float) $asset['balance'];
                            if ($balance > 0) {
                                $balanceData[$asset['asset']] = [
                                    'asset' => $asset['asset'],
                                    'balance' => $balance,
                                    'available' => (float) ($asset['availableBalance'] ?? $asset['withdrawAvailable'] ?? 0),
                                    'type' => 'futures'
                                ];
                            }
                        }
                    }
                }
            }
            
            // Coba account() untuk spot balance
            if (method_exists($binance, 'account')) {
                $accountInfo = $binance->account();
                
                if (isset($accountInfo['balances']) && is_array($accountInfo['balances'])) {
                    foreach ($accountInfo['balances'] as $balance) {
                        if (isset($balance['asset']) && ((float) ($balance['free'] ?? 0) > 0 || (float) ($balance['locked'] ?? 0) > 0)) {
                            $balanceData[$balance['asset']] = [
                                'asset' => $balance['asset'],
                                'balance' => (float) ($balance['free'] ?? 0) + (float) ($balance['locked'] ?? 0),
                                'available' => (float) ($balance['free'] ?? 0),
                                'locked' => (float) ($balance['locked'] ?? 0),
                                'type' => 'spot'
                            ];
                        }
                    }
                }
            }
            
            // Hitung total
            $totalBalance = 0;
            $availableBalance = 0;
            
            foreach ($balanceData as $asset) {
                $totalBalance += $asset['balance'];
                $availableBalance += $asset['available'] ?? 0;
            }
            
            return [
                'total' => $totalBalance,
                'available' => $availableBalance,
                'assets' => $balanceData,
                'timestamp' => now()->timestamp,
                'currency' => 'USDT'
            ];
            
        } catch (\Exception $e) {
            Log::error("Failed to fetch Binance balance for user {$this->userId}: " . $e->getMessage());
            
            // Return default balance
            return [
                'total' => 0,
                'available' => 0,
                'assets' => [],
                'timestamp' => now()->timestamp,
                'currency' => 'USDT'
            ];
        }
    }
    
    /**
     * Update balance snapshot di database
     */
    private function updateDatabaseBalance(array $balance): void
    {
        try {
            DB::transaction(function () use ($balance) {
                $user = User::find($this->userId);
                
                if ($user && $user->portfolio) {
                    $user->portfolio->update([
                        'real_balance' => $balance['total'] ?? 0,
                        'real_available_balance' => $balance['available'] ?? 0,
                        'balance_updated_at' => now()
                    ]);
                    
                    Log::debug("Updated database balance for user {$this->userId}", [
                        'balance' => $balance['total'],
                        'available' => $balance['available']
                    ]);
                }
            });
        } catch (\Exception $e) {
            Log::warning("Failed to update database balance for user {$this->userId}: " . $e->getMessage());
        }
    }
    
    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception)
    {
        Log::error("❌ RefreshUserDataJob failed for user {$this->userId}", [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
        
        // Reset user trading state
        try {
            $cache = new TradingCacheService();
            $cache->setUserTrading($this->userId, false);
        } catch (\Exception $e) {
            Log::error("Failed to reset user trading state: " . $e->getMessage());
        }
    }
}