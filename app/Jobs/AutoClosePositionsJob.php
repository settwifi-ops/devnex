<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\TradingExecutionService;
use Illuminate\Support\Facades\Log;

class AutoClosePositionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;
    public $tries = 3;
    
    public function __construct()
    {
        $this->onQueue('default');
    }
    
    public function handle()
    {
        try {
            $service = app(TradingExecutionService::class);
            $service->autoClosePositions();
            Log::debug('Auto close positions executed successfully');
        } catch (\Exception $e) {
            Log::error('Auto Close Positions Job Failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}