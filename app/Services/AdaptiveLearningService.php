<?php

namespace App\Services;

use App\Models\AiDecision;
use App\Models\UserPosition;
use App\Models\MarketRegime;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AdaptiveLearningService
{
    private $performanceService;
    private $isAutoLearningEnabled = false;

    // Learning configuration
    private $minTradesForLearning = 5;
    private $emergencyWinRateThreshold = 40;
    private $highVolatilityThreshold = 0.05;

    public function __construct(PerformanceAnalyticsService $performanceService)
    {
        $this->performanceService = $performanceService;
    }

    /**
     * Enable fully autonomous learning system
     */
    public function enableAutoLearning()
    {
        if ($this->isAutoLearningEnabled) {
            return;
        }

        $this->setupEventListeners();
        $this->initializeLearningCache();
        
        $this->isAutoLearningEnabled = true;
        Log::info("🤖 Autonomous learning system ENABLED");
    }

    /**
     * Setup event listeners for real-time learning
     */
    private function setupEventListeners()
    {
        // Listen to position closed events
        UserPosition::updated(function ($position) {
            if ($position->isDirty('status') && $position->status === 'CLOSED') {
                $this->onPositionClosed($position);
            }
        });

        // Listen to new AI decisions
        AiDecision::created(function ($decision) {
            if ($decision->action !== 'HOLD') {
                $this->onNewDecision($decision);
            }
        });

        Log::info("🎯 Event listeners registered for auto-learning");
    }

    /**
     * Initialize learning cache with default values
     */
    private function initializeLearningCache()
    {
        $defaultOptimizations = [
            'min_confidence' => 70,
            'preferred_symbols' => [],
            'regime_weights' => [],
            'risk_parameters' => [
                'stop_loss' => 2.0,
                'take_profit' => 4.0,
                'risk_reward_ratio' => '1:2'
            ],
            'last_optimization' => now()->toDateTimeString()
        ];

        Cache::put('trading_optimizations', $defaultOptimizations, now()->addDays(1));
        Log::info("💾 Learning cache initialized");
    }

    /**
     * REAL-TIME LEARNING: Triggered when position is closed
     */
    private function onPositionClosed(UserPosition $position)
    {
        $symbol = $position->symbol;
        $pnlPercentage = $position->pnl_percentage;
        
        Log::info("📚 Learning from closed position: {$symbol} PnL: {$pnlPercentage}%");

        // Quick learning for this specific symbol
        $this->quickSymbolOptimization($symbol);
        
        // Update risk parameters based on recent volatility
        $this->updateRiskParametersFromTrade($position);
        
        // Batch learning trigger every 10 closed positions
        $this->checkBatchOptimization();
        
        // Emergency check for performance drops
        $this->checkEmergencyOptimization();
    }

    /**
     * Triggered when new AI decision is created
     */
    private function onNewDecision(AiDecision $decision)
    {
        // Track decision patterns for frequency analysis
        $this->trackDecisionPatterns($decision);
    }

    /**
     * Track decision patterns for frequency analysis
     */
    private function trackDecisionPatterns(AiDecision $decision)
    {
        $symbol = $decision->symbol;
        $hour = now()->hour;
        
        $patternKey = "decision_pattern_{$symbol}_{$hour}";
        $currentCount = Cache::get($patternKey, 0);
        Cache::put($patternKey, $currentCount + 1, now()->addHours(2));
        
        Log::debug("📊 Decision pattern tracked: {$symbol} at hour {$hour}");
    }

    /**
     * QUICK SYMBOL OPTIMIZATION
     */
    private function quickSymbolOptimization($symbol)
    {
        $symbolPerformance = $this->getSymbolPerformance($symbol, 7); // 7 days lookback
        
        if ($symbolPerformance && $symbolPerformance['trade_count'] >= 3) {
            $newWeight = $this->calculateSymbolWeight($symbolPerformance);
            
            // Update in real-time cache
            $optimizations = Cache::get('trading_optimizations', []);
            $optimizations['preferred_symbols'][$symbol] = $newWeight;
            Cache::put('trading_optimizations', $optimizations, now()->addDays(1));
            
            Log::info("🎯 Quick symbol optimization: {$symbol} weight = {$newWeight}");
        }
    }

    /**
     * Get symbol performance data - FIXED untuk AiDecision relationship
     */
    private function getSymbolPerformance($symbol, $days = 7)
    {
        $startDate = now()->subDays($days);

        $result = UserPosition::where('symbol', $symbol)
            ->where('status', 'CLOSED')
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('COUNT(*) as trade_count'),
                DB::raw('AVG(pnl_percentage) as avg_pnl'),
                DB::raw('SUM(CASE WHEN pnl_percentage > 0 THEN 1 ELSE 0 END) as winning_trades'),
                DB::raw('AVG(ABS(pnl_percentage)) as avg_volatility')
            )
            ->first();

        return $result ? (array)$result->getAttributes() : null;
    }

    /**
     * Calculate symbol weight based on performance
     */
    private function calculateSymbolWeight($performance)
    {
        $winRate = ($performance['winning_trades'] / $performance['trade_count']) * 100;
        $score = ($winRate * 0.6) + (max(0, $performance['avg_pnl']) * 8);
        
        return max(0.3, min(3.0, $score / 50)); // Normalize to 0.3-3.0 range
    }

    /**
     * UPDATE RISK PARAMETERS from recent trade
     */
    private function updateRiskParametersFromTrade(UserPosition $position)
    {
        $recentVolatility = $this->calculateRecentVolatility($position->symbol);
        
        if ($recentVolatility > $this->highVolatilityThreshold) {
            $newStopLoss = min(5.0, $recentVolatility * 0.7);
            $newTakeProfit = $newStopLoss * 2;
            
            $this->updateRiskParameters($newStopLoss, $newTakeProfit);
            Log::info("🌊 Volatility-adjusted risk: SL={$newStopLoss}%, TP={$newTakeProfit}%");
        }
    }

    /**
     * Calculate recent volatility for symbol
     */
    private function calculateRecentVolatility($symbol)
    {
        $startDate = now()->subDays(3);

        return UserPosition::where('symbol', $symbol)
            ->where('status', 'CLOSED')
            ->where('created_at', '>=', $startDate)
            ->avg(DB::raw('ABS(pnl_percentage)')) ?? 2.0;
    }

    /**
     * BATCH OPTIMIZATION CHECK
     */
    private function checkBatchOptimization()
    {
        $recentClosedCount = UserPosition::where('status', 'CLOSED')
            ->where('created_at', '>=', now()->subDay())
            ->count();
            
        if ($recentClosedCount > 0 && $recentClosedCount % 10 === 0) {
            Log::info("📊 Batch optimization triggered by {$recentClosedCount} closed positions");
            $this->dailyOptimization();
        }
    }

    /**
     * EMERGENCY OPTIMIZATION CHECK
     */
    private function checkEmergencyOptimization()
    {
        $recentPerformance = $this->getRecentPerformance(24); // 24 hours
        
        if ($recentPerformance['win_rate'] < $this->emergencyWinRateThreshold && $recentPerformance['total_trades'] >= 5) {
            Log::warning("📉 Emergency optimization triggered - Win rate: {$recentPerformance['win_rate']}%");
            $this->emergencyOptimization();
        }
    }

    /**
     * SCHEDULED: Daily comprehensive optimization
     */
    public function dailyOptimization()
    {
        Log::info("🔄 Starting daily auto-optimization...");
        
        try {
            $optimization = $this->optimizeTradingParameters(30); // 30 days lookback
            
            // Auto-apply optimizations
            $this->autoApplyOptimizations($optimization);
            
            Log::info("✅ Daily optimization completed");
            
        } catch (\Exception $e) {
            Log::error("❌ Daily optimization failed: " . $e->getMessage());
        }
    }

    /**
     * SCHEDULED: Weekly deep learning
     */
    public function weeklyDeepLearning()
    {
        Log::info("🧠 Starting weekly deep learning...");
        
        try {
            $optimization = $this->optimizeTradingParameters(90); // 90 days lookback
            
            // More comprehensive optimization
            $this->deepLearningOptimization($optimization);
            
            Log::info("✅ Weekly deep learning completed");
            
        } catch (\Exception $e) {
            Log::error("❌ Weekly deep learning failed: " . $e->getMessage());
        }
    }

    /**
     * PERFORMANCE-TRIGGERED: Emergency optimization
     */
    private function emergencyOptimization()
    {
        Log::warning("🚨 EMERGENCY: Performance drop detected - optimizing aggressively");
        
        $optimization = $this->optimizeTradingParameters(7); // Short lookback for quick response
        
        // Apply aggressive changes
        $this->applyAggressiveOptimizations($optimization);
        
        // Reset performance tracking
        Cache::put('last_emergency_optimization', now(), now()->addHours(6));
    }

    /**
     * INCREMENTAL: Update risk parameters
     */
    private function updateRiskParameters($stopLoss, $takeProfit)
    {
        $optimizations = Cache::get('trading_optimizations', []);
        $optimizations['risk_parameters'] = [
            'stop_loss' => round($stopLoss, 2),
            'take_profit' => round($takeProfit, 2),
            'risk_reward_ratio' => '1:' . round($takeProfit / $stopLoss, 1),
            'updated_at' => now()->toDateTimeString()
        ];
        Cache::put('trading_optimizations', $optimizations, now()->addDays(1));
    }

    /**
     * Get recent performance metrics
     */
    private function getRecentPerformance($hours = 24)
    {
        $startDate = now()->subHours($hours);

        $performance = UserPosition::where('status', 'CLOSED')
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('COUNT(*) as total_trades'),
                DB::raw('SUM(CASE WHEN pnl_percentage > 0 THEN 1 ELSE 0 END) as winning_trades'),
                DB::raw('AVG(pnl_percentage) as avg_pnl'),
                DB::raw('AVG(ABS(pnl_percentage)) as volatility')
            )
            ->first();

        $winRate = $performance && $performance->total_trades > 0 
            ? ($performance->winning_trades / $performance->total_trades) * 100 
            : 0;

        return [
            'win_rate' => round($winRate, 2),
            'avg_pnl' => round($performance->avg_pnl ?? 0, 2),
            'volatility' => round($performance->volatility ?? 0, 2),
            'total_trades' => $performance->total_trades ?? 0
        ];
    }

    /**
     * UPDATED: Analyze historical patterns dengan relationship yang benar
     */
    public function optimizeTradingParameters($lookbackDays = 90)
    {
        $analysis = [];

        // 1. Analyze best performing regimes - FIXED relationship
        $regimeAnalysis = $this->analyzeRegimeEffectiveness($lookbackDays);
        $analysis['optimal_regimes'] = $this->calculateOptimalRegimeWeights($regimeAnalysis);

        // 2. Analyze confidence threshold optimization - FIXED relationship
        $confidenceAnalysis = $this->analyzeConfidenceThresholds($lookbackDays);
        $analysis['optimal_confidence'] = $this->calculateOptimalConfidence($confidenceAnalysis);

        // 3. Analyze time-of-day performance
        $timeAnalysis = $this->analyzeTimeBasedPerformance($lookbackDays);
        $analysis['optimal_trading_hours'] = $this->calculateOptimalTradingHours($timeAnalysis);

        // 4. Analyze symbol-specific performance
        $symbolAnalysis = $this->getTopPerformingSymbols(10, $lookbackDays);
        $analysis['preferred_symbols'] = $this->calculateSymbolPreferences($symbolAnalysis);

        // 5. Analyze risk parameter effectiveness
        $riskAnalysis = $this->analyzeRiskParameters($lookbackDays);
        $analysis['optimized_risk'] = $this->calculateOptimalRiskParameters($riskAnalysis);

        Log::info("🎯 Adaptive Learning Optimization Completed", [
            'lookback_days' => $lookbackDays,
            'regimes_analyzed' => count($analysis['optimal_regimes']),
            'symbols_analyzed' => count($analysis['preferred_symbols'])
        ]);

        return $analysis;
    }

    /**
     * UPDATED: Analyze regime effectiveness dengan relationship yang benar
     */
    private function analyzeRegimeEffectiveness($lookbackDays)
    {
        $startDate = now()->subDays($lookbackDays);

        return DB::table('user_positions')
            ->join('ai_decisions', 'user_positions.ai_decision_id', '=', 'ai_decisions.id')
            ->join('market_regimes', function($join) {
                $join->on('ai_decisions.symbol', '=', 'market_regimes.symbol')
                     ->whereColumn('ai_decisions.created_at', '>=', 'market_regimes.timestamp');
            })
            ->where('user_positions.created_at', '>=', $startDate)
            ->where('user_positions.status', 'CLOSED')
            ->whereNotNull('user_positions.pnl_percentage')
            ->select(
                'market_regimes.regime',
                DB::raw('COUNT(*) as trade_count'),
                DB::raw('AVG(user_positions.pnl_percentage) as avg_pnl'),
                DB::raw('SUM(CASE WHEN user_positions.pnl_percentage > 0 THEN 1 ELSE 0 END) as winning_trades'),
                DB::raw('AVG(ai_decisions.confidence) as avg_decision_confidence')
            )
            ->groupBy('market_regimes.regime')
            ->get()
            ->keyBy('regime');
    }

    /**
     * UPDATED: Analyze confidence thresholds dengan relationship yang benar
     */
    private function analyzeConfidenceThresholds($lookbackDays)
    {
        $startDate = now()->subDays($lookbackDays);

        return DB::table('ai_decisions')
            ->join('user_positions', 'ai_decisions.id', '=', 'user_positions.ai_decision_id')
            ->where('ai_decisions.created_at', '>=', $startDate)
            ->where('ai_decisions.action', '!=', 'HOLD')
            ->where('user_positions.status', 'CLOSED')
            ->select(
                DB::raw('FLOOR(ai_decisions.confidence/10)*10 as confidence_bucket'),
                DB::raw('COUNT(*) as total_decisions'),
                DB::raw('SUM(CASE WHEN user_positions.pnl_percentage > 0 THEN 1 ELSE 0 END) as profitable_trades'),
                DB::raw('AVG(user_positions.pnl_percentage) as avg_pnl')
            )
            ->groupBy('confidence_bucket')
            ->orderBy('confidence_bucket')
            ->get();
    }

    /**
     * UPDATED: Analyze time-based performance
     */
    private function analyzeTimeBasedPerformance($lookbackDays)
    {
        $startDate = now()->subDays($lookbackDays);

        return DB::table('user_positions')
            ->where('created_at', '>=', $startDate)
            ->where('status', 'CLOSED')
            ->whereNotNull('pnl_percentage')
            ->select(
                DB::raw('HOUR(created_at) as hour_of_day'),
                DB::raw('COUNT(*) as trade_count'),
                DB::raw('AVG(pnl_percentage) as avg_pnl'),
                DB::raw('SUM(CASE WHEN pnl_percentage > 0 THEN 1 ELSE 0 END) as winning_trades')
            )
            ->groupBy('hour_of_day')
            ->orderBy('hour_of_day')
            ->get();
    }

    /**
     * UPDATED: Get top performing symbols
     */
    private function getTopPerformingSymbols($limit = 10, $lookbackDays = 90)
    {
        $startDate = now()->subDays($lookbackDays);

        return UserPosition::where('created_at', '>=', $startDate)
            ->where('status', 'CLOSED')
            ->whereNotNull('pnl_percentage')
            ->select(
                'symbol',
                DB::raw('COUNT(*) as trade_count'),
                DB::raw('AVG(pnl_percentage) as avg_pnl'),
                DB::raw('SUM(CASE WHEN pnl_percentage > 0 THEN 1 ELSE 0 END) as winning_trades')
            )
            ->groupBy('symbol')
            ->having('trade_count', '>=', 3)
            ->orderByDesc(DB::raw('AVG(pnl_percentage)'))
            ->limit($limit)
            ->get()
            ->map(function($item) {
                $winRate = ($item->winning_trades / $item->trade_count) * 100;
                return [
                    'symbol' => $item->symbol,
                    'trade_count' => $item->trade_count,
                    'avg_pnl' => round($item->avg_pnl, 2),
                    'win_rate' => round($winRate, 2)
                ];
            })
            ->toArray();
    }

    /**
     * UPDATED: Analyze risk parameters
     */
    private function analyzeRiskParameters($lookbackDays)
    {
        $startDate = now()->subDays($lookbackDays);

        return DB::table('user_positions')
            ->where('created_at', '>=', $startDate)
            ->where('status', 'CLOSED')
            ->select(
                DB::raw('AVG(ABS(pnl_percentage)) as avg_volatility'),
                DB::raw('AVG(pnl_percentage) as avg_pnl_percentage'),
                DB::raw('COUNT(*) as total_trades'),
                DB::raw('SUM(CASE WHEN pnl_percentage > 0 THEN 1 ELSE 0 END) as winning_trades')
            )
            ->first();
    }

    /**
     * Auto-apply optimizations to cache
     */
    private function autoApplyOptimizations($optimization)
    {
        $currentOptimizations = Cache::get('trading_optimizations', []);

        // Update confidence threshold
        $currentOptimizations['min_confidence'] = $optimization['optimal_confidence']['min_confidence'] ?? 70;
        
        // Update symbol preferences
        $currentOptimizations['preferred_symbols'] = $optimization['preferred_symbols'] ?? [];
        
        // Update regime weights
        $currentOptimizations['regime_weights'] = $optimization['optimal_regimes'] ?? [];
        
        // Update risk parameters if better ones found
        if (isset($optimization['optimized_risk'])) {
            $currentOptimizations['risk_parameters'] = $optimization['optimized_risk'];
        }
        
        $currentOptimizations['last_optimization'] = now()->toDateTimeString();
        
        Cache::put('trading_optimizations', $currentOptimizations, now()->addDays(1));
        
        Log::info("⚙️ Auto-applied daily optimizations", [
            'min_confidence' => $currentOptimizations['min_confidence'],
            'preferred_symbols' => array_keys($currentOptimizations['preferred_symbols'])
        ]);
    }

    /**
     * Apply aggressive optimizations during emergencies
     */
    private function applyAggressiveOptimizations($optimization)
    {
        $currentOptimizations = Cache::get('trading_optimizations', []);

        // More aggressive confidence threshold
        $currentOptimizations['min_confidence'] = min(85, ($optimization['optimal_confidence']['min_confidence'] ?? 70) + 10);
        
        // Focus only on top performers
        $topSymbols = array_slice($optimization['preferred_symbols'] ?? [], 0, 3, true);
        $currentOptimizations['preferred_symbols'] = $topSymbols;
        
        // Conservative risk parameters
        $currentOptimizations['risk_parameters'] = [
            'stop_loss' => 1.5,
            'take_profit' => 3.0,
            'risk_reward_ratio' => '1:2',
            'emergency_mode' => true
        ];
        
        $currentOptimizations['last_emergency_optimization'] = now()->toDateTimeString();
        
        Cache::put('trading_optimizations', $currentOptimizations, now()->addHours(6));
        
        Log::warning("🚨 Applied aggressive emergency optimizations");
    }

    /**
     * Deep learning with comprehensive analysis
     */
    private function deepLearningOptimization($optimization)
    {
        // Store comprehensive learning results
        Cache::put('deep_learning_results', $optimization, now()->addDays(7));
        
        // Update long-term strategies
        $this->updateLongTermStrategies($optimization);
        
        Log::info("🧠 Deep learning completed - long-term strategies updated");
    }

    /**
     * Update long-term trading strategies
     */
    private function updateLongTermStrategies($optimization)
    {
        // Implement long-term strategy updates here
    }

    // ========== ANALYSIS CALCULATION METHODS ==========

    /**
     * Calculate optimal regime weights for decision making
     */
    private function calculateOptimalRegimeWeights($regimeAnalysis)
    {
        $weights = [];
        $totalPerformance = 0;

        foreach ($regimeAnalysis as $regime => $data) {
            if ($data->trade_count >= 5) {
                $winRate = ($data->winning_trades / $data->trade_count) * 100;
                $performanceScore = ($winRate * 0.6) + (($data->avg_pnl > 0 ? $data->avg_pnl * 10 : 0) * 0.4);
                
                $weights[$regime] = [
                    'performance_score' => round($performanceScore, 2),
                    'win_rate' => round($winRate, 2),
                    'avg_pnl' => round($data->avg_pnl, 2),
                    'trade_count' => $data->trade_count,
                    'weight' => max(0.1, min(2.0, $performanceScore / 50))
                ];
                $totalPerformance += $performanceScore;
            }
        }

        if ($totalPerformance > 0) {
            foreach ($weights as $regime => &$data) {
                $data['normalized_weight'] = round($data['performance_score'] / $totalPerformance, 3);
            }
        }

        return $weights;
    }

    /**
     * Calculate optimal confidence threshold
     */
    private function calculateOptimalConfidence($confidenceAnalysis)
    {
        $optimalThreshold = 60;
        $bestScore = 0;

        foreach ($confidenceAnalysis as $bucket) {
            if ($bucket->total_decisions >= 3) {
                $accuracy = $bucket->profitable_trades / $bucket->total_decisions;
                $score = ($accuracy * 100) + ($bucket->avg_pnl * 5);
                
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $optimalThreshold = $bucket->confidence_bucket;
                }
            }
        }

        return [
            'optimal_threshold' => $optimalThreshold,
            'min_confidence' => max(50, $optimalThreshold - 10),
            'analysis' => $confidenceAnalysis->toArray()
        ];
    }

    /**
     * Calculate optimal trading hours
     */
    private function calculateOptimalTradingHours($timeAnalysis)
    {
        $optimalHours = [];
        $hourlyPerformance = [];

        foreach ($timeAnalysis as $hour) {
            if ($hour->trade_count >= 3) {
                $winRate = ($hour->winning_trades / $hour->trade_count) * 100;
                $performanceScore = $winRate + ($hour->avg_pnl * 10);
                
                $hourlyPerformance[$hour->hour_of_day] = [
                    'performance_score' => round($performanceScore, 2),
                    'win_rate' => round($winRate, 2),
                    'avg_pnl' => round($hour->avg_pnl, 2),
                    'trade_count' => $hour->trade_count
                ];
            }
        }

        arsort($hourlyPerformance);
        $topHours = array_slice($hourlyPerformance, 0, 6, true);

        foreach ($topHours as $hour => $data) {
            if ($data['performance_score'] > 50) {
                $optimalHours[] = [
                    'hour' => $hour,
                    'performance' => $data
                ];
            }
        }

        return $optimalHours;
    }

    /**
     * Calculate symbol preferences based on performance
     */
    private function calculateSymbolPreferences($symbolAnalysis)
    {
        $preferences = [];
        $totalScore = 0;

        foreach ($symbolAnalysis as $symbol) {
            $score = ($symbol['win_rate'] * 0.7) + (($symbol['avg_pnl'] > 0 ? $symbol['avg_pnl'] * 20 : 0) * 0.3);
            
            $preferences[$symbol['symbol']] = [
                'performance_score' => round($score, 2),
                'win_rate' => $symbol['win_rate'],
                'avg_pnl' => $symbol['avg_pnl'],
                'trade_count' => $symbol['trade_count'],
                'preference_weight' => max(0.5, min(3.0, $score / 50))
            ];
            $totalScore += $score;
        }

        return $preferences;
    }

    /**
     * Calculate optimal risk parameters
     */
    private function calculateOptimalRiskParameters($riskAnalysis)
    {
        $avgVolatility = $riskAnalysis->avg_volatility ?? 2.0;
        $winRate = $riskAnalysis->total_trades > 0 
            ? ($riskAnalysis->winning_trades / $riskAnalysis->total_trades) * 100 
            : 50;

        $baseStopLoss = max(1.0, min(5.0, $avgVolatility * 0.8));
        $baseTakeProfit = $baseStopLoss * 2;

        if ($winRate > 60) {
            $baseStopLoss *= 1.1;
            $baseTakeProfit *= 1.1;
        } elseif ($winRate < 40) {
            $baseStopLoss *= 0.9;
            $baseTakeProfit *= 0.9;
        }

        return [
            'recommended_stop_loss' => round($baseStopLoss, 2),
            'recommended_take_profit' => round($baseTakeProfit, 2),
            'risk_reward_ratio' => '1:' . round($baseTakeProfit / $baseStopLoss, 1),
            'based_on_win_rate' => round($winRate, 2),
            'avg_volatility' => round($avgVolatility, 2)
        ];
    }

    /**
     * Get current optimizations for AITradingService
     */
    public function getOptimizationRecommendations()
    {
        if (!$this->isAutoLearningEnabled) {
            $this->enableAutoLearning();
        }

        $optimizations = Cache::get('trading_optimizations', []);
        
        $recommendations = [];

        if (!empty($optimizations['preferred_symbols'])) {
            $topSymbols = array_slice($optimizations['preferred_symbols'], 0, 3);
            $recommendations[] = "🏆 Focus on: " . implode(', ', array_keys($topSymbols));
        }

        if (isset($optimizations['min_confidence'])) {
            $recommendations[] = "📈 Min confidence: {$optimizations['min_confidence']}%";
        }

        if (isset($optimizations['risk_parameters'])) {
            $risk = $optimizations['risk_parameters'];
            $recommendations[] = "🛡️ Risk: SL {$risk['stop_loss']}%, TP {$risk['take_profit']}%";
        }

        return [
            'recommendations' => $recommendations,
            'detailed_analysis' => $optimizations
        ];
    }

    /**
     * Get real-time optimizations for immediate use
     */
    public function getRealTimeOptimizations()
    {
        return Cache::get('trading_optimizations', []);
    }
}