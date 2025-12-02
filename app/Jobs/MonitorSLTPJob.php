<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\TradingExecutionService;
use Illuminate\Support\Facades\Log;

class MonitorSLTPJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 30;
    public $tries = 3;
    
    public function __construct()
    {
        $this->onQueue('high');
    }
    
    public function handle()
    {
        try {
            $service = app(TradingExecutionService::class);
            $service->executeSLTPMonitoring();
            Log::debug('SLTP monitoring executed successfully');
        } catch (\Exception $e) {
            Log::error('SLTP Monitoring Job Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    
    public function failed(\Throwable $exception)
    {
        Log::critical('SLTP Monitoring Job Permanently Failed', [
            'error' => $exception->getMessage()
        ]);
    }
}