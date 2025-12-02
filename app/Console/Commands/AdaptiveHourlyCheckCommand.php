<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AdaptiveLearningService;
use Illuminate\Support\Facades\Log;

class AdaptiveHourlyCheckCommand extends Command
{
    protected $signature = 'adaptive:hourly-check';
    protected $description = 'Hourly performance check for adaptive learning';

    public function handle()
    {
        $this->info('Starting hourly performance check...');
        
        try {
            $service = app(AdaptiveLearningService::class);
            $service->hourlyPerformanceCheck();
            
            $this->info('Hourly performance check completed successfully.');
            Log::info('Hourly performance check executed', ['time' => now()]);
        } catch (\Exception $e) {
            $this->error('Error in hourly performance check: ' . $e->getMessage());
            Log::error('Hourly performance check failed', [
                'error' => $e->getMessage()
            ]);
        }
    }
}