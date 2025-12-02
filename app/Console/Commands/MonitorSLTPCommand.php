<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TradingExecutionService;
use Illuminate\Support\Facades\Log;

class MonitorSLTPCommand extends Command
{
    protected $signature = 'trading:monitor-sltp';
    protected $description = 'Monitor SL/TP positions every 30 seconds';

    public function handle()
    {
        $this->info('Starting SL/TP monitoring...');
        
        try {
            $service = app(TradingExecutionService::class);
            $service->executeSLTPMonitoring();
            
            $this->info('SL/TP monitoring completed successfully.');
            Log::info('SL/TP monitoring executed', ['time' => now()]);
        } catch (\Exception $e) {
            $this->error('Error in SL/TP monitoring: ' . $e->getMessage());
            Log::error('SL/TP monitoring failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}