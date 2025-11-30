<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BinanceService;

class TestBinanceCommand extends Command
{
    protected $signature = 'test:binance {symbol=btc}';
    protected $description = 'Test Binance API connection and data fetching';

    public function handle()
    {
        $symbol = $this->argument('symbol');
        $binanceService = new BinanceService();

        $this->info('🔌 Testing Binance API Connection...');
        
        // Test connection
        if ($binanceService->testConnection()) {
            $this->info('✅ Binance API is accessible');
        } else {
            $this->error('❌ Binance API connection failed');
            return;
        }

        // Test symbol formatting
        $formattedSymbol = $binanceService->formatSymbol($symbol);
        $this->info("📋 Symbol formatting: {$symbol} -> {$formattedSymbol}");

        // Test current price
        $price = $binanceService->getCurrentPrice($symbol);
        if ($price) {
            $this->info("💰 Current price: $" . number_format($price, 2));
        } else {
            $this->error('❌ Failed to get current price');
        }

        // Test candle data
        $this->info("🕯️ Fetching candle data for {$symbol}...");
        $candles = $binanceService->getCandleData($symbol, '1h', 10);
        
        if ($candles) {
            $this->info("✅ Successfully fetched " . count($candles) . " candles");
            
            // Show last 3 candles
            $this->info("\nLast 3 candles:");
            $recentCandles = array_slice($candles, -3);
            
            foreach ($recentCandles as $i => $candle) {
                $this->info(sprintf(
                    "Candle %d: O:%s H:%s L:%s C:%s V:%s",
                    $i + 1,
                    $candle[1], // Open
                    $candle[2], // High
                    $candle[3], // Low
                    $candle[4], // Close
                    $candle[5]  // Volume
                ));
            }
        } else {
            $this->error('❌ Failed to fetch candle data');
        }
    }
}