<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AdaptiveLearningService;
use Illuminate\Support\Facades\Log;

class AdaptiveWeeklyOptimizationCommand extends Command
{
    protected $signature = 'adaptive:weekly-optimization';
    protected $description = 'Weekly optimization on Sunday at 02:00';

    public function handle()
    {
        $this->info('Starting weekly optimization...');
        
        try {
            $service = app(AdaptiveLearningService::class);
            $optimization = $service->getOptimizationRecommendations();
            
            Log::info("?? Weekly Strategy Optimization Completed", [
                'recommendations' => $optimization['recommendations'] ?? [],
                'timestamp' => now()
            ]);
            
            $this->info('Weekly optimization completed successfully.');
        } catch (\Exception $e) {
            $this->error('Error in weekly optimization: ' . $e->getMessage());
            Log::error('Weekly optimization failed', [
                'error' => $e->getMessage()
            ]);
        }
    }
}