<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Signal;

class CheckSignalsCommand extends Command
{
    protected $signature = 'signals:check';
    protected $description = 'Check signals data in database';

    public function handle()
    {
        $this->info('🔍 Checking signals in database...');

        // Total signals
        $totalSignals = Signal::count();
        $this->info("📊 Total signals in database: {$totalSignals}");

        if ($totalSignals === 0) {
            $this->error('❌ No signals found in database!');
            return;
        }

        // Active signals
        $activeSignals = Signal::where('is_active_signal', true)->count();
        $this->info("🎯 Active signals: {$activeSignals}");

        // Signals dengan last_summary_count = 0 or null
        $neverAnalyzed = Signal::where(function($query) {
            $query->whereNull('last_summary_count')
                  ->orWhere('last_summary_count', 0);
        })->count();
        $this->info("🆕 Never analyzed: {$neverAnalyzed}");

        // Signals dengan appearance_count > last_summary_count
        $countIncreased = Signal::whereRaw('appearance_count > last_summary_count')->count();
        $this->info("📈 Appearance count increased: {$countIncreased}");

        // Signals needing update (correct logic)
        $needsUpdate = Signal::where(function($query) {
            $query->whereNull('last_summary_count')
                  ->orWhere('last_summary_count', 0)
                  ->orWhereRaw('appearance_count > last_summary_count')
                  ->orWhere(function($q) {
                      $q->whereNull('ai_summary')
                        ->orWhere('ai_summary', '')
                        ->orWhere('ai_summary', 'like', '%Error%');
                  });
        })->count();
        $this->info("🔄 Signals needing AI update: {$needsUpdate}");

        // Sample signals data
        $this->info("\n📋 Sample of 5 signals (including inactive):");
        $sampleSignals = Signal::limit(5)->get();
        
        foreach ($sampleSignals as $signal) {
            $needsAnalysis = $this->needsAnalysis($signal);
            $this->info("---");
            $this->info("ID: {$signal->id}");
            $this->info("Symbol: {$signal->symbol}");
            $this->info("Active: " . ($signal->is_active_signal ? 'Yes' : 'No'));
            $this->info("Appearance Count: {$signal->appearance_count}");
            $this->info("Last Summary Count: " . ($signal->last_summary_count ?? 'NULL'));
            $this->info("Enhanced Score: {$signal->enhanced_score}");
            $this->info("Needs Analysis: " . ($needsAnalysis ? 'Yes' : 'No'));
            $this->info("Reason: " . $this->getAnalysisReason($signal));
        }

        $this->info("\n💡 RECOMMENDATION:");
        if ($needsUpdate > 0) {
            $this->info("Run: php artisan signals:analyze --limit={$needsUpdate}");
        } else {
            $this->info("All signals are up to date!");
        }
    }

    private function needsAnalysis($signal)
    {
        if (is_null($signal->last_summary_count) || $signal->last_summary_count == 0) {
            return true;
        }
        if ($signal->appearance_count > $signal->last_summary_count) {
            return true;
        }
        if (empty($signal->ai_summary) || strpos($signal->ai_summary, 'Error') !== false) {
            return true;
        }
        return false;
    }

    private function getAnalysisReason($signal)
    {
        if (is_null($signal->last_summary_count) || $signal->last_summary_count == 0) {
            return "Never analyzed";
        }
        if ($signal->appearance_count > $signal->last_summary_count) {
            return "Appearance count increased";
        }
        if (empty($signal->ai_summary)) {
            return "AI summary empty";
        }
        if (strpos($signal->ai_summary, 'Error') !== false) {
            return "Previous analysis had error";
        }
        return "Up to date";
    }
}