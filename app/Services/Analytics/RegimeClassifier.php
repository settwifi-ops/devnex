<?php
// app/Services/Analytics/RegimeClassifier.php

namespace App\Services\Analytics;

use Illuminate\Support\Collection;

class RegimeClassifier
{
    private $thresholds;

    public function __construct()
    {
        $this->thresholds = [
            'volatility' => [
                'low' => 0.02,    // < 2%
                'medium' => 0.05,  // 2-5%
                'high' => 0.08     // > 8%
            ],
            'price_change' => [
                'bull' => 0.015,   // > 1.5%
                'bear' => -0.015,  // < -1.5%
            ],
            'volume' => [
                'surge' => 0.25,   // > 25% volume increase
                'spike' => 0.5     // > 50% volume increase
            ]
        ];
    }

    public function classifyAdvanced(array $priceData, array $volumeData, array $technicalIndicators = []): array
    {
        $returns = $this->calculateReturns($priceData);
        $volatility = $this->calculateVolatility($returns);
        $volumeChange = $this->calculateVolumeChange($volumeData);
        $priceTrend = $this->calculatePriceTrend($priceData);
        
        // Technical Analysis Integration
        $rsi = $technicalIndicators['rsi'] ?? null;
        $macd = $technicalIndicators['macd'] ?? null;
        $bollingerPosition = $technicalIndicators['bollinger_position'] ?? null;

        $baseRegime = $this->classifyBaseRegime($volatility, $volumeChange, $priceTrend);
        $enhancedRegime = $this->enhanceWithTechnicalAnalysis($baseRegime, $rsi, $macd, $bollingerPosition);
        
        $confidence = $this->calculateConfidenceScore([
            'volatility' => $volatility,
            'volume_change' => $volumeChange,
            'price_trend' => $priceTrend,
            'rsi' => $rsi,
            'macd' => $macd
        ]);

        return [
            'regime' => $enhancedRegime,
            'confidence' => $confidence,
            'metadata' => [
                'volatility' => $volatility,
                'volume_change' => $volumeChange,
                'price_trend' => $priceTrend,
                'rsi' => $rsi,
                'macd' => $macd,
                'bollinger_position' => $bollingerPosition,
                'returns_std_dev' => $volatility
            ]
        ];
    }

    private function classifyBaseRegime(float $volatility, float $volumeChange, float $priceTrend): string
    {
        // High volatility with volume surge = volatile regime
        if ($volatility > $this->thresholds['volatility']['high'] && 
            abs($volumeChange) > $this->thresholds['volume']['spike']) {
            return 'volatile';
        }

        // Moderate volatility with strong price movement = potential reversal
        if ($volatility > $this->thresholds['volatility']['medium'] && 
            abs($priceTrend) > 0.03) {
            return 'reversal';
        }

        // Standard regime classification
        if ($priceTrend > $this->thresholds['price_change']['bull']) {
            return 'bull';
        } elseif ($priceTrend < $this->thresholds['price_change']['bear']) {
            return 'bear';
        } else {
            return 'neutral';
        }
    }

    private function enhanceWithTechnicalAnalysis(string $baseRegime, ?float $rsi, ?float $macd, ?string $bollingerPosition): string
    {
        // RSI Overbought/Oversold adjustment
        if ($rsi !== null) {
            if ($rsi > 70 && $baseRegime === 'bull') {
                return 'volatile'; // Overbought bull market
            } elseif ($rsi < 30 && $baseRegime === 'bear') {
                return 'reversal'; // Oversold, potential reversal
            }
        }

        // MACD Signal adjustment
        if ($macd !== null) {
            if ($macd > 0 && $baseRegime === 'bear') {
                return 'reversal'; // Bullish MACD in bear market
            } elseif ($macd < 0 && $baseRegime === 'bull') {
                return 'volatile'; // Bearish MACD in bull market
            }
        }

        // Bollinger Bands position
        if ($bollingerPosition === 'upper' && $baseRegime === 'bull') {
            return 'volatile'; // At upper band, overextended
        } elseif ($bollingerPosition === 'lower' && $baseRegime === 'bear') {
            return 'reversal'; // At lower band, potential bounce
        }

        return $baseRegime;
    }

    private function calculateConfidenceScore(array $indicators): float
    {
        $scores = [];

        // Volatility confidence (inverse - high volatility = lower confidence)
        $volatilityConfidence = max(0, 1 - ($indicators['volatility'] * 10));
        $scores[] = $volatilityConfidence * 0.25;

        // Volume change confidence
        $volumeConfidence = 1 - min(1, abs($indicators['volume_change']));
        $scores[] = $volumeConfidence * 0.20;

        // Price trend confidence
        $trendConfidence = min(1, abs($indicators['price_trend']) * 20);
        $scores[] = $trendConfidence * 0.30;

        // RSI confidence (if available)
        if ($indicators['rsi'] !== null) {
            $rsiConfidence = 1 - (abs($indicators['rsi'] - 50) / 50);
            $scores[] = $rsiConfidence * 0.15;
        }

        // MACD confidence (if available)
        if ($indicators['macd'] !== null) {
            $macdConfidence = 1 - min(1, abs($indicators['macd']));
            $scores[] = $macdConfidence * 0.10;
        }

        return min(1, array_sum($scores));
    }

    private function calculateReturns(array $prices): array
    {
        $returns = [];
        for ($i = 1; $i < count($prices); $i++) {
            if ($prices[$i-1] != 0) {
                $returns[] = ($prices[$i] - $prices[$i-1]) / $prices[$i-1];
            }
        }
        return $returns;
    }

    private function calculateVolatility(array $returns): float
    {
        if (count($returns) < 2) return 0;
        
        $mean = array_sum($returns) / count($returns);
        $variance = 0;
        
        foreach ($returns as $return) {
            $variance += pow($return - $mean, 2);
        }
        
        return sqrt($variance / count($returns));
    }

    private function calculateVolumeChange(array $volumes): float
    {
        if (count($volumes) < 2) return 0;
        
        $current = end($volumes);
        $previous = prev($volumes);
        
        return $previous != 0 ? ($current - $previous) / $previous : 0;
    }

    private function calculatePriceTrend(array $prices): float
    {
        if (count($prices) < 2) return 0;
        
        $first = $prices[0];
        $last = end($prices);
        
        return $first != 0 ? ($last - $first) / $first : 0;
    }
}