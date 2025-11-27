<?php
// app/Services/Analytics/InsightGenerator.php

namespace App\Services\Analytics;

use App\Models\MarketEvent;
use App\Models\MarketRegime;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class InsightGenerator
{
    public function generateAndStoreInsights(array $processedData, array $dominanceScores)
    {
        $this->detectRegimeChanges($processedData);
        $this->detectDominanceShifts($dominanceScores);
        $this->generateMarketInsights($processedData, $dominanceScores);
    }
    
    private function detectRegimeChanges(array $currentData)
    {
        $yesterday = Carbon::yesterday();
        
        foreach ($currentData as $current) {
            $previous = MarketRegime::where('symbol', $current['symbol'])
                ->where('date', $yesterday)
                ->orderBy('created_at', 'desc')
                ->first();
                
            if ($previous && $previous->regime !== $current['regime']) {
                MarketEvent::create([
                    'date' => Carbon::today(),
                    'symbol' => $current['symbol'],
                    'event_type' => 'regime_change',
                    'title' => "Regime Change: {$current['symbol']}",
                    'description' => "{$current['symbol']} changed from {$previous->regime} to {$current['regime']} with {$current['regime_confidence']} confidence",
                    'previous_state' => ['regime' => $previous->regime, 'confidence' => $previous->regime_confidence],
                    'current_state' => ['regime' => $current['regime'], 'confidence' => $current['regime_confidence']],
                    'severity' => $this->determineRegimeChangeSeverity($previous->regime, $current['regime']),
                    'triggered_at' => now()
                ]);
            }
        }
    }
    
    private function detectDominanceShifts(array $currentDominance)
    {
        $yesterday = Carbon::yesterday();
        
        // Get yesterday's top 5
        $previousTop = MarketRegime::where('date', $yesterday)
            ->orderBy('dominance_score', 'desc')
            ->limit(5)
            ->pluck('symbol')
            ->toArray();
            
        $currentTop = array_slice(array_keys($currentDominance), 0, 5);
        
        // Check for changes in top 5
        $changes = array_diff($previousTop, $currentTop);
        
        if (!empty($changes)) {
            MarketEvent::create([
                'date' => Carbon::today(),
                'symbol' => null,
                'event_type' => 'dominance_shift',
                'title' => 'Top 5 Dominance Shift',
                'description' => "Dominance ranking changed. Out: " . implode(', ', $changes) . ". In: " . implode(', ', array_diff($currentTop, $previousTop)),
                'previous_state' => ['top_5' => $previousTop],
                'current_state' => ['top_5' => $currentTop],
                'severity' => 'warning',
                'triggered_at' => now()
            ]);
        }
    }
    
    private function generateMarketInsights(array $processedData, array $dominanceScores)
    {
        $insights = [];
        
        // Insight 1: Overall market sentiment
        $bullCount = count(array_filter($processedData, fn($item) => $item['regime'] === 'bull'));
        $bearCount = count(array_filter($processedData, fn($item) => $item['regime'] === 'bear'));
        $total = count($processedData);
        
        if ($bullCount / $total > 0.6) {
            $insights[] = "Strong bullish sentiment with {$bullCount} of {$total} assets in bull regime";
        } elseif ($bearCount / $total > 0.6) {
            $insights[] = "Strong bearish sentiment with {$bearCount} of {$total} assets in bear regime";
        }
        
        // Insight 2: Volatility analysis
        $avgVolatility = array_sum(array_column($processedData, 'volatility_24h')) / $total;
        if ($avgVolatility > 0.08) {
            $insights[] = "High market volatility detected (avg: " . round($avgVolatility * 100, 2) . "%)";
        }
        
        // Insight 3: Dominance concentration
        $top3Dominance = array_sum(array_slice(array_column($dominanceScores, 'score'), 0, 3));
        if ($top3Dominance > 70) {
            $insights[] = "High market concentration - Top 3 assets hold " . round($top3Dominance, 2) . "% dominance";
        }
        
        // Store insights
        foreach ($insights as $insight) {
            MarketEvent::create([
                'date' => Carbon::today(),
                'event_type' => 'market_insight',
                'title' => 'Market Insight',
                'description' => $insight,
                'severity' => 'info',
                'triggered_at' => now()
            ]);
        }
    }
    
    private function determineRegimeChangeSeverity(string $fromRegime, string $toRegime): string
    {
        $criticalChanges = [
            'bull' => 'bear',
            'bear' => 'bull',
            'neutral' => 'volatile'
        ];
        
        return isset($criticalChanges[$fromRegime]) && $criticalChanges[$fromRegime] === $toRegime 
            ? 'critical' 
            : 'warning';
    }
}