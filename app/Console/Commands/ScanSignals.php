<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SignalScannerService;
use App\Services\TradingExecutionService;

class ScanSignals extends Command
{
    protected $signature = 'signals:scan 
                          {--execute : Execute trades for high confidence signals}';
    
    protected $description = 'Scan for high confidence trading signals (score > 70, confidence > 60)';

    public function handle(SignalScannerService $scannerService, TradingExecutionService $tradingService)
    {
        $this->info('🔍 Scanning signals database for high confidence signals...');
        
        $highConfidenceSignals = $scannerService->scanHighConfidenceSignals();
        
        if ($highConfidenceSignals->count() > 0) {
            $this->info("🎯 Found {$highConfidenceSignals->count()} high confidence signals:");
            
            $this->table(
                ['Symbol', 'Name', 'Score', 'Confidence'],
                $highConfidenceSignals->map(function($signal) {
                    return [
                        $signal->symbol,
                        $signal->name ?? 'N/A',
                        $signal->enhanced_score . '%',
                        $signal->smart_confidence . '%'
                    ];
                })
            );

            // Generate AI decision dari signals
            if ($this->option('execute')) {
                $this->info('⚡ Generating AI decisions from high confidence signals...');
                $decisions = $scannerService->generateDecisionsFromSignals();
                
                if (!empty($decisions)) {
                    $this->info("✅ Generated " . count($decisions) . " AI decisions:");
                    
                    foreach ($decisions as $decision) {
                        $this->info("   📊 {$decision->action} {$decision->symbol} with {$decision->confidence}% confidence");
                        $this->info("   📝 Explanation: {$decision->explanation}");
                        
                        // Execute trading untuk setiap decision yang bukan HOLD
                        if ($decision->action !== 'HOLD') {
                            $this->info("   🚀 Executing trade for {$decision->symbol}...");
                            $tradingService->executeDecision($decision);
                            $this->info("   ✅ Trade execution completed for {$decision->symbol}");
                        } else {
                            $this->info("   ⏸️  Skipping execution for {$decision->symbol} - HOLD decision");
                        }
                        
                        $this->line(''); // Empty line untuk pemisah
                    }
                    
                    $this->info('🎯 All trading executions completed');
                } else {
                    $this->error('❌ No AI decisions generated from signals');
                    $this->info('💡 Possible reasons:');
                    $this->info('   - No symbols passed confidence threshold (min 70%)');
                    $this->info('   - Recent decisions exist for symbols (12-hour cooldown)');
                    $this->info('   - Market conditions not favorable');
                }
            } else {
                $this->info('💡 Use --execute option to generate and execute AI trading decisions');
            }
        } else {
            $this->info('📭 No signals found with score > 60 and confidence > 60');
        }

        $this->info('✅ Signal scan completed');
        return Command::SUCCESS;
    }
}