<?php
// app/Services/SignalService.php

namespace App\Services;

use App\Models\Signal;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SignalService
{
    protected $apiUrl;

    public function __construct()
    {
        $this->apiUrl = 'http://127.0.0.1:8001/api/signals';
    }

    public function fetchAndStoreSignals()
    {
        try {
            Log::info('=== START FETCHING SIGNALS ===');
            Log::info('API URL: ' . $this->apiUrl);
            
            $response = Http::timeout(30)->get($this->apiUrl);
            
            Log::info('Response Status: ' . $response->status());
            
            if ($response->successful()) {
                $data = $response->json();
                Log::info('Raw API Response Type: ' . gettype($data));
                
                // Handle different response structures
                $signalsData = $this->extractSignalsData($data);
                
                if (empty($signalsData)) {
                    Log::warning('No signals data found in response');
                    return false;
                }
                
                Log::info('Signals to process: ' . count($signalsData));
                
                foreach ($signalsData as $index => $signalData) {
                    Log::info("Processing signal {$index}: " . ($signalData['symbol'] ?? 'Unknown'));
                    $this->storeOrUpdateSignal($signalData);
                }
                
                // Verifikasi data tersimpan
                $count = Signal::count();
                Log::info("Total signals in database after processing: {$count}");
                
                Log::info('=== FINISHED FETCHING SIGNALS ===');
                return true;
            }
            
            Log::error('API request failed. Status: ' . $response->status());
            Log::error('Response Body: ' . $response->body());
            return false;
            
        } catch (\Exception $e) {
            Log::error('Exception in fetchAndStoreSignals: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    private function extractSignalsData($data)
    {
        // Jika data adalah array numerik (multiple signals)
        if (isset($data[0]) && is_array($data[0]) && isset($data[0]['symbol'])) {
            Log::info('Detected multiple signals array');
            return $data;
        }
        
        // Jika data adalah associative array dengan signals di dalamnya
        if (isset($data['symbol'])) {
            Log::info('Detected single signal');
            return [$data];
        }
        
        // Jika data memiliki root-level array of signals
        foreach ($data as $key => $value) {
            if (is_array($value) && isset($value[0]) && is_array($value[0]) && isset($value[0]['symbol'])) {
                Log::info("Detected signals in key: {$key}");
                return $value;
            }
        }
        
        Log::warning('Could not extract signals data from response');
        return [];
    }

    private function storeOrUpdateSignal($signalData)
    {
        DB::beginTransaction();
        try {
            Log::info('=== STORING SIGNAL ===');
            
            if (!isset($signalData['symbol'])) {
                Log::warning('Missing symbol in signal data: ' . json_encode($signalData));
                DB::rollBack();
                return false;
            }

            // Map field names sesuai dengan response API
            $mappedData = [
                'symbol' => $signalData['symbol'],
                'name' => $signalData['name'] ?? $signalData['symbol'],
                'enhanced_score' => $signalData['enhanced_score'] ?? ($signalData['current_score'] ?? 0),
                'smart_confidence' => $signalData['smart_confidence'] ?? 0,
                'current_price' => $signalData['current_price'] ?? 0,
                'price_change_1h' => $signalData['price_change_1h'] ?? 0,
                'price_change_24h' => $signalData['price_change_24h'] ?? 0,
                'volume_spike_ratio' => $signalData['volume_spike_ratio'] ?? 1,
                'volume_acceleration' => $signalData['volume_acceleration'] ?? 0,
                'rsi_delta' => $signalData['rsi_delta'] ?? 0,
                'momentum_regime' => $signalData['momentum_regime'] ?? 'NEUTRAL',
                'momentum_phase' => $signalData['momentum_phase'] ?? 'ACCUMULATION',
                'health_score' => $signalData['health_score'] ?? 0,
                'trend_strength' => $signalData['trend_strength'] ?? 0,
                'risk_level' => $signalData['risk_level'] ?? 'LOW',
                'appearance_count' => $signalData['appearance_count'] ?? 1,
                'performance_since_first' => $signalData['performance_since_first'] ?? 0,
                'hours_since_first' => $signalData['hours_since_first'] ?? 0,
                'latest_update' => $signalData['latest_update'] ?? 'No update',
                'timestamp' => $signalData['timestamp'] ?? now(),
                'first_detection_time' => $signalData['first_detection_time'] ?? now(),
            ];

            Log::info('Mapped data for ' . $signalData['symbol'] . ': ' . json_encode($mappedData));

            $result = Signal::updateOrCreate(
                ['symbol' => $mappedData['symbol']],
                $mappedData
            );

            Log::info('Successfully stored signal: ' . $signalData['symbol']);
            Log::info('Signal ID: ' . $result->id);
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing signal ' . ($signalData['symbol'] ?? 'unknown') . ': ' . $e->getMessage());
            Log::error('Full signal data: ' . json_encode($signalData));
            return false;
        }
    }
}