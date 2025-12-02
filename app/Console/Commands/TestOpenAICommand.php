<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OpenAIService;
use App\Services\BinanceService;

class TestOpenAICommand extends Command
{
    protected $signature = 'test:openai {symbol=btc}';
    protected $description = 'Test OpenAI API connection and analysis';

    public function handle()
    {
        $symbol = $this->argument('symbol');
        
        $this->info('🔌 Testing OpenAI API Connection...');
        
        try {
            $openAIService = new OpenAIService();
            
            // Test connection
            if ($openAIService->testConnection()) {
                $this->info('✅ OpenAI API is accessible');
            } else {
                $this->error('❌ OpenAI API connection failed');
                return;
            }

            $this->info("\n🧪 Testing AI Analysis with sample data...");
            
            // Create sample signal data
            $sampleSignal = (object)[
                'symbol' => $symbol,
                'current_price' => 94336.52,
                'enhanced_score' => 85,
                'oi_change' => 15.2,
                'funding_rate' => 0.01,
                'volume_spike_ratio' => 2.8,
                'smart_confidence' => 78,
                'trend_strength' => 82,
                'momentum_regime' => 'bullish',
                'momentum_phase' => 'accelerating'
            ];

            // Get some candle data
            $binanceService = new BinanceService();
            $candleData = $binanceService->getCandleData($symbol, '1h', 10);
            
            if (!$candleData) {
                $this->error('❌ Failed to get candle data for testing');
                return;
            }

            $this->info("📊 Analyzing {$symbol} with AI...");
            
            $analysis = $openAIService->analyzeTradingSignal($sampleSignal, $candleData);
            
            $this->info("\n🎯 AI ANALYSIS RESULTS:");
            $this->info("======================");
            
            foreach ($analysis as $key => $value) {
                $this->info("{$key}: {$value}");
            }

        } catch (Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
        }
    }
}