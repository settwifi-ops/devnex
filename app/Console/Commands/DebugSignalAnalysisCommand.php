<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http; // TAMBAH INI
use App\Services\TradingAnalysisService;
use App\Services\BinanceService;
use App\Services\OpenAIService;
use App\Models\Signal;

class DebugSignalAnalysisCommand extends Command
{
    protected $signature = 'debug:signal {id=1}';
    protected $description = 'Debug signal analysis step by step';

    public function handle()
    {
        $signalId = $this->argument('id');
        
        $this->info("🔧 Debugging Signal Analysis for ID: {$signalId}");
        $this->info("=============================================");

        // Step 1: Check Signal Data
        $this->info("\n📋 STEP 1: Checking Signal Data...");
        $signal = Signal::find($signalId);
        
        if (!$signal) {
            $this->error("❌ Signal ID {$signalId} not found");
            return;
        }

        $this->info("✅ Signal Found:");
        $this->info("   Symbol: {$signal->symbol}");
        $this->info("   Active: " . ($signal->is_active_signal ? 'Yes' : 'No'));
        $this->info("   Current Price: {$signal->current_price}");
        $this->info("   Enhanced Score: {$signal->enhanced_score}");
        $this->info("   Appearance Count: {$signal->appearance_count}");
        $this->info("   Last Summary Count: " . ($signal->last_summary_count ?? 'NULL'));

        // Step 2: Test Binance Data
        $this->info("\n📊 STEP 2: Testing Binance Data...");
        $binanceService = new BinanceService();
        
        $candleData = $binanceService->getCandleData($signal->symbol, '1h', 10);
        
        if ($candleData && count($candleData) > 0) {
            $this->info("✅ Binance Data OK - Got " . count($candleData) . " candles");
            
            $latestCandle = end($candleData);
            $this->info("   Latest Candle:");
            $this->info("   - Open: {$latestCandle[1]}");
            $this->info("   - High: {$latestCandle[2]}");
            $this->info("   - Low: {$latestCandle[3]}");
            $this->info("   - Close: {$latestCandle[4]}");
            $this->info("   - Volume: {$latestCandle[5]}");
        } else {
            $this->error("❌ Binance Data Failed");
            return;
        }

        // Step 3: Test OpenAI with Simple Prompt
        $this->info("\n🤖 STEP 3: Testing OpenAI with Simple Prompt...");
        
        // Test dengan prompt sangat simple
        $testPrompt = "Reply with just 'OK TEST'";
        
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $testPrompt
                    ]
                ],
                'max_tokens' => 10,
                'temperature' => 0.1
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                $aiResponse = $responseData['choices'][0]['message']['content'] ?? 'No response';
                $this->info("✅ OpenAI Simple Test: SUCCESS");
                $this->info("   Response: '{$aiResponse}'");
            } else {
                $this->error("❌ OpenAI Simple Test Failed");
                $this->error("   Status: " . $response->status());
                $this->error("   Error: " . $response->body());
                return;
            }

        } catch (\Exception $e) {
            $this->error("❌ OpenAI Test Exception: " . $e->getMessage());
            return;
        }

        // Step 4: Test Full Analysis
        $this->info("\n🎯 STEP 4: Testing Full Analysis...");
        
        try {
            $openAIService = new OpenAIService();
            $analysis = $openAIService->analyzeTradingSignal($signal, $candleData);
            
            if ($analysis && isset($analysis['summary'])) {
                $this->info("✅ Full Analysis SUCCESS!");
                $this->info("\n📊 ANALYSIS RESULTS:");
                foreach ($analysis as $key => $value) {
                    $this->info("   {$key}: {$value}");
                }
                
                // Step 5: Test Database Update
                $this->info("\n💾 STEP 5: Testing Database Update...");
                $tradingService = new TradingAnalysisService();
                $updateSuccess = $tradingService->updateSignalWithAnalysis($signal, $analysis);
                
                if ($updateSuccess) {
                    $this->info("✅ Database Update: SUCCESS");
                    $updatedSignal = Signal::find($signalId);
                    $this->info("   AI Summary: " . ($updatedSignal->ai_summary ?? 'N/A'));
                    $this->info("   Probability: " . ($updatedSignal->ai_probability ?? 'N/A') . '%');
                } else {
                    $this->error("❌ Database Update: FAILED");
                }
                
            } else {
                $this->error("❌ Full Analysis Failed - No analysis returned");
                $this->error("   Analysis data: " . json_encode($analysis));
            }

        } catch (\Exception $e) {
            $this->error("❌ Full Analysis Exception: " . $e->getMessage());
            $this->error("   Stack trace: " . $e->getTraceAsString());
        }

        $this->info("\n🔍 Debug Complete!");
    }
}