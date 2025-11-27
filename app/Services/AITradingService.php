<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\AiDecision;
use App\Models\UserPosition;
use App\Models\MarketRegime;
use App\Jobs\ExecuteTradingDecisionJob;

class AITradingService
{
    private $openaiApiKey;
    private $binanceService;
    private $adaptiveLearningService;

    // Configuration
    private $minConfidenceThreshold = 70;
    private $maxDecisionsPerSymbolPerHour = 2;
    private $lossCooldownHours = 3;

    public function __construct(BinanceService $binanceService, AdaptiveLearningService $adaptiveLearningService)
    {
        $this->openaiApiKey = env('OPENAI_API_KEY');
        $this->binanceService = $binanceService;
        $this->adaptiveLearningService = $adaptiveLearningService;
        
        // Debug: Check if API key is loaded
        if (empty($this->openaiApiKey)) {
            Log::error('❌ OpenAI API Key is empty! Check .env file');
        } else {
            Log::info('✅ OpenAI API Key loaded: ' . substr($this->openaiApiKey, 0, 10) . '...');
        }
    }

    /**
     * Generate trading decision using GPT - UPDATED WITH SMART POSITION CHECKING
     */
    public function generateTradingDecision($symbols = ['BTC', 'ETH'])
    {
        // Check API key first
        if (empty($this->openaiApiKey) || $this->openaiApiKey === 'sk-your-actual-api-key-here') {
            Log::error('❌ OpenAI API Key not configured properly');
            return null;
        }

        $marketAnalysis = $this->binanceService->getMultipleMarketData($symbols);
        
        if (empty($marketAnalysis)) {
            Log::error('Failed to get market data for AI analysis');
            return null;
        }

        $decisions = [];
        
        // Generate decision untuk setiap symbol dengan smart filtering
        foreach ($symbols as $symbol) {
            if (!isset($marketAnalysis[$symbol])) {
                continue;
            }

            // ✅ SMART POSITION-BASED CHECKING
            if (!$this->canTradeSymbol($symbol, $marketAnalysis[$symbol])) {
                continue;
            }

            $decision = $this->generateSymbolDecision($symbol, $marketAnalysis[$symbol]);
            if ($decision && $this->isValidDecision($decision)) {
                $decisions[] = $decision;
            }
        }

        // Jika tidak ada decision yang bagus, return null
        if (empty($decisions)) {
            Log::info("⏭️ No high-confidence decisions generated for any symbols");
            return null;
        }

        // Execute semua decisions yang valid
        $executedDecisions = [];
        foreach ($decisions as $decisionData) {
            $decision = $this->saveAndExecuteDecision($decisionData);
            if ($decision) {
                $executedDecisions[] = $decision;
            }
        }

        Log::info("📊 Total decisions executed: " . count($executedDecisions));
        return count($executedDecisions) === 1 ? $executedDecisions[0] : $executedDecisions;
    }

    /**
     * SMART POSITION-BASED CHECKING
     */
    private function canTradeSymbol($symbol, $marketData)
    {
        $symbolWithSuffix = $symbol . 'USDT';
        
        // 1. ✅ Cek posisi aktif di UserPosition - SKIP jika ada
        if ($this->hasActiveUserPosition($symbolWithSuffix)) {
            Log::info("⏭️ Skipping {$symbol} - Active user position exists");
            return false;
        }
        
        // 2. ✅ Cek recent losses besar - SKIP jika ada loss > 5% dalam 3 jam
        if ($this->hasRecentBigLoss($symbolWithSuffix)) {
            Log::info("⏭️ Skipping {$symbol} - Recent big loss within {$this->lossCooldownHours} hours");
            return false;
        }
        
        // 3. ⚠️ Cek decision frequency - WARNING jika terlalu sering
        $this->checkDecisionFrequency($symbolWithSuffix);
        
        // 4. ✅ Boleh trade!
        Log::info("✅ {$symbol} passed all position checks - proceeding with analysis");
        return true;
    }

    /**
     * Check if user has active position for symbol
     */
    private function hasActiveUserPosition($symbolWithSuffix)
    {
        return UserPosition::where('symbol', $symbolWithSuffix)
            ->where('status', 'OPEN')
            ->exists();
    }

