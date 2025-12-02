<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TradingExecutionService;
use Illuminate\Support\Facades\Log;

class AutoClosePositionsCommand extends Command
{
    protected $signature = 'trading:auto-close-positions';
    protected $description = 'Auto close positions every minute';

    public function handle()
    {
        $this->info('Starting auto close positions...');
        
        try {
            $service = app(TradingExecutionService::class);
            $service->autoClosePositions();
            
            $this->info('Auto close positions completed successfully.');
            Log::info('Auto close positions executed', ['time' => now()]);
        } catch (\Exception $e) {
            $this->error('Error in auto close positions: ' . $e->getMessage());
            Log::error('Auto close positions failed', [
                'error' => $e->getMessage()
            ]);
        }
    }
}