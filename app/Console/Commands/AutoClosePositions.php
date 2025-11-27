<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TradingExecutionService;

class AutoClosePositions extends Command
{
    protected $signature = 'trading:auto-close';
    protected $description = 'Auto close positions based on rules (stop loss, take profit, time limit)';

    public function handle(TradingExecutionService $tradingService)
    {
        $this->info('🔒 Auto-closing positions based on rules...');
        
        $result = $tradingService->autoClosePositions();
        
        $this->info("✅ Auto-close completed: {$result['closed']} positions closed, Total PNL: \${$result['total_pnl']}");
        return Command::SUCCESS;
    }
}