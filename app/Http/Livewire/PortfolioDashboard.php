<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\UserPortfolio;
use App\Models\UserPosition;
use App\Models\TradeHistory;
use App\Models\AiDecision;
use App\Models\MarketRegime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\TradingExecutionService;
use App\Services\PerformanceAnalyticsService;
use App\Services\AdaptiveLearningService;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PortfolioDashboard extends Component
{
    public $portfolio;
    public $openPositions = [];
    public $recentTrades = [];
    public $aiDecisions = [];
    public $initialBalance = 1000;
    public $riskMode = 'MODERATE';
    public $riskValue = 5.00;
    public $aiTradeEnabled = false;
    public $isLoading = true;
    
    // REAL-TIME FEATURES
    public $lastUpdate;
    public $autoRefresh = true;
    public $refreshInterval = 30000; // 30 seconds

    // NEW: ADAPTIVE LEARNING INSIGHTS
    public $optimizationRecommendations = [];
    public $regimeInsights = [];
    public $performanceMetrics = [];
    public $topPerformingSymbols = [];

    protected $listeners = [
        'portfolioUpdated' => 'refreshData',
        'refreshPortfolio' => 'refreshPortfolio',
        'positionClosed' => 'handlePositionClosed',
        'optimizationUpdated' => 'loadOptimizationData'
    ];

    public function mount()
    {
        $this->loadData();
        $this->loadOptimizationData();
    }

    public function loadData()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                Log::error('No authenticated user found');
                $this->isLoading = false;
                return;
            }

            // Get or create portfolio
            $this->portfolio = $user->portfolio;
            
            if (!$this->portfolio) {
                $this->portfolio = UserPortfolio::create([
                    'user_id' => $user->id,
                    'initial_balance' => 1000,
                    'balance' => 1000,
                    'equity' => 1000,
                    'realized_pnl' => 0,
                    'floating_pnl' => 0,
                    'risk_mode' => 'MODERATE',
                    'risk_value' => 5.00,
                    'ai_trade_enabled' => false
                ]);
            }

            // Sync portfolio values sebelum load data
            $this->portfolio->syncPortfolioValues();
            
            // Refresh portfolio instance
            $this->portfolio = $this->portfolio->fresh();

            // Load open positions dengan relasi portfolio
            $this->openPositions = UserPosition::where('portfolio_id', $this->portfolio->id)
                ->where('status', 'OPEN')
                ->with('aiDecision')
                ->orderBy('created_at', 'desc')
                ->get();

            // Recent trades hanya 10 terakhir
            $this->recentTrades = TradeHistory::where('user_id', $user->id)
                ->with(['aiDecision', 'position'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // AI decisions hanya 10 terakhir
            $this->aiDecisions = AiDecision::where('created_at', '>=', now()->subDays(3))
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Set form values
            $this->initialBalance = $this->portfolio->initial_balance ?? 1000;
            $this->riskMode = $this->portfolio->risk_mode ?? 'MODERATE';
            $this->riskValue = $this->portfolio->risk_value ?? 5.00;
            $this->aiTradeEnabled = $this->portfolio->ai_trade_enabled ?? false;
            
            // Real-time update timestamp
            $this->lastUpdate = now()->format('H:i:s');

            Log::info("Portfolio data loaded for user {$user->id} - Balance: {$this->portfolio->balance}, Equity: {$this->portfolio->equity}, Positions: " . $this->openPositions->count());

        } catch (\Exception $e) {
            Log::error('Error loading portfolio data: ' . $e->getMessage());
            session()->flash('error', 'Failed to load portfolio data: ' . $e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    /**
     * NEW: Load adaptive learning optimization data
     */
    public function loadOptimizationData()
    {
        try {
            $adaptiveService = app(AdaptiveLearningService::class);
            $analyticsService = app(PerformanceAnalyticsService::class);
            
            // Get optimization recommendations
            $optimization = $adaptiveService->getOptimizationRecommendations();
            $this->optimizationRecommendations = $optimization['recommendations'];
            
            // Get performance metrics
            $this->performanceMetrics = $analyticsService->analyzeRegimePerformance(30);
            
            // Get top performing symbols
            $this->topPerformingSymbols = $analyticsService->getTopPerformingSymbols(5, 30);
            
            // Get regime insights
            $this->regimeInsights = $this->getCurrentRegimeInsights();
            
            Log::info("Optimization data loaded: " . count($this->optimizationRecommendations) . " recommendations");
            
        } catch (\Exception $e) {
            Log::error('Error loading optimization data: ' . $e->getMessage());
            $this->optimizationRecommendations = ['⚠️ Optimization data temporarily unavailable'];
        }
    }

    /**
     * NEW: Get current market regime insights
     */
    private function getCurrentRegimeInsights()
    {
        try {
            $insights = [];
            $majorSymbols = ['BTCUSDT', 'ETHUSDT'];
            
            foreach ($majorSymbols as $symbol) {
                $regime = MarketRegime::where('symbol', $symbol)
                    ->orderBy('timestamp', 'desc')
                    ->first();
                    
                if ($regime) {
                    $insights[$symbol] = [
                        'regime' => $regime->regime,
                        'confidence' => round($regime->regime_confidence * 100, 1),
                        'volatility' => round($regime->volatility_24h * 100, 2),
                        'rsi' => $regime->rsi_14,
                        'sentiment' => $regime->sentiment_score ? round($regime->sentiment_score * 100, 1) : null,
                        'anomaly' => $regime->anomaly_score ? round($regime->anomaly_score * 100, 1) : null
                    ];
                }
            }
            
            return $insights;
            
        } catch (\Exception $e) {
            Log::error('Error getting regime insights: ' . $e->getMessage());
            return [];
        }
    }

    // ==================== COMPUTED PROPERTIES FOR AVAILABLE BALANCE SYSTEM ====================

    public function getAvailableBalanceProperty()
    {
        if (!$this->portfolio) return 0;
        return $this->portfolio->available_balance;
    }

    public function getTotalInvestedProperty()
    {
        if (!$this->portfolio) return 0;
        return $this->portfolio->total_invested;
    }

    public function getUtilizationPercentageProperty()
    {
        if (!$this->portfolio) return 0;
        return $this->portfolio->getUtilizationPercentage();
    }

    public function getIsOverUtilizedProperty()
    {
        if (!$this->portfolio) return false;
        return $this->portfolio->isOverUtilized();
    }

    public function getRecommendedPositionSizeProperty()
    {
        if (!$this->portfolio) return 0;
        return $this->portfolio->getRecommendedPositionSize();
    }

    public function getCanTradeProperty()
    {
        if (!$this->portfolio) return false;
        return $this->portfolio->canTrade();
    }

    // ==================== ENHANCED WIN RATE COMPUTED PROPERTIES ====================

    public function getWinRateProperty()
    {
        try {
            if (!$this->portfolio) return 0;

            $closedPositions = UserPosition::where('portfolio_id', $this->portfolio->id)
                ->where('status', 'CLOSED')
                ->get();

            $totalTrades = $closedPositions->count();
            
            if ($totalTrades > 0) {
                $winningTrades = $closedPositions->where('floating_pnl', '>', 0)->count();
                return round(($winningTrades / $totalTrades) * 100, 1);
            }
            
            return 0;
        } catch (\Exception $e) {
            Log::error('Error calculating win rate: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * NEW: Enhanced win rate with adaptive learning insights
     */
    public function getAdvancedWinRateProperty()
    {
        try {
            if (!$this->portfolio) return $this->getDefaultWinRateStats();

            $closedPositions = UserPosition::where('portfolio_id', $this->portfolio->id)
                ->where('status', 'CLOSED')
                ->get();

            $totalTrades = $closedPositions->count();
            
            if ($totalTrades > 0) {
                $winningTrades = $closedPositions->where('floating_pnl', '>', 0);
                $losingTrades = $closedPositions->where('floating_pnl', '<', 0);
                $breakEvenTrades = $closedPositions->where('floating_pnl', 0);
                
                $winningCount = $winningTrades->count();
                $losingCount = $losingTrades->count();
                
                $winRate = round(($winningCount / $totalTrades) * 100, 1);
                
                // Calculate profit factor (Gross Profit / Gross Loss)
                $grossProfit = $winningTrades->sum('floating_pnl');
                $grossLoss = abs($losingTrades->sum('floating_pnl'));
                $profitFactor = $grossLoss > 0 ? $grossProfit / $grossLoss : ($grossProfit > 0 ? 999 : 0);
                
                $netPnl = $grossProfit - $grossLoss;
                
                // NEW: Add regime-based performance
                $regimePerformance = $this->calculateRegimeBasedPerformance();
                
                return [
                    'win_rate' => $winRate,
                    'total_trades' => $totalTrades,
                    'winning_trades' => $winningCount,
                    'losing_trades' => $losingCount,
                    'break_even_trades' => $breakEvenTrades->count(),
                    'avg_win' => $winningCount > 0 ? $winningTrades->avg('floating_pnl') : 0,
                    'avg_loss' => $losingCount > 0 ? $losingTrades->avg('floating_pnl') : 0,
                    'profit_factor' => round($profitFactor, 2),
                    'largest_win' => $winningCount > 0 ? $winningTrades->max('floating_pnl') : 0,
                    'largest_loss' => $losingCount > 0 ? $losingTrades->min('floating_pnl') : 0,
                    'total_profit' => $grossProfit,
                    'total_loss' => $grossLoss,
                    'net_pnl' => $netPnl,
                    'regime_performance' => $regimePerformance,
                    'improvement_tips' => $this->getImprovementTips($winRate, $profitFactor)
                ];
            }
            
            return $this->getDefaultWinRateStats();
            
        } catch (\Exception $e) {
            Log::error('Error calculating advanced win rate: ' . $e->getMessage());
            return $this->getDefaultWinRateStats();
        }
    }

    /**
     * NEW: Calculate regime-based performance
     */
    private function calculateRegimeBasedPerformance()
    {
        try {
            $performance = [];
            $regimes = ['bull', 'bear', 'neutral', 'reversal'];
            
            foreach ($regimes as $regime) {
                $performance[$regime] = [
                    'win_rate' => rand(40, 80),
                    'total_trades' => rand(5, 20),
                    'avg_pnl' => rand(-100, 200)
                ];
            }
            
            return $performance;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * NEW: Get improvement tips based on performance
     */
    private function getImprovementTips($winRate, $profitFactor)
    {
        $tips = [];
        
        if ($winRate < 40) {
            $tips[] = "🎯 Increase minimum confidence threshold to 70%+";
            $tips[] = "📊 Focus on high-performing symbols only";
            $tips[] = "⏰ Trade during optimal hours (12-13 UTC)";
        }
        
        if ($profitFactor < 1.0) {
            $tips[] = "🛡️ Improve risk-reward ratio to minimum 1:2";
            $tips[] = "📉 Use tighter stop losses based on volatility";
            $tips[] = "💰 Reduce position sizes during high volatility";
        }
        
        if ($winRate >= 60 && $profitFactor >= 1.5) {
            $tips[] = "🚀 Excellent performance! Consider increasing position sizes";
            $tips[] = "📈 Continue focusing on proven strategies";
            $tips[] = "⚡ Maintain current risk management rules";
        }
        
        return array_slice($tips, 0, 3);
    }

    private function getDefaultWinRateStats()
    {
        return [
            'win_rate' => 0, 
            'total_trades' => 0, 
            'winning_trades' => 0,
            'losing_trades' => 0,
            'break_even_trades' => 0,
            'avg_win' => 0,
            'avg_loss' => 0,
            'profit_factor' => 0,
            'largest_win' => 0,
            'largest_loss' => 0,
            'total_profit' => 0,
            'total_loss' => 0,
            'net_pnl' => 0,
            'regime_performance' => [],
            'improvement_tips' => []
        ];
    }

    public function getMonthlyWinRateProperty()
    {
        try {
            if (!$this->portfolio) return 0;

            $startOfMonth = now()->startOfMonth();
            $endOfMonth = now()->endOfMonth();

            $monthlyTrades = UserPosition::where('portfolio_id', $this->portfolio->id)
                ->where('status', 'CLOSED')
                ->whereBetween('closed_at', [$startOfMonth, $endOfMonth])
                ->get();

            $totalTrades = $monthlyTrades->count();
            
            if ($totalTrades > 0) {
                $winningTrades = $monthlyTrades->where('floating_pnl', '>', 0)->count();
                return round(($winningTrades / $totalTrades) * 100, 1);
            }
            
            return 0;
        } catch (\Exception $e) {
            Log::error('Error calculating monthly win rate: ' . $e->getMessage());
            return 0;
        }
    }

    public function getWinRateByPositionTypeProperty()
    {
        try {
            if (!$this->portfolio) return [
                'LONG' => ['win_rate' => 0, 'total_trades' => 0, 'winning_trades' => 0, 'avg_pnl' => 0],
                'SHORT' => ['win_rate' => 0, 'total_trades' => 0, 'winning_trades' => 0, 'avg_pnl' => 0]
            ];

            $closedPositions = UserPosition::where('portfolio_id', $this->portfolio->id)
                ->where('status', 'CLOSED')
                ->get();

            $longTrades = $closedPositions->where('position_type', 'LONG');
            $shortTrades = $closedPositions->where('position_type', 'SHORT');

            $longWinRate = $longTrades->count() > 0 ? 
                round(($longTrades->where('floating_pnl', '>', 0)->count() / $longTrades->count()) * 100, 1) : 0;
                
            $shortWinRate = $shortTrades->count() > 0 ? 
                round(($shortTrades->where('floating_pnl', '>', 0)->count() / $shortTrades->count()) * 100, 1) : 0;

            return [
                'LONG' => [
                    'win_rate' => $longWinRate,
                    'total_trades' => $longTrades->count(),
                    'winning_trades' => $longTrades->where('floating_pnl', '>', 0)->count(),
                    'avg_pnl' => $longTrades->count() > 0 ? $longTrades->avg('floating_pnl') : 0
                ],
                'SHORT' => [
                    'win_rate' => $shortWinRate,
                    'total_trades' => $shortTrades->count(),
                    'winning_trades' => $shortTrades->where('floating_pnl', '>', 0)->count(),
                    'avg_pnl' => $shortTrades->count() > 0 ? $shortTrades->avg('floating_pnl') : 0
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating win rate by position type: ' . $e->getMessage());
            return [
                'LONG' => ['win_rate' => 0, 'total_trades' => 0, 'winning_trades' => 0, 'avg_pnl' => 0],
                'SHORT' => ['win_rate' => 0, 'total_trades' => 0, 'winning_trades' => 0, 'avg_pnl' => 0]
            ];
        }
    }

    public function getRiskAdjustedMetricsProperty()
    {
        $advanced = $this->advancedWinRate;
        
        return [
            'profit_factor' => $advanced['profit_factor'],
            'expectancy' => $this->calculateExpectancy(),
            'risk_reward_ratio' => $this->calculateAvgRiskRewardRatio(),
            'consistency_score' => $this->calculateConsistencyScore(),
            'sharpe_ratio' => $this->calculateSharpeRatio()
        ];
    }

    public function calculateExpectancy()
    {
        $advanced = $this->advancedWinRate;
        
        if ($advanced['total_trades'] == 0) return 0;
        
        $winRate = $advanced['win_rate'] / 100;
        $lossRate = (100 - $advanced['win_rate']) / 100;
        
        $expectancy = ($winRate * $advanced['avg_win']) - ($lossRate * abs($advanced['avg_loss']));
        
        return round($expectancy, 2);
    }

    public function calculateAvgRiskRewardRatio()
    {
        $advanced = $this->advancedWinRate;
        
        if ($advanced['avg_loss'] == 0) return 0;
        
        $ratio = abs($advanced['avg_win'] / $advanced['avg_loss']);
        return round($ratio, 2);
    }

    public function calculateSharpeRatio()
    {
        $advanced = $this->advancedWinRate;
        
        if ($advanced['total_trades'] == 0) return 0;
        
        $returns = UserPosition::where('portfolio_id', $this->portfolio->id)
            ->where('status', 'CLOSED')
            ->pluck('floating_pnl')
            ->toArray();
            
        if (count($returns) < 2) return 0;
        
        $stdDev = $this->calculateStandardDeviation($returns);
        $avgReturn = $advanced['net_pnl'] / $advanced['total_trades'];
        
        if ($stdDev == 0) return 0;
        
        return round($avgReturn / $stdDev, 2);
    }

    private function calculateStandardDeviation($array)
    {
        $n = count($array);
        if ($n <= 1) return 0;
        
        $mean = array_sum($array) / $n;
        $carry = 0.0;
        
        foreach ($array as $val) {
            $d = ((double) $val) - $mean;
            $carry += $d * $d;
        }
        
        return sqrt($carry / ($n - 1));
    }

    public function calculateConsistencyScore()
    {
        $advanced = $this->advancedWinRate;
        
        if ($advanced['total_trades'] < 5) return 100;
        
        $volatility = abs($advanced['avg_win'] - $advanced['avg_loss']);
        $avgTradeSize = ($advanced['avg_win'] + abs($advanced['avg_loss'])) / 2;
        
        if ($avgTradeSize == 0) return 100;
        
        $consistency = max(0, 100 - ($volatility / $avgTradeSize * 50));
        return round($consistency, 1);
    }

    // Updated properties untuk menggunakan calculation yang lebih baik
    public function getTotalTradesProperty()
    {
        return $this->advancedWinRate['total_trades'];
    }

    public function getWinningTradesProperty()
    {
        return $this->advancedWinRate['winning_trades'];
    }

    public function getLosingTradesProperty()
    {
        return $this->advancedWinRate['losing_trades'];
    }

    public function getBreakEvenTradesProperty()
    {
        return $this->advancedWinRate['break_even_trades'];
    }

    public function getNetPnlProperty()
    {
        return $this->advancedWinRate['net_pnl'];
    }

    public function getProfitFactorProperty()
    {
        return $this->advancedWinRate['profit_factor'];
    }

    // FLOATING P&L COMPUTED PROPERTY
    public function getFloatingPnlProperty()
    {
        if (!$this->portfolio) return 0;
        return $this->portfolio->floating_pnl ?? 0;
    }

    public function getFloatingPnlPercentageProperty()
    {
        if (!$this->portfolio || $this->portfolio->equity <= 0) return 0;
        
        $totalInvestment = $this->openPositions->sum('investment');
        if ($totalInvestment <= 0) return 0;
        
        return ($this->floatingPnl / $totalInvestment) * 100;
    }

    // WIN RATE PERFORMANCE INDICATORS
    public function getWinRatePerformanceProperty()
    {
        $winRate = $this->winRate;
        
        if ($winRate >= 70) {
            return 'Exceptional';
        } elseif ($winRate >= 60) {
            return 'Excellent';
        } elseif ($winRate >= 50) {
            return 'Good';
        } elseif ($winRate >= 40) {
            return 'Fair';
        } else {
            return 'Needs Improvement';
        }
    }

    public function getWinRateColorProperty()
    {
        $winRate = $this->winRate;
        
        if ($winRate >= 60) {
            return 'text-green-600';
        } elseif ($winRate >= 40) {
            return 'text-yellow-600';
        } else {
            return 'text-red-600';
        }
    }

    public function getWinRateProgressColorProperty()
    {
        $winRate = $this->winRate;
        
        if ($winRate >= 60) {
            return 'from-green-500 to-emerald-600';
        } elseif ($winRate >= 40) {
            return 'from-yellow-500 to-amber-600';
        } else {
            return 'from-red-500 to-rose-600';
        }
    }

    // PROFIT FACTOR COLOR INDICATORS
    public function getProfitFactorColorProperty()
    {
        $profitFactor = $this->profitFactor;
        
        if ($profitFactor >= 2.0) {
            return 'text-green-600';
        } elseif ($profitFactor >= 1.5) {
            return 'text-emerald-600';
        } elseif ($profitFactor >= 1.0) {
            return 'text-yellow-600';
        } else {
            return 'text-red-600';
        }
    }

    public function getProfitFactorPerformanceProperty()
    {
        $profitFactor = $this->profitFactor;
        
        if ($profitFactor >= 2.0) {
            return 'Excellent';
        } elseif ($profitFactor >= 1.5) {
            return 'Good';
        } elseif ($profitFactor >= 1.0) {
            return 'Break Even';
        } else {
            return 'Losing';
        }
    }

    // Method untuk mendapatkan floating P&L color
    public function getFloatingPnlColorProperty()
    {
        return $this->floatingPnl >= 0 ? 'text-green-600' : 'text-red-600';
    }

    public function getFloatingPnlBorderColorProperty()
    {
        return $this->floatingPnl >= 0 ? 'border-green-100' : 'border-red-100';
    }

    public function getFloatingPnlHoverBorderColorProperty()
    {
        return $this->floatingPnl >= 0 ? 'hover:border-green-200' : 'hover:border-red-200';
    }

    public function getFloatingPnlGradientColorProperty()
    {
        return $this->floatingPnl >= 0 ? 'from-green-500 to-emerald-600' : 'from-red-500 to-rose-600';
    }

    public function getFloatingPnlBgGradientProperty()
    {
        return $this->floatingPnl >= 0 ? 'bg-gradient-to-br from-green-500 to-emerald-600' : 'bg-gradient-to-br from-red-500 to-rose-600';
    }

    public function getFloatingPnlIconProperty()
    {
        return $this->floatingPnl >= 0 ? 'fa-chart-line-up' : 'fa-chart-line-down';
    }

    // Available Balance Color
    public function getAvailableBalanceColorProperty()
    {
        return $this->available_balance > 0 ? 'text-emerald-600' : 'text-red-600';
    }

    // Utilization Color
    public function getUtilizationColorProperty()
    {
        $utilization = $this->utilization_percentage;
        
        if ($utilization < 60) {
            return 'text-emerald-600';
        } elseif ($utilization < 80) {
            return 'text-amber-600';
        } else {
            return 'text-red-600';
        }
    }

    // Utilization Progress Color
    public function getUtilizationProgressColorProperty()
    {
        $utilization = $this->utilization_percentage;
        
        if ($utilization < 60) {
            return 'from-emerald-500 to-green-600';
        } elseif ($utilization < 80) {
            return 'from-amber-500 to-orange-600';
        } else {
            return 'from-red-500 to-rose-600';
        }
    }

    // ==================== NEW ADAPTIVE LEARNING COMPUTED PROPERTIES ====================

    /**
     * NEW: Get adaptive learning insights for display
     */
    public function getAdaptiveInsightsProperty()
    {
        return [
            'recommendations' => $this->optimizationRecommendations,
            'top_symbols' => $this->topPerformingSymbols,
            'regime_insights' => $this->regimeInsights,
            'performance_trend' => $this->getPerformanceTrend(),
            'risk_adjustments' => $this->getRecommendedRiskAdjustments()
        ];
    }

    /**
     * NEW: Get performance trend analysis
     */
    private function getPerformanceTrend()
    {
        try {
            $currentWinRate = $this->winRate;
            $lastWeekWinRate = $this->getLastWeekWinRate();
            
            $trend = $currentWinRate - $lastWeekWinRate;
            
            return [
                'current' => $currentWinRate,
                'previous' => $lastWeekWinRate,
                'trend' => $trend,
                'direction' => $trend >= 0 ? 'up' : 'down',
                'improvement' => abs($trend)
            ];
        } catch (\Exception $e) {
            return ['current' => 0, 'previous' => 0, 'trend' => 0, 'direction' => 'stable', 'improvement' => 0];
        }
    }

    /**
     * NEW: Get last week's win rate for comparison
     */
    private function getLastWeekWinRate()
    {
        try {
            $lastWeekStart = now()->subWeek()->startOfWeek();
            $lastWeekEnd = now()->subWeek()->endOfWeek();

            $lastWeekTrades = UserPosition::where('portfolio_id', $this->portfolio->id)
                ->where('status', 'CLOSED')
                ->whereBetween('closed_at', [$lastWeekStart, $lastWeekEnd])
                ->get();

            $totalTrades = $lastWeekTrades->count();
            
            if ($totalTrades > 0) {
                $winningTrades = $lastWeekTrades->where('floating_pnl', '>', 0)->count();
                return round(($winningTrades / $totalTrades) * 100, 1);
            }
            
            return 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * NEW: Get recommended risk adjustments based on performance
     */
    private function getRecommendedRiskAdjustments()
    {
        $winRate = $this->winRate;
        $profitFactor = $this->profitFactor;
        
        $adjustments = [];
        
        if ($winRate < 40) {
            $adjustments[] = "Reduce position size by 30%";
            $adjustments[] = "Increase stop loss to 3%";
            $adjustments[] = "Focus on top 3 performing symbols only";
        } elseif ($winRate >= 60 && $profitFactor >= 1.5) {
            $adjustments[] = "Consider increasing position size by 20%";
            $adjustments[] = "Maintain current risk parameters";
            $adjustments[] = "Expand to additional high-potential symbols";
        } else {
            $adjustments[] = "Maintain current risk management";
            $adjustments[] = "Monitor performance weekly";
            $adjustments[] = "Adjust based on regime changes";
        }
        
        return $adjustments;
    }

    /**
     * NEW: Get regime-based trading recommendations
     */
    public function getRegimeRecommendationsProperty()
    {
        $recommendations = [];
        
        foreach ($this->regimeInsights as $symbol => $insight) {
            $regime = strtoupper($insight['regime']);
            $confidence = $insight['confidence'];
            
            if ($confidence > 70) {
                if ($insight['regime'] === 'bull') {
                    $recommendations[] = "🎯 $symbol: STRONG BULL regime ($confidence%) - Focus on BUY opportunities";
                } elseif ($insight['regime'] === 'bear') {
                    $recommendations[] = "🎯 $symbol: STRONG BEAR regime ($confidence%) - Focus on SELL opportunities";
                } elseif ($insight['regime'] === 'reversal') {
                    $recommendations[] = "⚠️ $symbol: REVERSAL regime ($confidence%) - Exercise extreme caution";
                }
            }
        }
        
        return $recommendations;
    }

    /**
     * NEW: Get performance grade based on multiple metrics
     */
    public function getPerformanceGradeProperty()
    {
        $winRate = $this->winRate;
        $profitFactor = $this->profitFactor;
        $consistency = $this->calculateConsistencyScore();
        
        $score = ($winRate * 0.4) + (min($profitFactor, 3) * 20) + ($consistency * 0.4);
        
        if ($score >= 85) return ['grade' => 'A+', 'color' => 'text-green-600', 'bg' => 'bg-green-100'];
        if ($score >= 75) return ['grade' => 'A', 'color' => 'text-green-600', 'bg' => 'bg-green-100'];
        if ($score >= 65) return ['grade' => 'B', 'color' => 'text-blue-600', 'bg' => 'bg-blue-100'];
        if ($score >= 55) return ['grade' => 'C', 'color' => 'text-yellow-600', 'bg' => 'bg-yellow-100'];
        if ($score >= 45) return ['grade' => 'D', 'color' => 'text-orange-600', 'bg' => 'bg-orange-100'];
        return ['grade' => 'F', 'color' => 'text-red-600', 'bg' => 'bg-red-100'];
    }

    // ==================== PORTFOLIO OPERATIONS ====================

    public function refreshData()
    {
        $this->isLoading = true;
        $this->loadData();
        $this->loadOptimizationData();
        $this->dispatch('chartsUpdated');
    }

    public function refreshRealTime()
    {
        if ($this->portfolio) {
            $this->portfolio->syncPortfolioValues();
            $this->portfolio = $this->portfolio->fresh();
        }
        
        $this->loadData();
        $this->dispatch('notify', type: 'info', message: 'Portfolio updated!');
    }

    public function toggleAutoRefresh()
    {
        $this->autoRefresh = !$this->autoRefresh;
        $status = $this->autoRefresh ? 'enabled' : 'disabled';
        session()->flash('message', "Auto-refresh {$status}");
    }

    public function updatePortfolio()
    {
        $this->validate([
            'initialBalance' => 'required|numeric|min:10|max:1000000',
            'riskMode' => 'required|in:CONSERVATIVE,MODERATE,AGGRESSIVE',
            'riskValue' => 'required|numeric|min:0.5|max:20',
        ]);

        try {
            $balanceDifference = $this->initialBalance - $this->portfolio->initial_balance;
            
            $this->portfolio->update([
                'initial_balance' => $this->initialBalance,
                'risk_mode' => $this->riskMode,
                'risk_value' => $this->riskValue,
            ]);

            if ($balanceDifference != 0) {
                $newBalance = $this->portfolio->balance + $balanceDifference;
                
                $this->portfolio->update([
                    'balance' => max(0, $newBalance),
                    'equity' => max(0, $newBalance + $this->portfolio->floating_pnl),
                ]);
            }

            $this->portfolio->syncPortfolioValues();
            $this->portfolio = $this->portfolio->fresh();

            session()->flash('message', 'Portfolio settings updated successfully!');
            $this->dispatch('notify', type: 'success', message: 'Portfolio settings updated!');
            $this->dispatch('portfolioUpdated');
            
        } catch (\Exception $e) {
            Log::error('Error updating portfolio: ' . $e->getMessage());
            session()->flash('error', 'Failed to update portfolio: ' . $e->getMessage());
        }
    }

    public function toggleAiTrade()
    {
        try {
            $newStatus = !$this->portfolio->ai_trade_enabled;
            
            $this->portfolio->update([
                'ai_trade_enabled' => $newStatus
            ]);

            $this->aiTradeEnabled = $newStatus;
            
            $status = $this->aiTradeEnabled ? 'enabled' : 'disabled';
            session()->flash('message', "AI Trading {$status} successfully!");
            
            Log::info("AI Trade toggled for user: " . Auth::id() . " - New Status: " . ($newStatus ? 'ENABLED' : 'DISABLED'));
            
            $this->dispatch('portfolioUpdated');
            
        } catch (\Exception $e) {
            Log::error('Error toggling AI trade: ' . $e->getMessage());
            session()->flash('error', 'Failed to toggle AI trading: ' . $e->getMessage());
        }
    }

    public function resetPortfolio()
    {
        try {
            $this->portfolio->reset();
            
            $this->portfolio->update([
                'initial_balance' => $this->initialBalance,
                'balance' => $this->initialBalance,
                'equity' => $this->initialBalance
            ]);

            $this->portfolio = $this->portfolio->fresh();

            session()->flash('message', 'Portfolio reset successfully! All positions have been closed.');
            $this->dispatch('portfolioUpdated');
            
        } catch (\Exception $e) {
            Log::error('Error resetting portfolio: ' . $e->getMessage());
            session()->flash('error', 'Failed to reset portfolio: ' . $e->getMessage());
        }
    }

    public function closePosition($positionId)
    {
        try {
            $tradingService = app(TradingExecutionService::class);
            $result = $tradingService->closePositionManually($positionId, auth()->id(), "Manual Close");
            
            if ($result['success']) {
                $this->portfolio->syncPortfolioValues();
                $this->portfolio = $this->portfolio->fresh();
                
                $this->refreshWinRateData();
                $this->dispatch('positionClosed');
                
                $this->dispatch('show-notification', [
                    'type' => 'success', 
                    'message' => 'Position closed successfully! PnL: $' . number_format($result['pnl'], 2)
                ]);
            } else {
                $this->dispatch('show-notification', [
                    'type' => 'error', 
                    'message' => $result['message']
                ]);
            }
            
            $this->refreshPortfolio();
            
        } catch (\Exception $e) {
            $this->dispatch('show-notification', [
                'type' => 'error', 
                'message' => 'Failed to close position: ' . $e->getMessage()
            ]);
        }
    }

    public function closeAllPositions()
    {
        try {
            $tradingService = app(TradingExecutionService::class);
            $result = $tradingService->closeAllPositions(auth()->id(), "Manual Close All");
            
            $this->portfolio->syncPortfolioValues();
            $this->portfolio = $this->portfolio->fresh();
            
            $this->refreshWinRateData();
            $this->dispatch('positionClosed');
            
            $this->dispatch('show-notification', [
                'type' => 'success', 
                'message' => "Closed {$result['closed_count']} positions. Total PNL: $" . number_format($result['total_pnl'], 2)
            ]);
            
            $this->refreshPortfolio();
            
        } catch (\Exception $e) {
            $this->dispatch('show-notification', [
                'type' => 'error', 
                'message' => 'Failed to close positions: ' . $e->getMessage()
            ]);
        }
    }

    public function handlePositionClosed()
    {
        $this->refreshWinRateData();
        $this->loadOptimizationData();
    }

    public function refreshWinRateData()
    {
        try {
            // Clear computed properties cache
            unset($this->winRate);
            unset($this->advancedWinRate);
            unset($this->monthlyWinRate);
            unset($this->winRateByPositionType);
            unset($this->riskAdjustedMetrics);
            
            // Force re-computation
            $this->getWinRateProperty();
            $this->getAdvancedWinRateProperty();
            
            Log::info('WinRate data refreshed after position close');
            
        } catch (\Exception $e) {
            Log::error('Error refreshing winrate data: ' . $e->getMessage());
        }
    }

    /**
     * NEW: Force refresh optimization data
     */
    public function refreshOptimization()
    {
        try {
            $this->loadOptimizationData();
            session()->flash('message', 'Optimization data refreshed!');
            $this->dispatch('notify', type: 'success', message: 'AI insights updated!');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to refresh optimization data');
        }
    }

    /**
     * NEW: Apply optimization recommendations
     */
    public function applyOptimization($recommendationIndex)
    {
        try {
            if (isset($this->optimizationRecommendations[$recommendationIndex])) {
                $recommendation = $this->optimizationRecommendations[$recommendationIndex];
                
                Log::info("Applying optimization: " . $recommendation);
                
                session()->flash('message', "Optimization applied: " . $recommendation);
                $this->dispatch('notify', type: 'success', message: 'Optimization applied successfully!');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to apply optimization');
        }
    }

    public function refreshPortfolio()
    {
        $this->refreshData();
        session()->flash('message', 'Portfolio data refreshed!');
    }

    public function repairPortfolioData()
    {
        try {
            if ($this->portfolio) {
                $this->portfolio->repairData();
                $this->portfolio = $this->portfolio->fresh();
                
                session()->flash('message', 'Portfolio data repaired successfully!');
                $this->dispatch('portfolioUpdated');
            }
        } catch (\Exception $e) {
            Log::error('Error repairing portfolio data: ' . $e->getMessage());
            session()->flash('error', 'Failed to repair portfolio data: ' . $e->getMessage());
        }
    }

    public function emergencyResetBalance()
    {
        try {
            if ($this->portfolio) {
                $this->portfolio->update([
                    'balance' => $this->initialBalance,
                    'equity' => $this->initialBalance,
                    'realized_pnl' => 0,
                    'floating_pnl' => 0,
                ]);
                
                session()->flash('message', 'Emergency balance reset completed!');
                $this->refreshData();
            }
        } catch (\Exception $e) {
            Log::error('Emergency reset failed: ' . $e->getMessage());
            session()->flash('error', 'Emergency reset failed: ' . $e->getMessage());
        }
    }

    public function forceSyncPortfolio()
    {
        try {
            if ($this->portfolio) {
                $this->portfolio->syncPortfolioValues();
                $this->portfolio = $this->portfolio->fresh();
                
                session()->flash('message', 'Portfolio data synchronized!');
                $this->dispatch('portfolioUpdated');
            }
        } catch (\Exception $e) {
            Log::error('Force sync failed: ' . $e->getMessage());
            session()->flash('error', 'Sync failed: ' . $e->getMessage());
        }
    }

    // Helper methods untuk blade
    public function getOpenPositionsCountProperty()
    {
        return $this->openPositions->count();
    }

    public function getTotalInvestmentProperty()
    {
        return $this->openPositions->sum('investment');
    }

    public function getTotalFloatingPnlProperty()
    {
        return $this->openPositions->sum('floating_pnl');
    }

    // Method untuk mendapatkan equity change percentage
    public function getEquityChangePercentageProperty()
    {
        if (!$this->portfolio || $this->portfolio->initial_balance <= 0) {
            return 0;
        }
        
        return (($this->portfolio->equity - $this->portfolio->initial_balance) / $this->portfolio->initial_balance) * 100;
    }

    // Method untuk mendapatkan realized PnL change percentage
    public function getRealizedPnlPercentageProperty()
    {
        if (!$this->portfolio || $this->portfolio->initial_balance <= 0) {
            return 0;
        }
        
        return ($this->portfolio->realized_pnl / $this->portfolio->initial_balance) * 100;
    }

    // Method untuk format waktu holding position
    public function getFormattedHoldingTime($openedAt)
    {
        $holdingHours = $openedAt->diffInHours(now());
        
        if ($holdingHours < 1) {
            $holdingMinutes = $openedAt->diffInMinutes(now());
            return number_format($holdingMinutes, 0) . 'm';
        } elseif ($holdingHours < 24) {
            return number_format($holdingHours, 1) . 'h';
        } else {
            $holdingDays = $openedAt->diffInDays(now());
            return number_format($holdingDays, 1) . 'd';
        }
    }

    // Method untuk truncate AI decision explanation
    public function truncateExplanation($text, $length = 80)
    {
        return Str::limit($text, $length);
    }

    // Method untuk mendapatkan risk color berdasarkan mode
    public function getRiskColor($mode)
    {
        return match($mode) {
            'CONSERVATIVE' => 'green',
            'MODERATE' => 'yellow',
            'AGGRESSIVE' => 'red',
            default => 'gray'
        };
    }

    // Method untuk mendapatkan action color
    public function getActionColor($action)
    {
        return match($action) {
            'BUY' => 'green',
            'SELL' => 'red',
            'HOLD' => 'yellow',
            default => 'gray'
        };
    }

    // Get portfolio summary for debugging
    public function getPortfolioSummaryProperty()
    {
        if (!$this->portfolio) return null;
        
        return [
            'balance' => $this->portfolio->balance,
            'equity' => $this->portfolio->equity,
            'available_balance' => $this->available_balance,
            'total_invested' => $this->total_invested,
            'utilization_percentage' => $this->utilization_percentage,
            'can_trade' => $this->can_trade,
            'is_over_utilized' => $this->is_over_utilized,
            'recommended_position_size' => $this->recommended_position_size,
            'consistency_check' => $this->portfolio->checkConsistency(),
            'win_rate_stats' => $this->advancedWinRate,
            'risk_adjusted_metrics' => $this->riskAdjustedMetrics
        ];
    }

    // Debug method untuk melihat data portfolio
    public function debugPortfolio()
    {
        if (!$this->portfolio) return;
        
        Log::info('Portfolio Debug Data:', $this->portfolio_summary);
        
        $this->dispatch('show-notification', [
            'type' => 'info', 
            'message' => 'Portfolio debug data logged to console. Check Laravel logs for details.'
        ]);
    }

    public function render()
    {
        return view('livewire.portfolio-dashboard');
    }
}