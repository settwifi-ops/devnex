<?php
// app/Services/DataProviders/CoinGeckoProvider.php

namespace App\Services\DataProviders;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CoinGeckoProvider
{
    private $baseUrl = 'https://api.coingecko.com/api/v3';
    
    public function getTopCryptos($limit = 50, $page = 1)
    {
        return Cache::remember("coingecko_top_{$limit}_page_{$page}", 300, function () use ($limit, $page) {
            try {
                $response = Http::timeout(15)->get("{$this->baseUrl}/coins/markets", [
                    'vs_currency' => 'usd',
                    'order' => 'market_cap_desc',
                    'per_page' => $limit,
                    'page' => $page,
                    'sparkline' => false,
                    'price_change_percentage' => '24h,7d,30d'
                ]);
                
                if ($response->successful()) {
                    return array_map(function($coin) {
                        return [
                            'id' => $coin['id'],
                            'symbol' => $coin['symbol'],
                            'name' => $coin['name'],
                            'current_price' => $coin['current_price'],
                            'market_cap' => $coin['market_cap'],
                            'market_cap_rank' => $coin['market_cap_rank'],
                            'total_volume' => $coin['total_volume'],
                            'high_24h' => $coin['high_24h'],
                            'low_24h' => $coin['low_24h'],
                            'price_change_24h' => $coin['price_change_24h'],
                            'price_change_percentage_24h' => $coin['price_change_percentage_24h'],
                            'price_change_percentage_7d_in_currency' => $coin['price_change_percentage_7d_in_currency'] ?? 0,
                            'price_change_percentage_30d_in_currency' => $coin['price_change_percentage_30d_in_currency'] ?? 0,
                            'last_updated' => $coin['last_updated']
                        ];
                    }, $response->json());
                }
            } catch (\Exception $e) {
                Log::error("CoinGecko API error: " . $e->getMessage());
            }
            
            return [];
        });
    }
    
    public function getGlobalData()
    {
        return Cache::remember('coingecko_global', 600, function () {
            try {
                $response = Http::timeout(10)->get("{$this->baseUrl}/global");
                
                if ($response->successful()) {
                    $data = $response->json();
                    return [
                        'total_market_cap' => $data['data']['total_market_cap']['usd'] ?? 0,
                        'total_volume' => $data['data']['total_volume']['usd'] ?? 0,
                        'market_cap_percentage' => $data['data']['market_cap_percentage'] ?? [],
                        'market_cap_change_percentage_24h_usd' => $data['data']['market_cap_change_percentage_24h_usd'] ?? 0,
                        'active_cryptocurrencies' => $data['data']['active_cryptocurrencies'] ?? 0,
                        'upcoming_icos' => $data['data']['upcoming_icos'] ?? 0,
                        'ongoing_icos' => $data['data']['ongoing_icos'] ?? 0,
                        'ended_icos' => $data['data']['ended_icos'] ?? 0
                    ];
                }
            } catch (\Exception $e) {
                Log::error("CoinGecko Global API error: " . $e->getMessage());
            }
            
            return null;
        });
    }
    
    public function getCoinData($coinId)
    {
        return Cache::remember("coingecko_coin_{$coinId}", 300, function () use ($coinId) {
            try {
                $response = Http::timeout(10)->get("{$this->baseUrl}/coins/{$coinId}", [
                    'localization' => 'false',
                    'tickers' => 'false',
                    'market_data' => 'true',
                    'community_data' => 'false',
                    'developer_data' => 'false',
                    'sparkline' => 'false'
                ]);
                
                return $response->successful() ? $response->json() : null;
            } catch (\Exception $e) {
                Log::error("CoinGecko Coin API error for {$coinId}: " . $e->getMessage());
                return null;
            }
        });
    }
}