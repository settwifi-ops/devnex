<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Services\TradingExecutionService;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        // Jalankan command update sektor tiap 30 menit
        $schedule->command('sectors:update')->everyFifteenMinutes();
        $schedule->command('signals:update-categories')->everyThirtyMinutes();
        $schedule->command('market:advanced-update')->everyFifteenMinutes();
        $schedule->command('market:update')->everyFiveMinutes();
        $schedule->command('signals:fetch')
                 ->everyMinute()
                 ->appendOutputTo(storage_path('logs/auto_log.txt'));; 
        $schedule->command('performance:fetch')
                 ->everyMinute()
                 ->appendOutputTo(storage_path('logs/auto_log.txt'));;
        $schedule->command('signals:analyze --limit=20')
                 ->everyFiveMinutes()
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/signal-analysis.log'));
        $schedule->command('summary:generate-top')->everyFiveMinutes();
        // 15-minute signal scan (hanya scan, tidak execute)
        // Auto PNL update setiap 15 menit
        $schedule->command('trading:auto-update-pnl')
                 ->everyMinute()
                 ->withoutOverlapping();

        // Auto close positions setiap 30 menit
        $schedule->command('trading:auto-close')
                 ->everyThirtyMinutes()
                 ->withoutOverlapping();

        // Signal-based trading setiap 2 jam
        $schedule->command('signals:scan --execute')
                 ->everyTenMinutes()
                 ->withoutOverlapping();
        // SL/TP Monitoring - setiap 30 detik (untuk manual SL/TP)
        $schedule->call(function () {
            app(TradingExecutionService::class)->executeSLTPMonitoring();
        })->everyThirtySeconds();
        // Check expired pending orders setiap menit
        $schedule->call(function () {
            app(\App\Services\RealTradingExecutionService::class)->checkPendingOrders();
        })->everyMinute()->name('check-pending-orders')->withoutOverlapping();
        // Auto-close rules - setiap menit (untuk auto rules)
        $schedule->call(function () {
            app(TradingExecutionService::class)->autoClosePositions();
        })->everyMinute();

        // Update floating PnL - setiap menit
        $schedule->call(function () {
            app(TradingExecutionService::class)->updateAllFloatingPnL();
        })->everyMinute();
        // ✅ NEW: Performance analytics
        $schedule->call(function () {
            $analytics = app(\App\Services\PerformanceAnalyticsService::class);
            $analytics->generateDailyReport();
            $analytics->analyzeRegimePerformance(7); // Weekly analysis
        })->dailyAt('23:00');

        $schedule->call(function () {
            $analytics = app(\App\Services\PerformanceAnalyticsService::class);
            $analytics->analyzeRegimeSpecificPerformance(30);
            $analytics->analyzeAIDecisionAccuracy(30);
            $analytics->getTopPerformingSymbols(5, 30);
        })->sundays()->at('00:00');
            // ✅ NEW: Weekly optimization
        $schedule->call(function () {
            $adaptive = app(\App\Services\AdaptiveLearningService::class);
            $optimization = $adaptive->getOptimizationRecommendations();
            
            // Log optimization results
            Log::info("🔄 Weekly Strategy Optimization Completed", [
                'recommendations' => $optimization['recommendations'],
                'timestamp' => now()
            ]);
        })->sundays()->at('02:00');
        // Daily optimization at market close
        $schedule->call(function () {
            app(\App\Services\AdaptiveLearningService::class)->dailyOptimization();
        })->dailyAt('00:00');
        
        // Weekly deep learning
        $schedule->call(function () {
            app(\App\Services\AdaptiveLearningService::class)->weeklyDeepLearning();
        })->sundays()->at('02:00');
        
        // Hourly performance monitoring
        $schedule->call(function () {
            app(\App\Services\AdaptiveLearningService::class)->hourlyPerformanceCheck();
        })->hourly();
    }

    protected $commands = [
        Commands\TestBinanceCommand::class,
        Commands\TestOpenAICommand::class,
        Commands\CheckSignalsCommand::class,
        Commands\UpdateSignalAnalysisCommand::class,
        Commands\DebugSignalAnalysisCommand::class,
        Commands\CheckDatabaseCommand::class,
        Commands\TestRealTrading::class,
        ];


    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}