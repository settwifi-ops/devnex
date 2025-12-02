<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TradingExecutionService;
use Illuminate\Support\Facades\Log;

class UpdateFloatingPnLCommand extends Command
{
    protected $signature = 'trading:update-floating-pnl';
    protected $description = 'Update floating PnL every minute';

    public function handle()
    {
        $this->info('Starting floating PnL update...');
        
        try {
            $service = app(TradingExecutionService::class);
            $service->updateAllFloatingPnL();
            
            $this->info('Floating PnL update completed successfully.');
            Log::info('Floating PnL update executed', ['time' => now()]);
        } catch (\Exception $e) {
            $this->error('Error in floating PnL update: ' . $e->getMessage());
            Log::error('Floating PnL update failed', [
                'error' => $e->getMessage()
            ]);
        }
    }
}