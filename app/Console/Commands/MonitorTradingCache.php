<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Cache\TradingCacheService;

class MonitorTradingCache extends Command
{
    protected $signature = 'trading:cache-monitor {--loop : Run in loop mode}';
    protected $description = 'Monitor trading cache performance';

    public function handle()
    {
        $cache = new TradingCacheService();
        
        if ($this->option('loop')) {
            $this->info('🔄 Starting cache monitor in loop mode (Ctrl+C to stop)');
            
            while (true) {
                $this->displayStats($cache);
                sleep(5); // Update setiap 5 detik
            }
        } else {
            $this->displayStats($cache);
        }
        
        return 0;
    }
    
    private function displayStats(TradingCacheService $cache)
    {
        $stats = $cache->getStats();
        
        $this->clearScreen();
        $this->info('📊 TRADING CACHE MONITOR');
        $this->line('Last updated: ' . now()->format('Y-m-d H:i:s'));
        $this->line('');
        
        $this->table(
            ['Metric', 'Value'],
            [
                ['Positions', $stats['positions'] ?? 0],
                ['Prices', $stats['prices'] ?? 0],
                ['Balances', $stats['balances'] ?? 0],
                ['Orders', $stats['orders'] ?? 0],
                ['User States', $stats['states'] ?? 0],
                ['Memory Used', $stats['memory_used'] ?? 'N/A'],
                ['Memory Peak', $stats['memory_peak'] ?? 'N/A'],
            ]
        );
        
        // Show top 5 positions by size
        $this->info('🎯 Sample Cached Positions:');
        $samplePositions = $cache->getPositions(1); // User ID 1 sebagai sample
        if (!empty($samplePositions)) {
            $this->table(
                ['Symbol', 'Quantity', 'Entry', 'Mark', 'P&L'],
                array_map(function($pos) {
                    return [
                        $pos['symbol'],
                        $pos['quantity'],
                        number_format($pos['entry_price'], 2),
                        number_format($pos['mark_price'], 2),
                        number_format($pos['unrealized_pnl'], 2)
                    ];
                }, array_slice($samplePositions, 0, 5))
            );
        }
    }
    
    private function clearScreen()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            system('cls');
        } else {
            system('clear');
        }
    }
}