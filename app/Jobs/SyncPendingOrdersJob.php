<?php
// app/Jobs/SyncPendingOrdersJob.php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Cache\TradingCacheService;
use App\Services\BinanceAccountService;
use App\Models\PendingOrder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SyncPendingOrdersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userId;
    public $timeout = 300;
    public $tries = 3;

    public function __construct($userId = null)
    {
        $this->userId = $userId;
    }

    public function handle()
    {
        Log::info("🔄 Starting pending orders sync", [
            'user_id' => $this->userId ?? 'all'
        ]);
        
        try {
            $cache = new TradingCacheService();
            $binanceService = new BinanceAccountService();
            
            // Query pending orders
            $query = PendingOrder::whereIn('status', ['PENDING', 'PARTIALLY_FILLED', 'NEW'])
                ->where('expires_at', '>', now());
            
            if ($this->userId) {
                $query->where('user_id', $this->userId);
            }
            
            $orders = $query->get();
            
            $updatedCount = 0;
            $filledCount = 0;
            $cancelledCount = 0;
            
            // Group orders by user untuk efisiensi
            $ordersByUser = [];
            foreach ($orders as $order) {
                $ordersByUser[$order->user_id][] = $order;
            }
            
            foreach ($ordersByUser as $userId => $userOrders) {
                try {
                    // Rate limiting
                    $rateLimit = $cache->limitUserApiCall($userId);
                    if (!$rateLimit['allowed']) {
                        Log::warning("Rate limit exceeded for user {$userId}, skipping");
                        continue;
                    }
                    
                    $binance = $binanceService->getBinanceInstance($userId);
                    
                    foreach ($userOrders as $order) {
                        $result = $this->syncOrder($order, $binance, $cache);
                        
                        if ($result === 'filled') $filledCount++;
                        if ($result === 'cancelled') $cancelledCount++;
                        if ($result === 'updated') $updatedCount++;
                    }
                    
                } catch (\Exception $e) {
                    Log::error("Failed to sync orders for user {$userId}: " . $e->getMessage());
                    continue;
                }
            }
            
            Log::info("✅ Pending orders sync completed", [
                'total' => $orders->count(),
                'updated' => $updatedCount,
                'filled' => $filledCount,
                'cancelled' => $cancelledCount
            ]);
            
        } catch (\Exception $e) {
            Log::error("❌ Pending orders sync failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Sync single order dengan Binance
     */
    private function syncOrder($order, $binance, TradingCacheService $cache): ?string
    {
        try {
            if (!$order->binance_order_id) {
                return null;
            }
            
            // Get order status from Binance
            $orderStatus = $binance->futuresOrderStatus(
                $order->symbol,
                ['orderId' => $order->binance_order_id]
            );

            
            $orderStatus = $binanceStatus['status'] ?? 'UNKNOWN';
            $executedQty = (float) ($binanceStatus['executedQty'] ?? 0);
            $avgPrice = (float) ($binanceStatus['avgPrice'] ?? 0);
            
            // Update database
            $order->update([
                'status' => $orderStatus,
                'executed_qty' => $executedQty,
                'avg_price' => $avgPrice,
                'last_checked' => now()
            ]);
            
            // Check if filled
            if ($orderStatus === 'FILLED' && $order->status !== 'FILLED') {
                $order->update(['status' => 'FILLED']);
                
                // Update cache
                $this->updateOrderCache($order->user_id, $cache);
                
                return 'filled';
            }
            
            // Check if cancelled
            if ($orderStatus === 'CANCELLED' && $order->status !== 'CANCELLED') {
                $order->update(['status' => 'CANCELLED']);
                return 'cancelled';
            }
            
            // Check if expired
            if ($order->expires_at && $order->expires_at <= now()) {
                $order->update(['status' => 'EXPIRED']);
                return 'cancelled';
            }
            
            return 'updated';
            
        } catch (\Exception $e) {
            Log::warning("Failed to sync order {$order->id}: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update order cache
     */
    private function updateOrderCache(int $userId, TradingCacheService $cache): void
    {
        try {
            // Invalidate orders cache untuk user ini
            $cache->invalidateUserCache($userId);
            
            // Trigger background refresh
            RefreshUserDataJob::dispatch($userId);
            
        } catch (\Exception $e) {
            Log::warning("Failed to update order cache for user {$userId}: " . $e->getMessage());
        }
    }
    
    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception)
    {
        Log::error("❌ SyncPendingOrdersJob failed", [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'user_id' => $this->userId
        ]);
    }
}