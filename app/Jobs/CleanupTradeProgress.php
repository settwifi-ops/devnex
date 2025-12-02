<?php
// app/Jobs/CleanupTradeProgress.php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Cache\TradingCacheService;
use Illuminate\Support\Facades\Log;

class CleanupTradeProgress implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $symbol;
    public $userIds;
    public $tries = 2;
    public $timeout = 60;

    public function __construct(string $symbol, array $userIds)
    {
        $this->symbol = $symbol;
        $this->userIds = $userIds;
    }

    public function handle()
    {
        Log::info("🧹 Cleaning up trade progress for symbol {$this->symbol}");
        
        $cache = new TradingCacheService();
        
        try {
            // 1. Complete trade progress
            $cache->completeTradeProgress($this->symbol);
            
            // 2. Reset trading state untuk semua user
            foreach ($this->userIds as $userId) {
                try {
                    $cache->setUserTrading($userId, false);
                } catch (\Exception $e) {
                    Log::warning("Failed to reset trading state for user {$userId}: " . $e->getMessage());
                }
            }
            
            // 3. Log completion
            Log::info("✅ Trade progress cleanup completed", [
                'symbol' => $this->symbol,
                'users_reset' => count($this->userIds)
            ]);
            
        } catch (\Exception $e) {
            Log::error("❌ Trade progress cleanup failed: " . $e->getMessage());
            throw $e;
        }
    }
}