<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AdaptiveLearningService;
use Illuminate\Support\Facades\Log;

class AdaptiveWeeklyDeepLearningCommand extends Command
{
    protected $signature = 'adaptive:weekly-deep-learning';
    protected $description = 'Weekly deep learning on Sunday at 02:10';

    public function handle()
    {
        $this->info('Starting weekly deep learning...');
        
        try {
            $service = app(AdaptiveLearningService::class);
            $service->weeklyDeepLearning();
            
            $this->info('Weekly deep learning completed successfully.');
            Log::info('Weekly deep learning executed', ['time' => now()]);
        } catch (\Exception $e) {
            $this->error('Error in weekly deep learning: ' . $e->getMessage());
            Log::error('Weekly deep learning failed', [
                'error' => $e->getMessage()
            ]);
        }
    }
}