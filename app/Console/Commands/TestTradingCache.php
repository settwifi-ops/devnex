<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Cache\TradingCacheService;

class TestTradingCache extends Command
{
    protected $signature = 'trading:cache-test';
    protected $description = 'Test trading cache functionality';

    public function handle()
    {
        $cache = new TradingCacheService();
        
        $this->info('🧪 Testing Trading Cache Service');
        $this->line('');
        
        // Test 1: Ping
        $this->info('1. Testing Redis connection...');
        if ($cache->ping()) {
            $this->info('   ✅ Redis connected successfully');
        } else {
            $this->error('   ❌ Redis connection failed');
            return 1;
        }
        
        // Test 2: Basic operations
        $this->info('2. Testing basic operations...');
        
        // Test positions
        $testPositions = [
            [
                'symbol' => 'BTCUSDT',
                'quantity' => 0.01,
                'entry_price' => 50000,
                'mark_price' => 51000,
                'unrealized_pnl' => 100
            ]
        ];
        
        $cache->cachePositions(1, $testPositions);
        $positions = $cache->getPositions(1);
        
        if (count($positions) > 0) {
            $this->info('   ✅ Positions cache working');
        } else {
            $this->error('   ❌ Positions cache failed');
        }
        
        // Test prices
        $testPrices = ['BTCUSDT' => 51000.50];
        $cache->cachePrices($testPrices);
        $price = $cache->getPrice('BTCUSDT');
        
        if ($price == 51000.50) {
            $this->info('   ✅ Prices cache working');
        } else {
            $this->error('   ❌ Prices cache failed');
        }
        
        // Test user state
        $cache->setUserState(1, 'is_trading', true);
        $isTrading = $cache->isUserTrading(1);
        
        if ($isTrading) {
            $this->info('   ✅ User state cache working');
        } else {
            $this->error('   ❌ User state cache failed');
        }
        
        // Test rate limiting
        $rateLimit = $cache->limitUserApiCall(1);
        if ($rateLimit['allowed']) {
            $this->info('   ✅ Rate limiting working');
        } else {
            $this->error('   ❌ Rate limiting failed');
        }
        
        // Show stats
        $this->info('3. Cache Statistics:');
        $stats = $cache->getStats();
        
        $this->table(
            ['Metric', 'Value'],
            [
                ['Positions cached', $stats['positions'] ?? 0],
                ['Prices cached', $stats['prices'] ?? 0],
                ['Memory used', $stats['memory_used'] ?? 'N/A'],
                ['Memory peak', $stats['memory_peak'] ?? 'N/A']
            ]
        );
        
        $this->info('🎉 All tests completed successfully!');
        
        return 0;
    }
}