<?php
// app/Services/Analytics/DominanceCalculator.php

namespace App\Services\Analytics;

class DominanceCalculator
{
    public function calculateAdvancedDominance(array $marketData): array
    {
        $totalMarketCap = array_sum(array_column($marketData, 'market_cap'));
        $totalVolume = array_sum(array_column($marketData, 'volume'));
        
        $scores = [];
        
        foreach ($marketData as $symbol => $data) {
            $volumeDominance = $data['volume'] / $totalVolume;
            $marketCapDominance = $data['market_cap'] / $totalMarketCap;
            $pricePerformance = $this->calculatePerformanceScore($data);
            $liquidityScore = $this->calculateLiquidityScore($data);
            $sentimentScore = $data['sentiment_score'] ?? 0.5;
            
            $dominanceScore = (
                $marketCapDominance * 0.35 +
                $volumeDominance * 0.25 +
                $pricePerformance * 0.20 +
                $liquidityScore * 0.15 +
                $sentimentScore * 0.05
            ) * 100;
            
            $scores[$symbol] = [
                'score' => round($dominanceScore, 2),
                'components' => [
                    'market_cap_dominance' => round($marketCapDominance * 100, 2),
                    'volume_dominance' => round($volumeDominance * 100, 2),
                    'price_performance' => round($pricePerformance * 100, 2),
                    'liquidity_score' => round($liquidityScore * 100, 2),
                    'sentiment_score' => round($sentimentScore * 100, 2)
                ]
            ];
        }
        
        // Normalize to 100%
        return $this->normalizeScores($scores);
    }
    
    private function calculatePerformanceScore(array $data): float
    {
        $performance = 0;
        $weights = ['24h' => 0.4, '7d' => 0.35, '30d' => 0.25];
        
        foreach ($weights as $period => $weight) {
            $change = $data["price_change_{$period}"] ?? 0;
            // Normalize performance to 0-1 scale
            $normalized = 1 / (1 + exp(-$change * 10)); // Sigmoid normalization
            $performance += $normalized * $weight;
        }
        
        return $performance;
    }
    
    private function calculateLiquidityScore(array $data): float
    {
        $volume = $data['volume'] ?? 0;
        $marketCap = $data['market_cap'] ?? 1;
        
        // Volume to market cap ratio (velocity)
        $velocity = $volume / $marketCap;
        
        // Normalize using log scale (liquidity follows power law)
        return min(1, log(1 + $velocity * 1000) / 10);
    }
    
    private function normalizeScores(array $scores): array
    {
        $total = array_sum(array_column($scores, 'score'));
        
        if ($total > 0) {
            foreach ($scores as $symbol => &$scoreData) {
                $scoreData['score'] = round(($scoreData['score'] / $total) * 100, 2);
            }
        }
        
        // Sort by score descending
        uasort($scores, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        return $scores;
    }
}