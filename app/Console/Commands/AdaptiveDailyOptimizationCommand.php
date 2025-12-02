<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AdaptiveLearningService;
use Illuminate\Support\Facades\Log;

class AdaptiveDailyOptimizationCommand extends Command
{
    protected $signature = 'adaptive:daily-optimization';
    protected $description = 'Daily optimization at 00:00';

    public function handle()
    {
        $this->info('Starting daily optimization...');
        
        try {
            $service = app(AdaptiveLearningService::class);
            $service->dailyOptimization();
            
            $this->info('Daily optimization completed successfully.');
            Log::info('Daily optimization executed', ['time' => now()]);
        } catch (\Exception $e) {
            $this->error('Error in daily optimization: ' . $e->getMessage());
            Log::error('Daily optimization failed', [
                'error' => $e->getMessage()
            ]);
        }
    }
}