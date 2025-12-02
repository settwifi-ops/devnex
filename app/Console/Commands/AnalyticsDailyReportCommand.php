<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PerformanceAnalyticsService;
use Illuminate\Support\Facades\Log;

class AnalyticsDailyReportCommand extends Command
{
    protected $signature = 'analytics:daily-report';
    protected $description = 'Generate daily analytics report at 23:00';

    public function handle()
    {
        $this->info('Generating daily analytics report...');
        
        try {
            $analytics = app(PerformanceAnalyticsService::class);
            $analytics->generateDailyReport();
            $analytics->analyzeRegimePerformance(7);
            
            $this->info('Daily analytics report generated successfully.');
            Log::info('Daily analytics report generated', ['time' => now()]);
        } catch (\Exception $e) {
            $this->error('Error generating daily report: ' . $e->getMessage());
            Log::error('Daily analytics report failed', [
                'error' => $e->getMessage()
            ]);
        }
    }
}