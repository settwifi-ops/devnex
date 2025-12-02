<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class SchedulerHealthCheckCommand extends Command
{
    protected $signature = 'scheduler:health-check';
    protected $description = 'Scheduler health check every hour';

    public function handle()
    {
        $health = [
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'cpu_load' => sys_getloadavg()[0] ?? 0,
            'queue_size' => Queue::size(),
            'timestamp' => now()->toISOString(),
        ];

        Log::info('Scheduler Health Status', $health);
        
        if ($health['memory_usage_mb'] > 512) {
            Log::warning('High memory usage in scheduler', $health);
        }
        
        if ($health['cpu_load'] > 4.0) {
            Log::warning('High CPU load in scheduler', $health);
        }
        
        $this->info('Scheduler health check completed.');
    }
}