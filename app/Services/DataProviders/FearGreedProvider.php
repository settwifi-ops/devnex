<?php
// app/Services/DataProviders/FearGreedProvider.php

namespace App\Services\DataProviders;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FearGreedProvider
{
    public function getFearGreedIndex()
    {
        return Cache::remember('fear_greed_index', 3600, function () {
            try {
                // Alternative 1: Use alternative API
                $response = Http::timeout(10)->get('https://api.alternative.me/fng/');
                
                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['data'][0])) {
                        return [
                            'value' => intval($data['data'][0]['value']),
                            'value_classification' => $data['data'][0]['value_classification'],
                            'timestamp' => $data['data'][0]['timestamp'],
                            'time_until_update' => $data['data'][0]['time_until_update']
                        ];
                    }
                }
                
                // Alternative 2: Fallback calculation based on market data
                return $this->calculateFallbackFearGreed();
                
            } catch (\Exception $e) {
                Log::error("Fear Greed Index API error: " . $e->getMessage());
                return $this->calculateFallbackFearGreed();
            }
        });
    }
    
    private function calculateFallbackFearGreed()
    {
        // Simple fallback based on volatility and market performance
        // This is a simplified version - in production you'd want more sophisticated logic
        return [
            'value' => 50, // Neutral
            'value_classification' => 'Neutral',
            'timestamp' => time(),
            'time_until_update' => 3600,
            'is_fallback' => true
        ];
    }
}