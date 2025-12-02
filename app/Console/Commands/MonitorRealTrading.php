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
                           {--check-expired : Check expired orders}
                           {--list-sl : List active stop loss orders}';
    
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
            $count = $this->tradingService->addStopLossToFilledOrders($userId);
            $this->info("✅ Added stop loss to {$count} orders");
        }
        
        if ($this->option('check-expired')) {
            $this->info('Checking expired orders...');
            $count = $this->tradingService->checkPendingOrders();
            $this->info("✅ Cancelled {$count} expired orders");
        }
        
        if ($this->option('list-sl')) {
            $this->info('Listing active stop loss orders...');
            $slOrders = $this->tradingService->getActiveStopLossOrders($userId);
            
            if (empty($slOrders)) {
                $this->info("No active stop loss orders found");
            } else {
                $this->table(
                    ['Order ID', 'Symbol', 'SL Price', 'SL Order ID', 'Status', 'Triggered'],
                    array_map(function($order) {
                        return [
                            $order['order_id'],
                            $order['symbol'],
                            $order['stop_loss_price'],
                            $order['stop_loss_id'],
                            $order['status'],
                            $order['triggered'] ? '✅ YES' : '⏳ NO'
                        ];
                    }, $slOrders)
                );
            }
        }
        
        if (!$this->option('add-sl') && !$this->option('check-expired') && !$this->option('list-sl')) {
            // Default: lakukan semua
            $this->info('Running complete monitoring...');
            
            $this->info('1. Checking expired orders...');
            $expiredCount = $this->tradingService->checkPendingOrders();
            $this->info("   ✅ Cancelled {$expiredCount} expired orders");
            
            $this->info('2. Adding stop loss to filled orders...');
            $slCount = $this->tradingService->addStopLossToFilledOrders($userId);
            $this->info("   ✅ Added stop loss to {$slCount} orders");
            
            $this->info('3. Listing active stop loss orders...');
            $slOrders = $this->tradingService->getActiveStopLossOrders($userId);
            $this->info("   📊 Found " . count($slOrders) . " active stop loss orders");
            
            $this->info("\n🎯 Monitoring completed!");
        }
        
        return 0;
    }
}