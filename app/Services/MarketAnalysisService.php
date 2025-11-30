<?php
// app/Services/MarketAnalysisService.php

namespace App\Services;

use App\Models\MarketRegime;
use App\Models\RegimeSummary;
use App\Models\MarketPattern;
use Carbon\Carbon;

class MarketAnalysisService
{
    public function getRegimeDistribution($date = null)
    {
        $date = $date ?: Carbon::today();
        
        $currentRegimes = MarketRegime::where('date', $date)
            ->orderBy('dominance_score', 'desc')
            ->get();

        $distribution = [
            'bull' => 0,
            'bear' => 0,
            'neutral' => 0,
            'volatile' => 0,
            'reversal' => 0
        ];

        foreach ($currentRegimes as $regime) {
            if (isset($distribution[$regime->regime])) {
                $distribution[$regime->regime]++;
            }
        }

        $total = $currentRegimes->count();
        if ($total > 0) {
            foreach ($distribution as $key => $value) {
                $distribution[$key] = [
                    'count' => $value,
                    'percentage' => round(($value / $total) * 100, 2)
                ];
            }
        }

        return $distribution;
    }

    public function calculateMarketMetrics($date = null)
    {
        $date = $date ?: Carbon::today();
        
        $currentRegimes = MarketRegime::where('date', $date)->get();
        $marketSummary = RegimeSummary::where('date', $date)
            ->orderBy('created_at', 'desc')
            ->first();

        $totalSymbols = $currentRegimes->count();
        $avgConfidence = $currentRegimes->avg('regime_confidence') * 100;
        $avgVolatility = $currentRegimes->avg('volatility_24h') * 100;
        
        // Calculate regime strength
        $bullCount = $currentRegimes->where('regime', 'bull')->count();
        $bearCount = $currentRegimes->where('regime', 'bear')->count();
        $regimeStrength = $totalSymbols > 0 ? abs($bullCount - $bearCount) / $totalSymbols * 100 : 0;

        return [
            'total_symbols' => $totalSymbols,
            'avg_confidence' => round($avgConfidence, 1),
            'avg_volatility' => round($avgVolatility, 2),
            'regime_strength' => round($regimeStrength, 1),
            'market_health' => $marketSummary->market_health_score ?? 50,
            'sentiment_score' => $marketSummary->sentiment_score ?? 50
        ];
    }

    public function getMarketPatterns($date = null)
    {
        $date = $date ?: Carbon::today();
        
        return MarketPattern::where('date', $date)
            ->where('is_active', true)
            ->orderBy('confidence', 'desc')
            ->get();
    }
}