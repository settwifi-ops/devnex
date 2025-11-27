<?php
// app/Services/DataProviders/BinanceDataProvider.php

namespace App\Services\DataProviders;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BinanceDataProvider
{
    private $baseUrl = 'https://api.binance.com/api/v3';
    
    public function getSymbolData($symbol = 'BTCUSDT')
    {
        return Cache::remember("binance_{$symbol}_ticker", 60, function () use ($symbol) {
            try {
                $response = Http::timeout(10)->get("{$this->baseUrl}/ticker/24hr", [
                    'symbol' => $symbol
                ]);
                
                if ($response->successful()) {
                    $data = $response->json();
                    return [
                        'symbol' => $data['symbol'],
                        'price' => floatval($data['lastPrice']),
                        'volume' => floatval($data['volume']),
                        'price_change_percent' => floatval($data['priceChangePercent']),
                        'high' => floatval($data['highPrice']),
                        'low' => floatval($data['lowPrice']),
                        'quote_volume' => floatval($data['quoteVolume'])
                    ];
                }
            } catch (\Exception $e) {
                Log::error("Binance API error for {$symbol}: " . $e->getMessage());
            }
            
            return null;
        });
    }
    
    public function getMultipleSymbols($symbols = ['BTCUSDT', 'ETHUSDT', 'ADAUSDT', 'DOTUSDT'])
    {
        $allData = [];
        foreach ($symbols as $symbol) {
            if ($data = $this->getSymbolData($symbol)) {
                $allData[$symbol] = $data;
            }
            usleep(100000); // Rate limiting 100ms
        }
        return $allData;
    }
    
    public function getKlineData($symbol = 'BTCUSDT', $interval = '1h', $limit = 24)
    {
        return Cache::remember("binance_kline_{$symbol}_{$interval}_{$limit}", 120, function () use ($symbol, $interval, $limit) {
            try {
                $response = Http::timeout(15)->get("{$this->baseUrl}/klines", [
                    'symbol' => $symbol,
                    'interval' => $interval,
                    'limit' => $limit
                ]);
                
                if ($response->successful()) {
                    $klineData = $response->json();
                    return array_map(function($kline) {
                        return [
                            'timestamp' => $kline[0],
                            'open' => floatval($kline[1]),
                            'high' => floatval($kline[2]),
                            'low' => floatval($kline[3]),
                            'close' => floatval($kline[4]),
                            'volume' => floatval($kline[5]),
                            'close_time' => $kline[6],
                            'quote_volume' => floatval($kline[7])
                        ];
                    }, $klineData);
                }
            } catch (\Exception $e) {
                Log::error("Binance Kline API error for {$symbol}: " . $e->getMessage());
            }
            
            return [];
        });
    }
    
    public function getExchangeInfo()
    {
        return Cache::remember('binance_exchange_info', 3600, function () {
            try {
                $response = Http::timeout(10)->get("{$this->baseUrl}/exchangeInfo");
                return $response->successful() ? $response->json() : [];
            } catch (\Exception $e) {
                Log::error("Binance Exchange Info error: " . $e->getMessage());
                return [];
            }
        });
    }
}