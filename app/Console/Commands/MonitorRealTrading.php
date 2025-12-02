<?php
// app/Console/Commands/MonitorRealTrading.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RealTradingExecutionService;
use App\Models\PendingOrder;
use Illuminate\Support\Facades\Log;

class MonitorRealTrading extends Command
{
    protected $signature = 'trading:monitor 
                           {--user= : User ID}
                           {--add-sl : Add stop loss to filled orders}
                           {--check-expired : Check expired orders}';
    
    protected $description = 'Monitor and maintain real trading orders';
    
    protected $tradingService;
    
    public function __construct(RealTradingExecutionService $tradingService)
    {
        parent::__construct();
        $this->tradingService = $tradingService;
    }
    
    public function handle()
    {
        $userId = $this->option('user');
        
        if ($this->option('add-sl')) {
            $this->info('Adding stop loss to filled orders...');
            $results = $this->tradingService->addStopLossToFilledOrders($userId);
            
            $addedCount = $results['stop_loss_added'] ?? 0;
            $failedCount = $results['failed'] ?? 0;
            $totalChecked = $results['total_checked'] ?? 0;
            
            $this->info("✅ Added stop loss to {$addedCount} orders");
            $this->info("   📊 Total checked: {$totalChecked} orders");
            if ($failedCount > 0) {
                $this->error("   ❌ Failed: {$failedCount} orders");
            }
            
            return 0;
        }
        
        if ($this->option('check-expired')) {
            $this->info('Checking expired orders...');
            $results = $this->tradingService->checkPendingOrders();
            
            $cancelledCount = $results['cancelled'] ?? 0;
            $failedCount = $results['failed'] ?? 0;
            $totalExpired = $results['expired'] ?? 0;
            
            $this->info("✅ Cancelled {$cancelledCount} expired orders");
            if ($failedCount > 0) {
                $this->error("   ❌ Failed to cancel {$failedCount} orders");
            }
            $this->info("   📊 Total expired: {$totalExpired} orders");
            
            return 0;
        }
        
        // Default: lakukan semua (tanpa listing)
        $this->info('Running complete monitoring...');
        
        // 1. Check expired orders
        $this->info('1. Checking expired orders...');
        $expiredResults = $this->tradingService->checkPendingOrders();
        
        $cancelledCount = $expiredResults['cancelled'] ?? 0;
        $failedExpired = $expiredResults['failed'] ?? 0;
        $totalExpired = $expiredResults['expired'] ?? 0;
        
        $this->info("   ✅ Cancelled {$cancelledCount} expired orders");
        if ($failedExpired > 0) {
            $this->error("   ❌ Failed to cancel {$failedExpired} orders");
        }
        $this->info("   📊 Total expired: {$totalExpired} orders");
        
        // 2. Add stop loss
        $this->info('2. Adding stop loss to filled orders...');
        $slResults = $this->tradingService->addStopLossToFilledOrders($userId);
        
        $addedCount = $slResults['stop_loss_added'] ?? 0;
        $failedSL = $slResults['failed'] ?? 0;
        $totalChecked = $slResults['total_checked'] ?? 0;
        
        $this->info("   ✅ Added stop loss to {$addedCount} orders");
        $this->info("   📊 Total checked: {$totalChecked} orders");
        if ($failedSL > 0) {
            $this->error("   ❌ Failed: {$failedSL} orders");
        }
        
        $this->info("\n🎯 Monitoring completed!");
        
        return 0;
    }
}