    /**
     * Check for recent big losses (>5%) within cooldown period - FIXED
     */
    private function hasRecentBigLoss($symbolWithSuffix)
    {
        // Query langsung ke UserPosition (lebih aman)
        $recentBigLoss = UserPosition::where('symbol', $symbolWithSuffix)
            ->where('status', 'CLOSED')
            ->where('created_at', '>=', now()->subHours($this->lossCooldownHours))
            ->where('pnl_percentage', '<', -5) // Loss > 5%
            ->exists();
            
        return $recentBigLoss;
    }

    /**
     * Check decision frequency and log warning if too frequent
     */
    private function checkDecisionFrequency($symbolWithSuffix)
    {
        $recentDecisionsCount = AiDecision::where('symbol', $symbolWithSuffix)
            ->where('created_at', '>=', now()->subHour())
            ->count();
            
        if ($recentDecisionsCount >= $this->maxDecisionsPerSymbolPerHour) {
            Log::warning("⚠️ {$symbolWithSuffix} has {$recentDecisionsCount} decisions in 1 hour - consider reducing frequency");
        }
    }

    /**
     * Generate decision untuk single symbol
     */
    private function generateSymbolDecision($symbol, $marketData)
    {
        $prompt = $this->buildSymbolPrompt($symbol, $marketData);
        
        try {
            Log::info("🚀 Sending request to OpenAI API for {$symbol}...");
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->openaiApiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4.1-mini',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are a professional AI trading expert. Analyze technical data and provide logical trading decisions. Always respond in English using ONLY the requested JSON format. Do not include any additional text, explanations, or markdown outside the JSON structure."
                    ],
                    [
                        'role' => 'user', 
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.2,
                'max_tokens' => 500,
                'response_format' => ['type' => 'json_object']
            ]);

            Log::info("📡 OpenAI API Response for {$symbol} - Status: " . $response->status());
            
