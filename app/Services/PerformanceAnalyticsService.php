<?php

namespace App\Services;

use App\Models\AiDecision;
use App\Models\TradeHistory;
use App\Models\UserPosition;
use App\Models\MarketRegime;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PerformanceAnalyticsService
{
    /**
     * Analyze trading performance by regime
     */
    public function analyzeRegimePerformance($days = 30)
    {
        $startDate = now()->subDays($days);
        
        $results = DB::table('ai_decisions')
            ->join('trade_histories', 'ai_decisions.id', '=', 'trade_histories.ai_decision_id')
            ->where('ai_decisions.created_at', '>=', $startDate)
            ->whereNotNull('trade_histories.pnl')
            ->select(
                DB::raw('COUNT(*) as total_trades'),
                DB::raw('SUM(CASE WHEN trade_histories.pnl > 0 THEN 1 ELSE 0 END) as winning_trades'),
                DB::raw('SUM(CASE WHEN trade_histories.pnl <= 0 THEN 1 ELSE 0 END) as losing_trades'),
                DB::raw('SUM(trade_histories.pnl) as total_pnl'),
                DB::raw('AVG(trade_histories.pnl) as avg_pnl'),
                DB::raw('MAX(trade_histories.pnl) as best_trade'),
                DB::raw('MIN(trade_histories.pnl) as worst_trade')
            )
            ->first();

        $winRate = $results->total_trades > 0 
            ? ($results->winning_trades / $results->total_trades) * 100 
            : 0;

        Log::info("📊 Performance Analytics ({$days} days):", [
            'total_trades' => $results->total_trades,
            'win_rate' => round($winRate, 2) . '%',
            'winning_trades' => $results->winning_trades,
            'losing_trades' => $results->losing_trades,
            'total_pnl' => $results->total_pnl,
            'avg_pnl' => $results->avg_pnl,
            'best_trade' => $results->best_trade,
            'worst_trade' => $results->worst_trade
        ]);

        return [
            'period' => "{$days} days",
            'total_trades' => $results->total_trades,
            'win_rate' => round($winRate, 2),
            'winning_trades' => $results->winning_trades,
            'losing_trades' => $results->losing_trades,
            'total_pnl' => $results->total_pnl,
            'avg_pnl' => round($results->avg_pnl, 2),
            'best_trade' => $results->best_trade,
            'worst_trade' => $results->worst_trade,
            'profit_factor' => $results->losing_trades > 0 
                ? abs($results->total_pnl / ($results->losing_trades * $results->avg_pnl)) 
                : 0
        ];
    }

    /**
     * Analyze performance by market regime
     */
    public function analyzeRegimeSpecificPerformance($days = 30)
    {
        $startDate = now()->subDays($days);
        
        $regimePerformance = DB::table('ai_decisions')
            ->join('trade_histories', 'ai_decisions.id', '=', 'trade_histories.ai_decision_id')
            ->join('market_regimes', function($join) {
                $join->on('ai_decisions.symbol', '=', 'market_regimes.symbol')
                     ->whereColumn('ai_decisions.created_at', '>=', 'market_regimes.timestamp');
            })
            ->where('ai_decisions.created_at', '>=', $startDate)
            ->whereNotNull('trade_histories.pnl')
            ->select(
                'market_regimes.regime',
                DB::raw('COUNT(*) as total_trades'),
                DB::raw('SUM(CASE WHEN trade_histories.pnl > 0 THEN 1 ELSE 0 END) as winning_trades'),
                DB::raw('SUM(trade_histories.pnl) as total_pnl'),
                DB::raw('AVG(trade_histories.pnl) as avg_pnl')
            )
            ->groupBy('market_regimes.regime')
            ->get();

        $analysis = [];
        foreach ($regimePerformance as $regime) {
            $winRate = $regime->total_trades > 0 
                ? ($regime->winning_trades / $regime->total_trades) * 100 
                : 0;

            $analysis[$regime->regime] = [
                'total_trades' => $regime->total_trades,
                'win_rate' => round($winRate, 2),
                'winning_trades' => $regime->winning_trades,
                'total_pnl' => $regime->total_pnl,
                'avg_pnl' => round($regime->avg_pnl, 2)
            ];
        }

        Log::info("🎯 Regime-Based Performance Analysis:", $analysis);

        return $analysis;
    }

    /**
     * Analyze AI decision accuracy
     */
    public function analyzeAIDecisionAccuracy($days = 30)
    {
        $startDate = now()->subDays($days);
        
        $accuracy = DB::table('ai_decisions')
            ->leftJoin('trade_histories', 'ai_decisions.id', '=', 'trade_histories.ai_decision_id')
            ->where('ai_decisions.created_at', '>=', $startDate)
            ->where('ai_decisions.action', '!=', 'HOLD')
            ->select(
                'ai_decisions.action',
                DB::raw('COUNT(*) as total_decisions'),
                DB::raw('SUM(CASE WHEN trade_histories.pnl > 0 THEN 1 ELSE 0 END) as profitable_trades'),
                DB::raw('AVG(ai_decisions.confidence) as avg_confidence')
            )
            ->groupBy('ai_decisions.action')
            ->get();

        $analysis = [];
        foreach ($accuracy as $action) {
            $accuracyRate = $action->total_decisions > 0 
                ? ($action->profitable_trades / $action->total_decisions) * 100 
                : 0;

            $analysis[$action->action] = [
                'total_decisions' => $action->total_decisions,
                'profitable_trades' => $action->profitable_trades,
                'accuracy_rate' => round($accuracyRate, 2),
                'avg_confidence' => round($action->avg_confidence, 2)
            ];
        }

        Log::info("🤖 AI Decision Accuracy Analysis:", $analysis);

        return $analysis;
    }

    /**
     * Generate daily performance report
     */
    public function generateDailyReport()
    {
        $today = now()->format('Y-m-d');
        
        $dailyStats = DB::table('trade_histories')
            ->whereDate('created_at', $today)
            ->select(
                DB::raw('COUNT(*) as daily_trades'),
                DB::raw('SUM(pnl) as daily_pnl'),
                DB::raw('AVG(pnl) as avg_daily_pnl'),
                DB::raw('SUM(CASE WHEN pnl > 0 THEN 1 ELSE 0 END) as daily_wins'),
                DB::raw('SUM(CASE WHEN pnl <= 0 THEN 1 ELSE 0 END) as daily_losses')
            )
            ->first();

        $dailyWinRate = $dailyStats->daily_trades > 0 
            ? ($dailyStats->daily_wins / $dailyStats->daily_trades) * 100 
            : 0;

        $report = [
            'date' => $today,
            'daily_trades' => $dailyStats->daily_trades,
            'daily_pnl' => $dailyStats->daily_pnl,
            'avg_daily_pnl' => round($dailyStats->avg_daily_pnl, 2),
            'daily_win_rate' => round($dailyWinRate, 2),
            'daily_wins' => $dailyStats->daily_wins,
            'daily_losses' => $dailyStats->daily_losses
        ];

        Log::info("📈 Daily Performance Report:", $report);

        return $report;
    }

    /**
     * Identify best performing symbols
     */
    public function getTopPerformingSymbols($limit = 5, $days = 30)
    {
        $startDate = now()->subDays($days);
        
        $topSymbols = DB::table('trade_histories')
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('pnl')
            ->select(
                'symbol',
                DB::raw('COUNT(*) as trade_count'),
                DB::raw('SUM(pnl) as total_pnl'),
                DB::raw('AVG(pnl) as avg_pnl'),
                DB::raw('SUM(CASE WHEN pnl > 0 THEN 1 ELSE 0 END) as winning_trades')
            )
            ->groupBy('symbol')
            ->orderBy('total_pnl', 'desc')
            ->limit($limit)
            ->get();

        $analysis = [];
        foreach ($topSymbols as $symbol) {
            $winRate = $symbol->trade_count > 0 
                ? ($symbol->winning_trades / $symbol->trade_count) * 100 
                : 0;

            $analysis[] = [
                'symbol' => $symbol->symbol,
                'trade_count' => $symbol->trade_count,
                'total_pnl' => $symbol->total_pnl,
                'avg_pnl' => round($symbol->avg_pnl, 2),
                'win_rate' => round($winRate, 2)
            ];
        }

        Log::info("🏆 Top Performing Symbols:", $analysis);

        return $analysis;
    }
}