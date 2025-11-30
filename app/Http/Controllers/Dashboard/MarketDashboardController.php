<?php
// app/Http/Controllers/Dashboard/MarketDashboardController.php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\MarketRegime;
use App\Models\RegimeSummary;
use App\Models\MarketEvent;
use App\Models\MarketAlert;
use App\Models\MarketPattern;
use App\Models\DominanceHistory;
use App\Services\MarketAnalysisService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MarketDashboardController extends Controller
{
    protected $marketAnalysisService;

    public function __construct(MarketAnalysisService $marketAnalysisService)
    {
        $this->marketAnalysisService = $marketAnalysisService;
    }

    public function index()
    {
        $today = Carbon::today();
        
        // Get latest market summary
        $marketSummary = RegimeSummary::where('date', $today)
            ->orderBy('created_at', 'desc')
            ->first();
            
        // If no summary for today, use yesterday's
        if (!$marketSummary) {
            $marketSummary = RegimeSummary::orderBy('date', 'desc')->first();
        }

        // Get current regime data
        $currentRegimes = MarketRegime::where('date', $today)
            ->orderBy('dominance_score', 'desc')
            ->get();

        // Get dominance leaders
        $dominanceLeaders = $currentRegimes->take(10);

        // Get regime distribution using service
        $regimeDistribution = $this->marketAnalysisService->getRegimeDistribution($today);

        // Get recent market events
        $recentEvents = MarketEvent::where('date', '>=', $today->subDays(3))
            ->orderBy('triggered_at', 'desc')
            ->limit(10)
            ->get();

        // Get unread alerts
        $criticalAlerts = MarketAlert::where('is_read', false)
            ->where('severity', 'critical')
            ->orderBy('triggered_at', 'desc')
            ->get();

        // Get historical data for charts
        $historicalSummary = RegimeSummary::where('date', '>=', $today->subDays(30))
            ->orderBy('date')
            ->get();

        // Get market patterns using service
        $marketPatterns = $this->marketAnalysisService->getMarketPatterns($today);

        // Calculate market metrics using service
        $marketMetrics = $this->marketAnalysisService->calculateMarketMetrics($today);

        // Generate research insights
        $researchInsights = $this->generateResearchInsights($regimeDistribution, $marketMetrics, $marketPatterns, $dominanceLeaders);

        // Calculate pattern performance
        $patternPerformance = $this->calculatePatternPerformance($marketPatterns);

        return view('dashboard.market', compact(
            'marketSummary',
            'currentRegimes',
            'dominanceLeaders',
            'regimeDistribution',
            'recentEvents',
            'criticalAlerts',
            'historicalSummary',
            'marketMetrics',
            'marketPatterns',
            'researchInsights',
            'patternPerformance'
        ));
    }

    // TAMBAHKAN METHOD INI - Static method untuk diakses dari Livewire
    public static function getDashboardData()
    {
        $today = Carbon::today();
        
        $currentRegimes = MarketRegime::where('date', $today)->get();
        $marketSummary = RegimeSummary::where('date', $today)->first();
        $marketPatterns = MarketPattern::where('date', $today)->where('is_active', true)->get();
        $dominanceLeaders = $currentRegimes->take(10);

        $controller = new self(app()->make(MarketAnalysisService::class));
        
        $regimeDistribution = $controller->marketAnalysisService->getRegimeDistribution($today);
        $marketMetrics = $controller->marketAnalysisService->calculateMarketMetrics($today);
        $researchInsights = $controller->generateResearchInsights($regimeDistribution, $marketMetrics, $marketPatterns, $dominanceLeaders);

        return [
            'regimeDistribution' => $regimeDistribution,
            'marketMetrics' => $marketMetrics,
            'researchInsights' => $researchInsights
        ];
    }

    // TAMBAHKAN METHOD INI - Public method untuk research insights saja
    public static function getResearchInsights()
    {
        $today = Carbon::today();
        
        $currentRegimes = MarketRegime::where('date', $today)->get();
        $marketSummary = RegimeSummary::where('date', $today)->first();
        $marketPatterns = MarketPattern::where('date', $today)->where('is_active', true)->get();
        $dominanceLeaders = $currentRegimes->take(10);

        $controller = new self(app()->make(MarketAnalysisService::class));
        
        $regimeDistribution = $controller->marketAnalysisService->getRegimeDistribution($today);
        $marketMetrics = $controller->marketAnalysisService->calculateMarketMetrics($today);
        
        return $controller->generateResearchInsights($regimeDistribution, $marketMetrics, $marketPatterns, $dominanceLeaders);
    }

    private function generateResearchInsights($regimeDistribution, $marketMetrics, $marketPatterns, $dominanceLeaders)
    {
        $insights = [];
        
        // Insight 1: Bull/Bear Market Dominance
        $bullPercentage = $regimeDistribution['bull']['percentage'] ?? 0;
        $bearPercentage = $regimeDistribution['bear']['percentage'] ?? 0;
        
        if ($bullPercentage > 60) {
            $insights[] = [
                'type' => 'opportunity',
                'title' => 'Strong Bull Market Dominance',
                'description' => "{$bullPercentage}% of assets in bull regime suggests favorable trading conditions with high probability of upward momentum",
                'confidence' => min(90, $bullPercentage),
                'impact' => 'high'
            ];
        } elseif ($bearPercentage > 60) {
            $insights[] = [
                'type' => 'warning',
                'title' => 'Strong Bear Market Dominance',
                'description' => "{$bearPercentage}% of assets in bear regime indicates cautious market sentiment - consider defensive positions",
                'confidence' => min(85, $bearPercentage),
                'impact' => 'high'
            ];
        }

        // Insight 2: Market Health Assessment
        $healthScore = $marketMetrics['market_health'];
        if ($healthScore >= 70) {
            $insights[] = [
                'type' => 'opportunity',
                'title' => 'Excellent Market Health',
                'description' => "Market health score of {$healthScore}/100 indicates robust conditions with strong fundamentals",
                'confidence' => $healthScore - 10,
                'impact' => 'medium'
            ];
        } elseif ($healthScore <= 30) {
            $insights[] = [
                'type' => 'critical',
                'title' => 'Poor Market Health',
                'description' => "Market health score of {$healthScore}/100 suggests underlying weakness - exercise caution",
                'confidence' => 100 - $healthScore,
                'impact' => 'high'
            ];
        }

        // Insight 3: Volatility Analysis
        $avgVolatility = $marketMetrics['avg_volatility'];
        if ($avgVolatility > 15) {
            $insights[] = [
                'type' => 'warning',
                'title' => 'High Market Volatility',
                'description' => "Average volatility at {$avgVolatility}% indicates turbulent conditions - adjust position sizing accordingly",
                'confidence' => min(80, $avgVolatility * 4),
                'impact' => 'medium'
            ];
        } elseif ($avgVolatility < 3) {
            $insights[] = [
                'type' => 'info',
                'title' => 'Low Volatility Environment',
                'description' => "Average volatility at {$avgVolatility}% suggests stable conditions - suitable for range trading strategies",
                'confidence' => 70,
                'impact' => 'low'
            ];
        }

        // Insight 4: Pattern Opportunities
        $highConfidencePatterns = $marketPatterns->where('confidence', '>', 0.7)->count();
        if ($highConfidencePatterns > 5) {
            $insights[] = [
                'type' => 'opportunity',
                'title' => 'Multiple High-Confidence Patterns',
                'description' => "{$highConfidencePatterns} trading patterns with >70% confidence detected - favorable setup conditions",
                'confidence' => 75,
                'impact' => 'medium'
            ];
        }

        // Insight 5: Dominance Concentration
        $topDominance = $dominanceLeaders->take(3);
        $totalTopDominance = $topDominance->sum('dominance_score');
        
        if ($totalTopDominance > 60) {
            $topSymbols = $topDominance->pluck('symbol')->implode(', ');
            $insights[] = [
                'type' => 'info',
                'title' => 'High Market Concentration',
                'description' => "Top 3 assets ({$topSymbols}) represent {$totalTopDominance}% of total dominance - market movement heavily influenced",
                'confidence' => 80,
                'impact' => 'medium'
            ];
        }

        // Insight 6: Regime Strength
        $regimeStrength = $marketMetrics['regime_strength'];
        if ($regimeStrength > 70) {
            $insights[] = [
                'type' => 'info',
                'title' => 'Strong Regime Consensus',
                'description' => "Regime strength of {$regimeStrength}% indicates clear market direction with low ambiguity",
                'confidence' => $regimeStrength,
                'impact' => 'medium'
            ];
        } elseif ($regimeStrength < 30) {
            $insights[] = [
                'type' => 'warning',
                'title' => 'Weak Regime Consensus',
                'description' => "Regime strength of {$regimeStrength}% suggests mixed signals and potential choppy market conditions",
                'confidence' => 100 - $regimeStrength,
                'impact' => 'medium'
            ];
        }

        // Limit to 5 most important insights
        return array_slice($insights, 0, 5);
    }

    private function calculatePatternPerformance($marketPatterns)
    {
        $performance = [];
        
        // Group patterns by type and calculate metrics
        $patternTypes = $marketPatterns->groupBy('pattern_name');
        
        foreach ($patternTypes as $patternName => $patterns) {
            $accuracy = $this->calculatePatternAccuracy($patternName);
            $avgConfidence = $patterns->avg('confidence') * 100;
            $successRate = $this->calculatePatternSuccessRate($patternName);
            $activeCount = $patterns->count();
            
            $performance[] = [
                'name' => $patternName,
                'accuracy' => $accuracy,
                'avg_confidence' => round($avgConfidence, 1),
                'success_rate' => $successRate,
                'active_count' => $activeCount
            ];
        }
        
        // Add default patterns if no data exists
        if (empty($performance)) {
            $performance = [
                [
                    'name' => 'RSI Oversold',
                    'accuracy' => 72,
                    'avg_confidence' => 68.5,
                    'success_rate' => 70,
                    'active_count' => $marketPatterns->where('pattern_name', 'like', '%RSI%')->count() ?: 3
                ],
                [
                    'name' => 'Support Break',
                    'accuracy' => 65,
                    'avg_confidence' => 71.2,
                    'success_rate' => 63,
                    'active_count' => $marketPatterns->where('pattern_name', 'like', '%Support%')->count() ?: 2
                ],
                [
                    'name' => 'Volatility Spike',
                    'accuracy' => 58,
                    'avg_confidence' => 62.8,
                    'success_rate' => 55,
                    'active_count' => $marketPatterns->where('pattern_name', 'like', '%Volatility%')->count() ?: 1
                ],
                [
                    'name' => 'Trend Reversal',
                    'accuracy' => 61,
                    'avg_confidence' => 59.3,
                    'success_rate' => 58,
                    'active_count' => $marketPatterns->where('pattern_name', 'like', '%Reversal%')->count() ?: 2
                ]
            ];
        }
        
        // Sort by accuracy descending
        usort($performance, function($a, $b) {
            return $b['accuracy'] - $a['accuracy'];
        });
        
        return $performance;
    }

    private function calculatePatternAccuracy($patternName)
    {
        // This would typically query historical pattern performance data
        // For now, return mock data based on pattern type
        
        $accuracyMap = [
            'RSI Oversold' => 72,
            'RSI Overbought' => 68,
            'Support Break' => 65,
            'Resistance Break' => 63,
            'Volatility Spike' => 58,
            'Trend Reversal' => 61,
            'Moving Average Cross' => 59,
            'Bollinger Squeeze' => 66
        ];
        
        return $accuracyMap[$patternName] ?? 60;
    }

    private function calculatePatternSuccessRate($patternName)
    {
        // This would typically calculate from historical successful patterns
        // For now, return mock data
        
        $successMap = [
            'RSI Oversold' => 70,
            'RSI Overbought' => 65,
            'Support Break' => 63,
            'Resistance Break' => 61,
            'Volatility Spike' => 55,
            'Trend Reversal' => 58,
            'Moving Average Cross' => 57,
            'Bollinger Squeeze' => 64
        ];
        
        return $successMap[$patternName] ?? 60;
    }

    public function regimeDetail($regimeType)
    {
        $today = Carbon::today();
        $validRegimes = ['bull', 'bear', 'neutral', 'volatile', 'reversal'];
        
        if (!in_array($regimeType, $validRegimes)) {
            abort(404);
        }

        $regimeAssets = MarketRegime::where('date', $today)
            ->where('regime', $regimeType)
            ->orderBy('dominance_score', 'desc')
            ->get();

        $regimeStats = $this->getRegimeStats($regimeAssets, $regimeType);

        return view('dashboard.regime-detail', compact(
            'regimeType',
            'regimeAssets',
            'regimeStats'
        ));
    }

    private function getRegimeStats($assets, $regimeType)
    {
        return [
            'total_assets' => $assets->count(),
            'avg_confidence' => round($assets->avg('regime_confidence') * 100, 1),
            'avg_volatility' => round($assets->avg('volatility_24h') * 100, 2),
            'avg_dominance' => round($assets->avg('dominance_score'), 2),
            'top_asset' => $assets->sortByDesc('dominance_score')->first()
        ];
    }

    public function symbolDetail($symbol)
    {
        $today = Carbon::today();
        
        $symbolData = MarketRegime::where('symbol', $symbol)
            ->where('date', $today)
            ->first();

        if (!$symbolData) {
            abort(404, 'Symbol not found');
        }

        $historicalData = MarketRegime::where('symbol', $symbol)
            ->where('date', '>=', $today->subDays(30))
            ->orderBy('date')
            ->get();

        $regimeHistory = $this->getRegimeHistory($historicalData);

        return view('dashboard.symbol-detail', compact(
            'symbolData',
            'historicalData',
            'regimeHistory'
        ));
    }

    private function getRegimeHistory($historicalData)
    {
        $history = [];
        foreach ($historicalData as $data) {
            $history[] = [
                'date' => $data->date->format('Y-m-d'),
                'regime' => $data->regime,
                'confidence' => $data->regime_confidence * 100,
                'price' => $data->price,
                'dominance' => $data->dominance_score
            ];
        }
        return $history;
    }

    public function markAlertRead($alertId)
    {
        $alert = MarketAlert::findOrFail($alertId);
        $alert->update(['is_read' => true]);

        return redirect()->back()->with('success', 'Alert marked as read');
    }

    public function markEventRead($eventId)
    {
        $event = MarketEvent::findOrFail($eventId);
        $event->update(['is_read' => true]);

        return redirect()->back()->with('success', 'Event marked as read');
    }
}