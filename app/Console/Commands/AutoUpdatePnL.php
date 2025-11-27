<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TradingExecutionService;

class AutoUpdatePnL extends Command
{
    protected $signature = 'trading:auto-update-pnl';
    protected $description = 'Auto update floating PNL for all open positions';

    public function handle(TradingExecutionService $tradingService)
    {
        $this->info('🔄 Auto-updating floating PNL for all open positions...');
        
        $updatedCount = $tradingService->updateAllFloatingPnL();
        
        $this->info("✅ Floating PNL updated for {$updatedCount} positions");
        return Command::SUCCESS;
    }
}