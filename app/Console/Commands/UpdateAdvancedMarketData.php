<?php
// app/Console/Commands/UpdateAdvancedMarketData.php (UPDATED)

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DataProviders\BinanceDataProvider;
use App\Services\DataProviders\CoinGeckoProvider;
use App\Services\DataProviders\FearGreedProvider;
use App\Services\Analytics\RegimeClassifier;
use App\Services\Analytics\DominanceCalculator;
use App\Services\Analytics\InsightGenerator;
use App\Models\MarketRegime;
use App\Models\RegimeSummary;
use App\Models\DominanceHistory;
use Carbon\Carbon;

class UpdateAdvancedMarketData extends Command
{
    protected $signature = 'market:advanced-update';
    protected $description = 'Update advanced market regime and dominance data with ML features';

    public function handle()
    {
        $this->info('Starting advanced market data update...');
        
        try {
            // Initialize services
            $binance = new BinanceDataProvider();
            $coingecko = new CoinGeckoProvider();
            $fearGreed = new FearGreedProvider();
            $classifier = new RegimeClassifier();
            $dominanceCalculator = new DominanceCalculator();
            $insightGenerator = new InsightGenerator();

            // Step 1: Fetch comprehensive market data
            $this->info('Fetching market data from CoinGecko...');
            $topCryptos = $coingecko->getTopCryptos(30); // Reduced to 30 for testing
            
            if (empty($topCryptos)) {
                $this->error('Failed to fetch data from CoinGecko');
                return 1;
            }

            $this->info('Processing ' . count($topCryptos) . ' cryptocurrencies...');
            
            $binanceSymbols = array_map(function($crypto) {
                return strtoupper($crypto['symbol']) . 'USDT';
            }, $topCryptos);
            
            $this->info('Fetching Binance data...');
            $binanceData = $binance->getMultipleSymbols($binanceSymbols);
            $klineData = $this->fetchKlineData($binance, $binanceSymbols);

            // Step 2: Process each symbol
            $this->info('Processing market regimes...');
            $processedData = [];
            $marketDataForDominance = [];
            
            $progressBar = $this->output->createProgressBar(count($topCryptos));
            $progressBar->start();

            foreach ($topCryptos as $crypto) {
                $symbol = strtoupper($crypto['symbol']) . 'USDT';
                
                if (isset($klineData[$symbol]) && !empty($klineData[$symbol]) && 
                    isset($binanceData[$symbol]) && !empty($binanceData[$symbol])) {
                    
                    $technicalIndicators = $this->calculateTechnicalIndicators($klineData[$symbol]);
                    
                    $regimeAnalysis = $classifier->classifyAdvanced(
                        array_column($klineData[$symbol], 'close'),
                        array_column($klineData[$symbol], 'volume'),
                        $technicalIndicators
                    );
                    
                    $processedData[] = [
                        'timestamp' => now(),
                        'date' => Carbon::today(),
                        'symbol' => $symbol,
                        'price' => $binanceData[$symbol]['price'],
                        'volume' => $binanceData[$symbol]['volume'],
                        'market_cap' => $crypto['market_cap'],
                        'volatility_24h' => $regimeAnalysis['metadata']['volatility'],
                        'rsi_14' => $technicalIndicators['rsi'] ?? null,
                        'macd' => $technicalIndicators['macd'] ?? null,
                        'regime' => $regimeAnalysis['regime'],
                        'regime_confidence' => $regimeAnalysis['confidence'],
                        'regime_metadata' => json_encode($regimeAnalysis['metadata']),
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                    
                    $marketDataForDominance[$symbol] = [
                        'market_cap' => $crypto['market_cap'],
                        'volume' => $binanceData[$symbol]['volume'],
                        'price_change_24h' => $crypto['price_change_percentage_24h'] ?? 0,
                        'price_change_7d' => $crypto['price_change_percentage_7d_in_currency'] ?? 0,
                        'price_change_30d' => $crypto['price_change_percentage_30d_in_currency'] ?? 0,
                        'sentiment_score' => $this->calculateSentimentFromRegime($regimeAnalysis['regime']),
                    ];
                } else {
                    $this->warn("Skipping {$symbol} - insufficient data");
                }
                
                $progressBar->advance();
                usleep(50000); // 50ms delay between symbols
            }
            
            $progressBar->finish();
            $this->newLine();

            if (empty($processedData)) {
                $this->error('No data processed successfully');
                return 1;
            }

            // Step 3: Calculate dominance scores
            $this->info('Calculating dominance scores...');
            $dominanceScores = $dominanceCalculator->calculateAdvancedDominance($marketDataForDominance);
            
            // Update processed data with dominance scores
            foreach ($processedData as &$data) {
                $symbol = $data['symbol'];
                $data['dominance_score'] = $dominanceScores[$symbol]['score'] ?? 0;
            }

            // Step 4: Save to database
            $this->info('Saving to database...');
            
            // Clear existing data for today
            MarketRegime::where('date', Carbon::today())->delete();
            
            // Insert in chunks
            foreach (array_chunk($processedData, 10) as $chunk) {
                MarketRegime::insert($chunk);
            }
            
            // Step 5: Generate market summary
            $this->info('Generating market summary...');
            $this->generateMarketSummary($processedData, $dominanceScores);
            
            // Step 6: Generate insights and alerts
            $this->info('Generating insights...');
            $insightGenerator->generateAndStoreInsights($processedData, $dominanceScores);

            $this->info('Advanced market data update completed successfully!');
            $this->info('Processed: ' . count($processedData) . ' symbols');
            
        } catch (\Exception $e) {
            $this->error('Error updating market data: ' . $e->getMessage());
            $this->error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            \Log::error('Market data update failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
        
        return 0;
    }
    
    private function calculateSentimentFromRegime(string $regime): float
    {
        $sentimentMap = [
            'bull' => 0.8,
            'neutral' => 0.5,
            'volatile' => 0.4,
            'reversal' => 0.6,
            'bear' => 0.2
        ];
        
        return $sentimentMap[$regime] ?? 0.5;
    }
    
    // ... (keep all the other private methods from previous version)
    private function fetchKlineData($binance, $symbols): array
    {
        $this->info('Fetching Kline data for ' . count($symbols) . ' symbols...');
        $klineData = [];
        $progressBar = $this->output->createProgressBar(count($symbols));
        
        foreach ($symbols as $symbol) {
            try {
                $klineData[$symbol] = $binance->getKlineData($symbol, '1h', 24);
                $progressBar->advance();
                usleep(200000); // 200ms delay between API calls
            } catch (\Exception $e) {
                $this->warn("Failed to fetch kline data for {$symbol}: " . $e->getMessage());
                $klineData[$symbol] = [];
            }
        }
        
        $progressBar->finish();
        $this->newLine();
        return $klineData;
    }
    
    private function calculateTechnicalIndicators(array $klineData): array
    {
        if (count($klineData) < 20) {
            return ['rsi' => null, 'macd' => null, 'bollinger_position' => null];
        }
        
        $closes = array_column($klineData, 'close');
        
        return [
            'rsi' => $this->calculateRSI($closes),
            'macd' => $this->calculateMACD($closes),
            'bollinger_position' => $this->calculateBollingerPosition($closes)
        ];
    }
    
    private function calculateRSI(array $prices, $period = 14): float
    {
        if (count($prices) < $period + 1) return 50;
        
        $changes = [];
        for ($i = 1; $i < count($prices); $i++) {
            $changes[] = $prices[$i] - $prices[$i-1];
        }
        
        $gains = $losses = 0;
        $periodChanges = array_slice($changes, -$period);
        
        foreach ($periodChanges as $change) {
            if ($change > 0) {
                $gains += $change;
            } else {
                $losses += abs($change);
            }
        }
        
        $avgGain = $gains / $period;
        $avgLoss = $losses / $period;
        
        if ($avgLoss == 0) return 100;
        
        $rs = $avgGain / $avgLoss;
        return 100 - (100 / (1 + $rs));
    }
    
    private function calculateMACD(array $prices): float
    {
        if (count($prices) < 26) return 0;
        
        $ema12 = $this->calculateEMA($prices, 12);
        $ema26 = $this->calculateEMA($prices, 26);
        
        return $ema12 - $ema26;
    }
    
    private function calculateEMA(array $prices, $period): float
    {
        $multiplier = 2 / ($period + 1);
        $ema = $prices[0];
        
        for ($i = 1; $i < count($prices); $i++) {
            $ema = ($prices[$i] - $ema) * $multiplier + $ema;
        }
        
        return $ema;
    }
    
    private function calculateBollingerPosition(array $prices): string
    {
        if (count($prices) < 20) return 'middle';
        
        $currentPrice = end($prices);
        $sma = array_sum($prices) / count($prices);
        $stdDev = $this->calculateStandardDeviation($prices);
        
        $upperBand = $sma + ($stdDev * 2);
        $lowerBand = $sma - ($stdDev * 2);
        
        if ($currentPrice > $upperBand) return 'upper';
        if ($currentPrice < $lowerBand) return 'lower';
        return 'middle';
    }
    
    private function calculateStandardDeviation(array $numbers): float
    {
        $n = count($numbers);
        if ($n <= 1) return 0;
        
        $mean = array_sum($numbers) / $n;
        $sumSquares = 0;
        
        foreach ($numbers as $number) {
            $sumSquares += pow($number - $mean, 2);
        }
        
        return sqrt($sumSquares / ($n - 1));
    }
    
    private function generateMarketSummary(array $processedData, array $dominanceScores)
    {
        $regimeCounts = array_count_values(array_column($processedData, 'regime'));
        $total = count($processedData);
        
        $summary = [
            'date' => Carbon::today(),
            'total_symbols' => $total,
            'regime_distribution' => json_encode($regimeCounts),
            'market_health_score' => $this->calculateMarketHealth($processedData),
            'volatility_index' => $this->calculateVolatilityIndex($processedData),
            'market_sentiment' => $this->determineMarketSentiment($processedData),
            'sentiment_score' => $this->calculateSentimentScore($processedData),
            'trend_strength' => $this->calculateTrendStrength($processedData),
            'top_dominance' => json_encode(array_slice($dominanceScores, 0, 5)),
            'created_at' => now(),
            'updated_at' => now()
        ];
        
        // Clear existing summary for today
        RegimeSummary::where('date', Carbon::today())->delete();
        RegimeSummary::create($summary);
    }
    
    private function calculateMarketHealth(array $data): float
    {
        $bullCount = count(array_filter($data, fn($item) => $item['regime'] === 'bull'));
        $volatileCount = count(array_filter($data, fn($item) => $item['regime'] === 'volatile'));
        $bearCount = count(array_filter($data, fn($item) => $item['regime'] === 'bear'));
        
        $total = count($data);
        $bullRatio = $bullCount / $total;
        $volatileRatio = $volatileCount / $total;
        $bearRatio = $bearCount / $total;
        
        // Health score: positive for bull, negative for bear, reduced by volatility
        $health = ($bullRatio - $bearRatio - $volatileRatio * 0.5) * 100;
        return max(0, min(100, $health + 50)); // Normalize to 0-100
    }
    
    private function calculateVolatilityIndex(array $data): float
    {
        $avgVolatility = array_sum(array_column($data, 'volatility_24h')) / count($data);
        return min(100, $avgVolatility * 1000);
    }
    
    private function determineMarketSentiment(array $data): string
    {
        $bullCount = count(array_filter($data, fn($item) => $item['regime'] === 'bull'));
        $bearCount = count(array_filter($data, fn($item) => $item['regime'] === 'bear'));
        $total = count($data);
        
        $bullRatio = $bullCount / $total;
        $bearRatio = $bearCount / $total;
        
        if ($bullRatio > 0.6) return 'extremely_bullish';
        if ($bullRatio > 0.55) return 'bullish';
        if ($bearRatio > 0.6) return 'extremely_bearish';
        if ($bearRatio > 0.55) return 'bearish';
        return 'neutral';
    }
    
    private function calculateSentimentScore(array $data): float
    {
        $sentimentMap = [
            'extremely_bullish' => 90,
            'bullish' => 70,
            'neutral' => 50,
            'bearish' => 30,
            'extremely_bearish' => 10
        ];
        
        $sentiment = $this->determineMarketSentiment($data);
        return $sentimentMap[$sentiment] ?? 50;
    }
    
    private function calculateTrendStrength(array $data): float
    {
        $avgConfidence = array_sum(array_column($data, 'regime_confidence')) / count($data);
        return $avgConfidence * 100;
    }
}