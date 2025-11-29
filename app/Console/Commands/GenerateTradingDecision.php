<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AITradingService;
use App\Services\TradingExecutionService;
use App\Models\AiDecision;

class GenerateTradingDecision extends Command
{
    protected $signature = 'trading:generate-decision 
                            {--symbols=BTC,ETH : Symbols to analyze (comma separated)}
                            {--test : Test mode without executing trades}';
    
    protected $description = 'Generate AI trading decision and execute for all users';

    public function handle(AITradingService $aiService, TradingExecutionService $executionService)
    {
        $this->info('🚀 Starting AI Trading Decision Generation...');
        
        $symbols = explode(',', $this->option('symbols'));
        $testMode = $this->option('test');

        $this->info("Analyzing symbols: " . implode(', ', $symbols));
        
        // Generate AI Decision
        $decision = $aiService->generateTradingDecision($symbols);
        
        if (!$decision) {
            $this->error('❌ Failed to generate trading decision');
            return Command::FAILURE;
        }

        $this->info("✅ Decision Generated:");
        $this->line("   Symbol: {$decision->symbol}");
        $this->line("   Action: {$decision->action}");
        $this->line("   Confidence: {$decision->confidence}%");
        $this->line("   Explanation: {$decision->explanation}");
        
        if ($testMode) {
            $this->warn('🧪 Test mode - Skipping trade execution');
            return Command::SUCCESS;
        }

        // Execute trading decision
        if ($decision->action !== 'HOLD') {
            $this->info('⚡ Executing trades for enabled users...');
            $executionService->executeDecision($decision);
            $this->info('✅ Trades executed successfully');
        } else {
            $this->info('⏸️  HOLD decision - No trades executed');
        }

        $this->info('🎯 AI Trading process completed!');
        return Command::SUCCESS;
    }
}