            if ($response->successful()) {
                return $this->parseGPTResponse($response->json(), [$symbol => $marketData]);
            } else {
                Log::error("❌ OpenAI API Error for {$symbol} - Status: " . $response->status());
                return null;
            }
            
        } catch (\Exception $e) {
            Log::error("❌ GPT Service Exception for {$symbol}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Validate decision sebelum execute
     */
    private function isValidDecision($decision)
    {
        // Skip jika confidence terlalu rendah untuk trading
        if ($decision['action'] !== 'HOLD' && $decision['confidence'] < $this->minConfidenceThreshold) {
            Log::info("⏭️ Skipping {$decision['symbol']} - Low confidence ({$decision['confidence']}% < {$this->minConfidenceThreshold}%)");
            return false;
        }

        // Skip HOLD dengan confidence rendah
        if ($decision['action'] === 'HOLD' && $decision['confidence'] < 50) {
            Log::info("⏭️ Skipping {$decision['symbol']} - Low confidence HOLD ({$decision['confidence']}%)");
            return false;
        }

        return true;
    }

    /**
     * Save and execute decision
     */
    private function saveAndExecuteDecision($decisionData)
    {
        // Save to database
        $decision = AiDecision::create($decisionData);
        
        Log::info("✅ AI Decision Created: {$decision->action} {$decision->symbol} with {$decision->confidence}% confidence");
        
        // Dispatch job to execute trading if not HOLD dan confidence tinggi
        if ($decision->action !== 'HOLD' && $decision->confidence >= $this->minConfidenceThreshold) {
            ExecuteTradingDecisionJob::dispatch($decision->id);
            Log::info("⚡ Trading execution job dispatched for {$decision->symbol}");
        }
        
        return $decision;
    }

    /**
     * Build prompt untuk single symbol
     */
    private function buildSymbolPrompt($symbol, $marketData)
    {
        // GET REGIME DATA
        $regimeData = $this->getCurrentRegimeData([$symbol]);
        $symbolRegime = $regimeData[$symbol] ?? null;
        
        $prompt = "🎯 SYMBOL-SPECIFIC TRADING ANALYSIS - {$symbol}\n\n";
        
        // ADD MARKET OVERVIEW SECTION
        if ($symbol === 'BTC' && $symbolRegime) {
            $prompt .= "=== MARKET OVERVIEW ===\n";
            $prompt .= "🏆 BTC Dominance: " . $symbolRegime['dominance_score'] . "%\n";
            $prompt .= "📊 Overall Market Regime: " . strtoupper($symbolRegime['regime']) . " (Confidence: " . round($symbolRegime['regime_confidence'] * 100, 1) . "%)\n";
            $prompt .= "📈 Market Volatility: " . round($symbolRegime['volatility_24h'] * 100, 2) . "%\n";
            
            if ($symbolRegime['sentiment_score']) {
                $sentiment = $symbolRegime['sentiment_score'] > 0 ? 'BULLISH' : 'BEARISH';
                $prompt .= "😊 Market Sentiment: " . $sentiment . " (" . round($symbolRegime['sentiment_score'] * 100, 2) . "%)\n";
            }
            $prompt .= "\n";
        }

        // SYMBOL-SPECIFIC ANALYSIS WITH REGIME CONTEXT
        $prompt .= "=== {$symbol} ADVANCED ANALYSIS ===\n";
        $prompt .= "💰 Current Price: $" . number_format($marketData['current_price'], 2) . "\n";
        
        // REGIME ANALYSIS SECTION
        if ($symbolRegime) {
            $prompt .= "🎯 MARKET REGIME: " . strtoupper($symbolRegime['regime']) . "\n";
            $prompt .= "📊 Regime Confidence: " . round($symbolRegime['regime_confidence'] * 100, 1) . "%\n";
            $prompt .= "📈 24h Volatility: " . round($symbolRegime['volatility_24h'] * 100, 2) . "%\n";
            $prompt .= "📍 RSI-14: " . $symbolRegime['rsi_14'] . " (" . $this->getRSILevel($symbolRegime['rsi_14']) . ")\n";
            $prompt .= "🏆 Dominance Score: " . $symbolRegime['dominance_score'] . "%\n";
            
            if ($symbolRegime['predicted_trend']) {
                $trend = $symbolRegime['predicted_trend'] > 0 ? 'BULLISH' : 'BEARISH';
                $prompt .= "🔮 Predicted Trend: " . $trend . " (" . round($symbolRegime['predicted_trend'] * 100, 2) . "%)\n";
            }
            
            if ($symbolRegime['anomaly_score'] > 0.7) {
                $prompt .= "⚠️ ANOMALY DETECTED: " . round($symbolRegime['anomaly_score'] * 100, 1) . "% - Exercise Extreme Caution\n";
            } elseif ($symbolRegime['anomaly_score'] > 0.5) {
                $prompt .= "🔸 MODERATE ANOMALY: " . round($symbolRegime['anomaly_score'] * 100, 1) . "% - Be Cautious\n";
            }
            
            $prompt .= "\n";
        } else {
            $prompt .= "ℹ️ No regime data available - using technical analysis only\n\n";
        }
        
        // EXISTING TECHNICAL ANALYSIS
        $indicators = $marketData['indicators'];
        $prompt .= "TECHNICAL INDICATORS:\n";
        $prompt .= "📊 Current RSI: " . round($indicators['rsi'], 2) . " (" . $this->getRSILevel($indicators['rsi']) . ")\n";
        $prompt .= "📈 MACD Line: " . round($indicators['macd']['macd_line'], 4) . "\n";
        $prompt .= "📊 MACD Signal: " . round($indicators['macd']['signal_line'], 4) . "\n";
        $prompt .= "📉 EMA 20: $" . number_format(end($indicators['ema_20']), 2) . "\n";
        
        $priceVsEMA = $marketData['current_price'] > end($indicators['ema_20']) ? 'ABOVE' : 'BELOW';
        $prompt .= "📍 Price vs EMA20: " . $priceVsEMA . "\n";
        
        $volumeData = $marketData['volume_data'] ?? [
            'current_volume' => $indicators['current_volume'] ?? 0,
            'volume_ratio' => 1
        ];
        
        $prompt .= "📊 Volume: " . number_format($volumeData['current_volume']) . " (Ratio: " . round($volumeData['volume_ratio'], 2) . "x)\n\n";

        // CONFIDENCE REQUIREMENTS
        $prompt .= "CONFIDENCE REQUIREMENTS:\n";
        $prompt .= "• BUY/SELL: Minimum {$this->minConfidenceThreshold}% confidence required\n";
        $prompt .= "• HOLD: Only if justified with clear reasoning\n";
        $prompt .= "• LOW CONFIDENCE: Better to skip than force trade\n\n";
        
        $prompt .= "RESPONSE REQUIREMENTS:\n";
        $prompt .= "- Confidence: 0-100 based on signal strength and regime alignment\n";
        $prompt .= "- Action: BUY/SELL/HOLD only\n";
        $prompt .= "- Symbol: Must include 'USDT' suffix\n";
        $prompt .= "- Explanation: Brief technical AND regime rationale in English\n";
        $prompt .= "- Response MUST be valid JSON only, no other text\n\n";
        
        $prompt .= "REQUIRED JSON FORMAT:\n";
        $prompt .= "{\n";
        $prompt .= "  \"symbol\": \"{$symbol}USDT\",\n";
        $prompt .= "  \"action\": \"BUY|SELL|HOLD\",\n";
        $prompt .= "  \"confidence\": 0-100,\n";
        $prompt .= "  \"explanation\": \"Technical and regime analysis for {$symbol}\"\n";
        $prompt .= "}";

        return $prompt;
    }

    /**
     * Generate optimized trading decision with adaptive learning
     */
    public function generateOptimizedTradingDecision($symbols = ['BTC', 'ETH'])
    {
        // Get optimization insights
        $optimization = $this->adaptiveLearningService->getOptimizationRecommendations();
        
        Log::info("🎯 Using optimized parameters:", $optimization['recommendations']);

        // Apply adaptive filters to symbols
        $filteredSymbols = $this->applyAdaptiveSymbolFilters($symbols, $optimization);
        
        Log::info("🎯 Optimized symbol selection:", [
            'original_symbols' => $symbols,
            'optimized_symbols' => $filteredSymbols
        ]);

        // Generate decision dengan optimized parameters
        return $this->generateTradingDecision($filteredSymbols);
    }

    /**
     * Apply adaptive filters based on learning
     */
    private function applyAdaptiveSymbolFilters($symbols, $optimization)
    {
        $filteredSymbols = [];
        
        foreach ($symbols as $symbol) {
            $shouldInclude = true;
            
            // Apply symbol preferences from optimization
            if (isset($optimization['detailed_analysis']['preferred_symbols'][$symbol])) {
                $preference = $optimization['detailed_analysis']['preferred_symbols'][$symbol];
                
                if ($preference['preference_weight'] > 1.2) {
                    Log::info("✅ High preference symbol: {$symbol} (Weight: {$preference['preference_weight']})");
                } elseif ($preference['preference_weight'] < 0.8) {
                    Log::info("⚠️ Low preference symbol: {$symbol} (Weight: {$preference['preference_weight']})");
                    $shouldInclude = false;
                }
            }

            if ($shouldInclude) {
                $filteredSymbols[] = $symbol;
            }
        }

        // Jika semua symbols difilter out, gunakan original symbols
        if (empty($filteredSymbols)) {
            Log::warning("⚠️ All symbols filtered out, using original symbols");
            return $symbols;
        }

        return $filteredSymbols;
    }

    /**
     * Get current regime data for symbols
     */
    private function getCurrentRegimeData($symbols)
    {
        $regimeData = [];
        
        foreach ($symbols as $symbol) {
            $binanceSymbol = $symbol . 'USDT';
            
            $regime = MarketRegime::where('symbol', $binanceSymbol)
                ->orderBy('timestamp', 'desc')
                ->first();
                
            if ($regime) {
                $regimeData[$symbol] = [
                    'regime' => $regime->regime,
                    'regime_confidence' => $regime->regime_confidence,
                    'volatility_24h' => $regime->volatility_24h,
                    'rsi_14' => $regime->rsi_14,
                    'dominance_score' => $regime->dominance_score,
                    'sentiment_score' => $regime->sentiment_score,
                    'predicted_trend' => $regime->predicted_trend,
                    'anomaly_score' => $regime->anomaly_score,
                    'metadata' => $regime->regime_metadata
                ];
                
                Log::info("📊 Regime data loaded for {$symbol}: {$regime->regime} (" . round($regime->regime_confidence * 100, 1) . "%)");
            } else {
                Log::warning("⚠️ No regime data found for {$binanceSymbol}");
            }
        }
        
        return $regimeData;
    }

    /**
     * Get RSI level description
     */
    private function getRSILevel($rsi)
    {
        if ($rsi === null) return 'UNKNOWN';
        
        if ($rsi >= 70) return 'OVERBOUGHT';
        if ($rsi <= 30) return 'OVERSOLD';
        if ($rsi >= 60) return 'BULLISH';
        if ($rsi <= 40) return 'BEARISH';
        return 'NEUTRAL';
    }

    /**
     * Parse GPT response and validate
     */
    private function parseGPTResponse($response, $marketAnalysis)
    {
        try {
            $content = $response['choices'][0]['message']['content'];
            
            // Clean and extract JSON
            $content = trim($content);
            $jsonStart = strpos($content, '{');
            $jsonEnd = strrpos($content, '}');
            
            if ($jsonStart === false || $jsonEnd === false) {
                throw new \Exception('No JSON found in response');
            }
            
            $jsonString = substr($content, $jsonStart, $jsonEnd - $jsonStart + 1);
            $decision = json_decode($jsonString, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON format: ' . json_last_error_msg());
            }
            
            // Validate required fields
            $required = ['symbol', 'action', 'confidence', 'explanation'];
            foreach ($required as $field) {
                if (!isset($decision[$field])) {
                    throw new \Exception("Missing field: {$field}");
                }
            }
            
            // Convert action to uppercase
            $decision['action'] = strtoupper(trim($decision['action']));
            
            // Validate action
            if (!in_array($decision['action'], ['BUY', 'SELL', 'HOLD'])) {
                throw new \Exception('Invalid action: ' . $decision['action']);
            }
            
            // Validate confidence range
            $decision['confidence'] = min(100, max(0, intval($decision['confidence'])));
            
            // Ensure symbol has USDT suffix
            if (strpos($decision['symbol'], 'USDT') === false) {
                $decision['symbol'] .= 'USDT';
            }
            
            // Get current price for the symbol
            $symbolKey = str_replace('USDT', '', $decision['symbol']);
            $decision['price'] = $marketAnalysis[$symbolKey]['current_price'] ?? 0;
            $decision['market_data'] = $marketAnalysis;
            $decision['decision_time'] = now();
            $decision['executed'] = false;
            
            return $decision;
            
        } catch (\Exception $e) {
            Log::error('❌ GPT Response Parsing Error: ' . $e->getMessage());
            Log::error('📝 Raw Response: ' . $response['choices'][0]['message']['content']);
            return null;
        }
    }

    /**
     * Test GPT connection with simple prompt
     */
    public function testConnection()
    {
        // Check API key first
        if (empty($this->openaiApiKey) || $this->openaiApiKey === 'sk-your-actual-api-key-here') {
            Log::error('❌ OpenAI API Key not configured in .env');
            return false;
        }

        try {
            Log::info('🧪 Testing OpenAI API connection...');
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->openaiApiKey,
                'Content-Type' => 'application/json',
            ])->timeout(15)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4.1-mini',
                'messages' => [
                    [
                        'role' => 'user', 
                        'content' => 'Respond with exactly: OK'
                    ]
                ],
                'max_tokens' => 5,
                'temperature' => 0.1
            ]);

            if ($response->successful()) {
                $content = $response->json()['choices'][0]['message']['content'] ?? '';
                $isOk = trim($content) === 'OK';
                
                if ($isOk) {
                    Log::info('✅ OpenAI API connection successful');
                } else {
                    Log::warning('⚠️ OpenAI API connected but unexpected response: ' . $content);
                }
                
                return $response->successful();
            } else {
                Log::error('❌ OpenAI API test failed - Status: ' . $response->status());
                Log::error('❌ Response: ' . $response->body());
                return false;
            }
            
        } catch (\Exception $e) {
            Log::error('❌ OpenAI Connection Test Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get API key info (for debugging)
     */
    public function getApiKeyInfo()
    {
        if (empty($this->openaiApiKey)) {
            return '❌ API Key is empty';
        }
        
        $keyStart = substr($this->openaiApiKey, 0, 7);
        $keyLength = strlen($this->openaiApiKey);
        
        return "✅ API Key: {$keyStart}... (length: {$keyLength})";
    }
}