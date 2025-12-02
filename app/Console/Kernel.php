<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ==================== 1. REAL-TIME JOBS ====================
            // Check expired orders setiap 5 menit
        $schedule->command('trading:monitor --check-expired')
            ->everyFiveMinutes()
            ->withoutOverlapping();
        
        // Add stop loss ke filled orders setiap 10 menit
        $schedule->command('trading:monitor --add-sl')
            ->everyTenMinutes()
            ->withoutOverlapping();
        
        // Full monitoring setiap jam
        $schedule->command('trading:monitor')
            ->hourly()
            ->withoutOverlapping();
        // SL/TP Monitoring - setiap 30 detik
        $schedule->command('trading:monitor-sltp')
                 ->everyMinute()
                 ->name('sltp-monitoring')
                 ->withoutOverlapping(40)
                 ->runInBackground();
        
        // Auto-close positions - setiap menit
        $schedule->command('trading:auto-close-positions')
                 ->everyMinute()
                 ->name('auto-close-positions')
                 ->withoutOverlapping(70)
                 ->runInBackground();
        
        // Update floating PnL - setiap menit
        $schedule->command('trading:update-floating-pnl')
                 ->everyMinute()
                 ->name('update-floating-pnl')
                 ->withoutOverlapping(70)
                 ->runInBackground();
        
        // ==================== 2. HIGH FREQUENCY JOBS ====================
        
        // Market updates - every 5 minutes
        $schedule->command('market:advanced-update')
                 ->everyThirtyMinutes()
                 ->name('market-advanced-update')
                 ->withoutOverlapping(600)
                 ->runInBackground();
                 
        $schedule->command('market:update')
                 ->everyThirtyMinutes()
                 ->name('market-basic-update')
                 ->withoutOverlapping(300)
                 ->runInBackground();
        
        // Signals fetching - every 5 minutes
        $schedule->command('signals:fetch')
                 ->everyFiveMinutes()
                 ->name('signals-fetch')
                 ->withoutOverlapping(300)
                 ->runInBackground();
                 
        $schedule->command('performance:fetch')
                 ->everyFiveMinutes()
                 ->name('performance-fetch')
                 ->withoutOverlapping(300)
                 ->runInBackground();
        
        // Signal analysis - every 5 minutes
        $schedule->command('signals:analyze --limit=5')
                 ->everyFiveMinutes()
                 ->name('signals-analyze')
                 ->withoutOverlapping(900)
                 ->runInBackground();
        
        // Summary generation - every 5 minutes
        $schedule->command('summary:generate-top')
                 ->everyFiveMinutes()
                 ->name('summary-generation')
                 ->withoutOverlapping(300)
                 ->runInBackground();
        
        // Auto PNL update - every 5 minutes
        $schedule->command('trading:auto-update-pnl')
                 ->everyFiveMinutes()
                 ->name('auto-pnl-update')
                 ->withoutOverlapping(300)
                 ->runInBackground();
        
        // ==================== 3. MEDIUM FREQUENCY JOBS ====================
        
        // Signal scan with execution - every 10 minutes
        $schedule->command('signals:scan --execute')
                 ->everyTenMinutes()
                 ->name('trading-execution')
                 ->withoutOverlapping(1800)
                 ->runInBackground();
        
        // Sector updates - every 15 minutes
        $schedule->command('sectors:update')
                 ->everyFifteenMinutes()
                 ->name('sectors-update')
                 ->withoutOverlapping(1800)
                 ->runInBackground();
        
        // Signal categories - every 30 minutes
        $schedule->command('signals:update-categories')
                 ->everyThirtyMinutes()
                 ->name('signal-categories-update')
                 ->withoutOverlapping(2700)
                 ->runInBackground();
        
        // Auto close positions - every 30 minutes
        $schedule->command('trading:auto-close')
                 ->everyThirtyMinutes()
                 ->name('auto-close-batch')
                 ->withoutOverlapping(2700)
                 ->runInBackground();
        
        // ==================== 4. LOW FREQUENCY JOBS ====================
        
        // Hourly performance monitoring
        $schedule->command('adaptive:hourly-check')
                 ->hourly()
                 ->name('hourly-performance-check')
                 ->withoutOverlapping(300)
                 ->runInBackground();
        
        // Daily performance analytics - 23:00
        $schedule->command('analytics:daily-report')
                 ->dailyAt('23:00')
                 ->name('daily-performance-analytics')
                 ->withoutOverlapping(3600)
                 ->runInBackground();
        
        // Daily optimization - 00:00
        $schedule->command('adaptive:daily-optimization')
                 ->dailyAt('00:00')
                 ->name('daily-optimization')
                 ->withoutOverlapping(7200)
                 ->runInBackground();
        
        // Weekly analytics - Sunday at 00:00
        $schedule->command('analytics:weekly-report')
                 ->sundays()->at('00:00')
                 ->name('weekly-analytics')
                 ->withoutOverlapping(10800)
                 ->runInBackground();
        
        // Weekly optimization - Sunday at 02:00
        $schedule->command('adaptive:weekly-optimization')
                 ->sundays()->at('02:00')
                 ->name('weekly-optimization')
                 ->withoutOverlapping(14400)
                 ->runInBackground();
        
        // Weekly deep learning - Sunday at 02:10
        $schedule->command('adaptive:weekly-deep-learning')
                 ->sundays()->at('02:10')
                 ->name('weekly-deep-learning')
                 ->withoutOverlapping(21600)
                 ->runInBackground();
        
        // ==================== 5. MAINTENANCE JOBS ====================
        
        // Queue monitoring - every 5 minutes
        $schedule->command('queue:monitor default,high,low --max=1000')
                 ->everyFiveMinutes()
                 ->name('queue-monitor')
                 ->withoutOverlapping(300)
                 ->runInBackground();
        
        // Scheduler health check - hourly
        $schedule->command('scheduler:health-check')
                 ->hourly()
                 ->name('scheduler-health-check')
                 ->withoutOverlapping(300)
                 ->runInBackground();
        
        // Log rotation - daily at 04:00
        $schedule->command('log:clean --days=7')
                 ->dailyAt('04:00')
                 ->name('log-clean')
                 ->withoutOverlapping(1800)
                 ->runInBackground();
        
        // Cache cleanup - daily at 03:00
        $schedule->command('cache:prune-stale-tags')
                 ->dailyAt('03:00')
                 ->name('cache-prune')
                 ->withoutOverlapping(1800)
                 ->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected $commands = [
        // Existing commands
        Commands\TestBinanceCommand::class,
        Commands\TestOpenAICommand::class,
        Commands\CheckSignalsCommand::class,
        Commands\UpdateSignalAnalysisCommand::class,
        Commands\DebugSignalAnalysisCommand::class,
        Commands\CheckDatabaseCommand::class,
        
        // New scheduler commands
        Commands\MonitorSLTPCommand::class,
        Commands\AutoClosePositionsCommand::class,
        Commands\UpdateFloatingPnLCommand::class,
        Commands\AnalyticsDailyReportCommand::class,
        Commands\AnalyticsWeeklyReportCommand::class,
        Commands\AdaptiveHourlyCheckCommand::class,
        Commands\AdaptiveDailyOptimizationCommand::class,
        Commands\AdaptiveWeeklyOptimizationCommand::class,
        Commands\AdaptiveWeeklyDeepLearningCommand::class,
        Commands\SchedulerHealthCheckCommand::class,
        Commands\MonitorRealTrading::class,
    ];

    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
    
    /**
     * Get the timeout for long running commands.
     */
    protected function getCommandTimeout(): int
    {
        return 7200;
    }
}
