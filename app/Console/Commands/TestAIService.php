<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AITradingService;
use App\Services\BinanceService;

class TestAIService extends Command
{
    protected $signature = 'trading:test-ai';
    protected $description = 'Test AI Trading Service and Binance connection';

    public function handle(AITradingService $aiService, BinanceService $binanceService)
    {
        $this->info('🧪 Testing AI Trading Service...');

        // Test Binance Connection
        $this->info('1. Testing Binance connection...');
        if ($binanceService->testConnection()) {
            $this->info('   ✅ Binance connection successful');
            
            // Test price fetch
            $btcPrice = $binanceService->getCurrentPrice('BTC');
            $this->info("   ✅ BTC Price: \${$btcPrice}");
        } else {
            $this->error('   ❌ Binance connection failed');
        }

        // Test GPT Connection
        $this->info('2. Testing GPT connection...');
        if ($aiService->testConnection()) {
            $this->info('   ✅ GPT connection successful');
        } else {
            $this->error('   ❌ GPT connection failed - check API key');
            return Command::FAILURE;
        }

        // Test Market Data
        $this->info('3. Testing market data analysis...');
        $marketData = $binanceService->getMarketSummary('BTC', '1h', 50);
        
        if ($marketData) {
            $this->info('   ✅ Market data analysis successful');
            $this->line("      RSI: " . round($marketData['indicators']['rsi'], 2));
            $this->line("      MACD: " . round($marketData['indicators']['macd']['macd_line'], 4));
        } else {
            $this->error('   ❌ Market data analysis failed');
        }

        $this->info('🎯 All tests completed!');
        return Command::SUCCESS;
    }
}