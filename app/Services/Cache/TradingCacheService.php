<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TradingCacheService
{
    // Connection Redis untuk trading
    private $redis;
    
    // TTL Configuration
    const TTL_POSITIONS = 10;    // 10 detik untuk positions
    const TTL_PRICES = 2;        // 2 detik untuk prices (sering update)
    const TTL_BALANCE = 30;      // 30 detik untuk balance
    const TTL_ORDERS = 15;       // 15 detik untuk orders
    const TTL_USER_STATE = 60;   // 60 detik untuk user state
    
    // Key Patterns
    const KEY_USER_POSITIONS = 'user:%s:positions';
    const KEY_USER_POSITION = 'user:%s:position:%s';
    const KEY_USER_BALANCE = 'user:%s:balance';
    const KEY_USER_ORDERS = 'user:%s:orders';
    const KEY_USER_ORDER = 'user:%s:order:%s';
    const KEY_USER_STATE = 'user:%s:state';
    const KEY_SYMBOL_PRICE = 'price:%s';
    const KEY_TRADE_IN_PROGRESS = 'trade:progress:%s';
    const KEY_RATE_LIMIT = 'rate:limit:%s:%s';
    
    public function __construct()
    {
        // Gunakan connection khusus trading
        $this->redis = Redis::connection('trading');
    }
    
    /**
     * ============================
     * POSITIONS CACHE METHODS
     * ============================
     */
    
    /**
     * Cache user positions
     */
    public function cachePositions(int $userId, array $positions): bool
    {
        try {
            $key = sprintf(self::KEY_USER_POSITIONS, $userId);
            
            // Store positions as hash for fast updates
            foreach ($positions as $position) {
                $symbol = $position['symbol'];
                $hashKey = sprintf(self::KEY_USER_POSITION, $userId, $symbol);
                
                // Cache individual position
                $this->redis->setex(
                    $hashKey,
                    self::TTL_POSITIONS,
                    json_encode($position)
                );
            }
            
            // Store list of symbols
            $symbols = array_column($positions, 'symbol');
            $this->redis->setex(
                $key,
                self::TTL_POSITIONS,
                json_encode($symbols)
            );
            
            Log::debug("Cached positions for user {$userId}", ['count' => count($positions)]);
            return true;
            
        } catch (\Exception $e) {
            Log::error("Failed to cache positions for user {$userId}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get cached positions for user
     */
    public function getPositions(int $userId): array
    {
        try {
            $key = sprintf(self::KEY_USER_POSITIONS, $userId);
            $symbolsJson = $this->redis->get($key);
            
            if (!$symbolsJson) {
                return [];
            }
            
            $symbols = json_decode($symbolsJson, true);
            $positions = [];
            
            foreach ($symbols as $symbol) {
                $position = $this->getPosition($userId, $symbol);
                if ($position) {
                    $positions[] = $position;
                }
            }
            
            return $positions;
            
        } catch (\Exception $e) {
            Log::error("Failed to get cached positions for user {$userId}: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get single cached position
     */
    public function getPosition(int $userId, string $symbol): ?array
    {
        try {
            $key = sprintf(self::KEY_USER_POSITION, $userId, $symbol);
            $positionJson = $this->redis->get($key);
            
            return $positionJson ? json_decode($positionJson, true) : null;
            
        } catch (\Exception $e) {
            Log::error("Failed to get cached position for user {$userId}, symbol {$symbol}: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update single position
     */
    public function updatePosition(int $userId, array $position): bool
    {
        try {
            $symbol = $position['symbol'];
            $key = sprintf(self::KEY_USER_POSITION, $userId, $symbol);
            
            // Update the position
            $this->redis->setex(
                $key,
                self::TTL_POSITIONS,
                json_encode($position)
            );
            
            // Update the symbols list
            $listKey = sprintf(self::KEY_USER_POSITIONS, $userId);
            $symbolsJson = $this->redis->get($listKey);
            
            if ($symbolsJson) {
                $symbols = json_decode($symbolsJson, true);
                if (!in_array($symbol, $symbols)) {
                    $symbols[] = $symbol;
                    $this->redis->setex(
                        $listKey,
                        self::TTL_POSITIONS,
                        json_encode($symbols)
                    );
                }
            } else {
                $this->redis->setex(
                    $listKey,
                    self::TTL_POSITIONS,
                    json_encode([$symbol])
                );
            }
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("Failed to update cached position for user {$userId}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * ============================
     * PRICES CACHE METHODS
     * ============================
     */
    
    /**
     * Cache symbol prices
     */
    public function cachePrices(array $prices): bool
    {
        try {
            foreach ($prices as $symbol => $price) {
                $key = sprintf(self::KEY_SYMBOL_PRICE, $symbol);
                $data = [
                    'price' => $price,
                    'timestamp' => now()->timestamp,
                    'symbol' => $symbol
                ];
                
                $this->redis->setex(
                    $key,
                    self::TTL_PRICES,
                    json_encode($data)
                );
            }
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("Failed to cache prices: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get cached price for symbol
     */
    public function getPrice(string $symbol): ?float
    {
        try {
            $key = sprintf(self::KEY_SYMBOL_PRICE, $symbol);
            $priceJson = $this->redis->get($key);
            
            if (!$priceJson) {
                return null;
            }
            
            $data = json_decode($priceJson, true);
            return $data['price'] ?? null;
            
        } catch (\Exception $e) {
            Log::error("Failed to get cached price for symbol {$symbol}: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get multiple prices
     */
    public function getPrices(array $symbols): array
    {
        $prices = [];
        
        try {
            foreach ($symbols as $symbol) {
                $price = $this->getPrice($symbol);
                if ($price !== null) {
                    $prices[$symbol] = $price;
                }
            }
            
            return $prices;
            
        } catch (\Exception $e) {
            Log::error("Failed to get cached prices for symbols: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * ============================
     * BALANCE CACHE METHODS
     * ============================
     */
    
    /**
     * Cache user balance
     */
    public function cacheBalance(int $userId, array $balance): bool
    {
        try {
            $key = sprintf(self::KEY_USER_BALANCE, $userId);
            
            $this->redis->setex(
                $key,
                self::TTL_BALANCE,
                json_encode([
                    'balance' => $balance,
                    'timestamp' => now()->timestamp
                ])
            );
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("Failed to cache balance for user {$userId}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get cached balance
     */
    public function getBalance(int $userId): ?array
    {
        try {
            $key = sprintf(self::KEY_USER_BALANCE, $userId);
            $balanceJson = $this->redis->get($key);
            
            if (!$balanceJson) {
                return null;
            }
            
            $data = json_decode($balanceJson, true);
            return $data['balance'] ?? null;
            
        } catch (\Exception $e) {
            Log::error("Failed to get cached balance for user {$userId}: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * ============================
     * ORDERS CACHE METHODS
     * ============================
     */
    
    /**
     * Cache user orders
     */
    public function cacheOrders(int $userId, array $orders): bool
    {
        try {
            $key = sprintf(self::KEY_USER_ORDERS, $userId);
            
            // Store list of order IDs
            $orderIds = array_column($orders, 'order_id');
            $this->redis->setex(
                $key,
                self::TTL_ORDERS,
                json_encode($orderIds)
            );
            
            // Store individual orders
            foreach ($orders as $order) {
                $orderKey = sprintf(self::KEY_USER_ORDER, $userId, $order['order_id']);
                $this->redis->setex(
                    $orderKey,
                    self::TTL_ORDERS,
                    json_encode($order)
                );
            }
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("Failed to cache orders for user {$userId}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get cached orders
     */
    public function getOrders(int $userId): array
    {
        try {
            $key = sprintf(self::KEY_USER_ORDERS, $userId);
            $orderIdsJson = $this->redis->get($key);
            
            if (!$orderIdsJson) {
                return [];
            }
            
            $orderIds = json_decode($orderIdsJson, true);
            $orders = [];
            
            foreach ($orderIds as $orderId) {
                $order = $this->getOrder($userId, $orderId);
                if ($order) {
                    $orders[] = $order;
                }
            }
            
            return $orders;
            
        } catch (\Exception $e) {
            Log::error("Failed to get cached orders for user {$userId}: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get single cached order
     */
    public function getOrder(int $userId, string $orderId): ?array
    {
        try {
            $key = sprintf(self::KEY_USER_ORDER, $userId, $orderId);
            $orderJson = $this->redis->get($key);
            
            return $orderJson ? json_decode($orderJson, true) : null;
            
        } catch (\Exception $e) {
            Log::error("Failed to get cached order for user {$userId}, order {$orderId}: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * ============================
     * USER STATE MANAGEMENT
     * ============================
     */
    
    /**
     * Set user trading state
     */
    public function setUserState(int $userId, string $key, $value, int $ttl = null): bool
    {
        try {
            $stateKey = sprintf(self::KEY_USER_STATE, $userId);
            
            // Get existing state
            $stateJson = $this->redis->get($stateKey);
            $state = $stateJson ? json_decode($stateJson, true) : [];
            
            // Update state
            $state[$key] = [
                'value' => $value,
                'updated_at' => now()->timestamp,
                'expires_at' => now()->addSeconds($ttl ?? self::TTL_USER_STATE)->timestamp
            ];
            
            $this->redis->setex(
                $stateKey,
                self::TTL_USER_STATE,
                json_encode($state)
            );
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("Failed to set user state for user {$userId}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user trading state
     */
    public function getUserState(int $userId, string $key = null)
    {
        try {
            $stateKey = sprintf(self::KEY_USER_STATE, $userId);
            $stateJson = $this->redis->get($stateKey);
            
            if (!$stateJson) {
                return $key ? null : [];
            }
            
            $state = json_decode($stateJson, true);
            
            // Clean expired states
            $cleanedState = [];
            foreach ($state as $k => $item) {
                if (($item['expires_at'] ?? 0) > now()->timestamp) {
                    $cleanedState[$k] = $item['value'];
                }
            }
            
            // Update if cleaned
            if (count($cleanedState) !== count($state)) {
                $this->redis->setex(
                    $stateKey,
                    self::TTL_USER_STATE,
                    json_encode($cleanedState)
                );
            }
            
            return $key ? ($cleanedState[$key] ?? null) : $cleanedState;
            
        } catch (\Exception $e) {
            Log::error("Failed to get user state for user {$userId}: " . $e->getMessage());
            return $key ? null : [];
        }
    }
    
    /**
     * Check if user is trading
     */
    public function isUserTrading(int $userId): bool
    {
        return (bool) $this->getUserState($userId, 'is_trading');
    }
    
    /**
     * Set user as trading
     */
    public function setUserTrading(int $userId, bool $trading = true): bool
    {
        return $this->setUserState($userId, 'is_trading', $trading);
    }
    
    /**
     * ============================
     * TRADE PROGRESS TRACKING
     * ============================
     */
    
    /**
     * Mark trade as in progress
     */
    public function markTradeInProgress(string $symbol, array $userIds): bool
    {
        try {
            $key = sprintf(self::KEY_TRADE_IN_PROGRESS, $symbol);
            
            $this->redis->setex(
                $key,
                300, // 5 menit expiry
                json_encode([
                    'symbol' => $symbol,
                    'user_ids' => $userIds,
                    'started_at' => now()->timestamp
                ])
            );
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("Failed to mark trade in progress for symbol {$symbol}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if trade is in progress
     */
    public function isTradeInProgress(string $symbol): bool
    {
        try {
            $key = sprintf(self::KEY_TRADE_IN_PROGRESS, $symbol);
            return (bool) $this->redis->exists($key);
            
        } catch (\Exception $e) {
            Log::error("Failed to check trade progress for symbol {$symbol}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get trade progress
     */
    public function getTradeProgress(string $symbol): ?array
    {
        try {
            $key = sprintf(self::KEY_TRADE_IN_PROGRESS, $symbol);
            $progressJson = $this->redis->get($key);
            
            return $progressJson ? json_decode($progressJson, true) : null;
            
        } catch (\Exception $e) {
            Log::error("Failed to get trade progress for symbol {$symbol}: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Complete trade progress
     */
    public function completeTradeProgress(string $symbol): bool
    {
        try {
            $key = sprintf(self::KEY_TRADE_IN_PROGRESS, $symbol);
            return (bool) $this->redis->del($key);
            
        } catch (\Exception $e) {
            Log::error("Failed to complete trade progress for symbol {$symbol}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * ============================
     * RATE LIMITING
     * ============================
     */
    
    /**
     * Check rate limit
     */
    public function checkRateLimit(string $type, string $identifier, int $limit, int $windowSeconds = 60): array
    {
        try {
            $key = sprintf(self::KEY_RATE_LIMIT, $type, $identifier);
            
            $current = $this->redis->get($key) ?: 0;
            
            if ($current >= $limit) {
                $ttl = $this->redis->ttl($key);
                return [
                    'allowed' => false,
                    'current' => $current,
                    'limit' => $limit,
                    'retry_after' => $ttl
                ];
            }
            
            // Increment counter
            if ($current === 0) {
                $this->redis->setex($key, $windowSeconds, 1);
            } else {
                $this->redis->incr($key);
            }
            
            return [
                'allowed' => true,
                'current' => $current + 1,
                'limit' => $limit,
                'retry_after' => null
            ];
            
        } catch (\Exception $e) {
            Log::error("Failed to check rate limit for {$type}:{$identifier}: " . $e->getMessage());
            // Allow on error to prevent blocking
            return [
                'allowed' => true,
                'current' => 0,
                'limit' => $limit,
                'retry_after' => null
            ];
        }
    }
    
    /**
     * Rate limit for user API calls
     */
    public function limitUserApiCall(int $userId): array
    {
        $limit = env('TRADING_RATE_LIMIT_PER_USER', 50);
        return $this->checkRateLimit('user_api', (string) $userId, $limit, 60);
    }
    
    /**
     * Rate limit for symbol trading
     */
    public function limitSymbolTrading(string $symbol): array
    {
        $limit = env('TRADING_RATE_LIMIT_PER_MINUTE', 1000);
        return $this->checkRateLimit('symbol_trade', $symbol, $limit, 60);
    }
    
    /**
     * ============================
     * BATCH OPERATIONS
     * ============================
     */
    
    /**
     * Batch cache positions for multiple users
     */
    public function batchCachePositions(array $userPositions): bool
    {
        try {
            foreach ($userPositions as $userId => $positions) {
                $this->cachePositions($userId, $positions);
            }
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("Failed to batch cache positions: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Batch get positions for multiple users
     */
    public function batchGetPositions(array $userIds): array
    {
        $results = [];
        
        try {
            foreach ($userIds as $userId) {
                $results[$userId] = $this->getPositions($userId);
            }
            
            return $results;
            
        } catch (\Exception $e) {
            Log::error("Failed to batch get positions: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * ============================
     * UTILITY METHODS
     * ============================
     */
    
    /**
     * Invalidate user cache
     */
    public function invalidateUserCache(int $userId): bool
    {
        try {
            // Get all keys for user
            $pattern = sprintf('*user:%s:*', $userId);
            $keys = $this->redis->keys($pattern);
            
            if (!empty($keys)) {
                $this->redis->del($keys);
            }
            
            Log::info("Invalidated cache for user {$userId}", ['keys_deleted' => count($keys)]);
            return true;
            
        } catch (\Exception $e) {
            Log::error("Failed to invalidate cache for user {$userId}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        try {
            // Count keys by pattern
            $patterns = [
                'positions' => '*user:*:positions*',
                'prices' => '*price:*',
                'balances' => '*user:*:balance*',
                'orders' => '*user:*:order*',
                'states' => '*user:*:state*',
            ];
            
            $stats = [];
            foreach ($patterns as $name => $pattern) {
                $keys = $this->redis->keys($pattern);
                $stats[$name] = count($keys);
            }
            
            // Memory usage
            $info = $this->redis->info('memory');
            $stats['memory_used'] = $info['used_memory_human'] ?? 'N/A';
            $stats['memory_peak'] = $info['used_memory_peak_human'] ?? 'N/A';
            
            return $stats;
            
        } catch (\Exception $e) {
            Log::error("Failed to get cache stats: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Clear all trading cache
     */
    public function clearAll(): bool
    {
        try {
            // Hanya clear trading database (db 3)
            $this->redis->flushdb();
            Log::info("Cleared all trading cache");
            return true;
            
        } catch (\Exception $e) {
            Log::error("Failed to clear trading cache: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Ping Redis connection
     */
    public function ping(): bool
    {
        try {
            $response = $this->redis->ping();
            // Handle berbagai tipe response
            if (is_bool($response)) {
                return $response;
            } elseif (is_string($response)) {
                return strtoupper($response) === 'PONG';
            } elseif (is_numeric($response)) {
                return $response == 1;
            }
            return false;
        } catch (\Exception $e) {
            Log::error("Redis ping failed: " . $e->getMessage());
            return false;
        }
    }
}