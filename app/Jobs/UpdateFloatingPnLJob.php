<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\TradingExecutionService;
use Illuminate\Support\Facades\Log;

class UpdateFloatingPnLJob implements ShouldQueue
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
            $service->updateAllFloatingPnL();
            Log::debug('Floating PnL update executed successfully');
        } catch (\Exception $e) {
            Log::error('Update Floating PnL Job Failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}