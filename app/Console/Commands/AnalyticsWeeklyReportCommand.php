<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PerformanceAnalyticsService;
use Illuminate\Support\Facades\Log;

class AnalyticsWeeklyReportCommand extends Command
{
    protected $signature = 'analytics:weekly-report';
    protected $description = 'Generate weekly analytics report on Sunday at 00:00';

    public function handle()
    {
        $this->info('Generating weekly analytics report...');
        
        try {
            $analytics = app(PerformanceAnalyticsService::class);
            $analytics->analyzeRegimeSpecificPerformance(30);
            $analytics->analyzeAIDecisionAccuracy(30);
            $analytics->getTopPerformingSymbols(5, 30);
            
            $this->info('Weekly analytics report generated successfully.');
            Log::info('Weekly analytics report generated', ['time' => now()]);
        } catch (\Exception $e) {
            $this->error('Error generating weekly report: ' . $e->getMessage());
            Log::error('Weekly analytics report failed', [
                'error' => $e->getMessage()
            ]);
        }
    }
}