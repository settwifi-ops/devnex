<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Models\Signal;
use App\Models\AiDecision;
use App\Services\BinanceService;
use App\Services\AITradingService;

class SignalScannerService
{
    private $binanceService;
    private $aiTradingService;

    public function __construct(BinanceService $binanceService, AITradingService $aiTradingService)
    {
        $this->binanceService = $binanceService;
        $this->aiTradingService = $aiTradingService;
    }

    /**
     * Scan signals dengan filter score > 60 dan confidence > 60
     */
    public function scanHighConfidenceSignals()
    {
        Log::info('🔍 Scanning for high confidence signals (score > 50, confidence > 60)...');
        
        // Ambil hanya symbol, score, dan confidence saja
        $highConfidenceSignals = Signal::select('symbol', 'name', 'category', 'enhanced_score', 'smart_confidence')
            ->where('enhanced_score', '>', 50)
            ->where('smart_confidence', '>', 60)
            ->orderBy('smart_confidence', 'desc')
            ->orderBy('enhanced_score', 'desc')
            ->limit(10) // Ambil 20 terbaik saja
            ->get();

        Log::info("🎯 Found {$highConfidenceSignals->count()} high confidence signals");

        return $highConfidenceSignals;
    }

    /**
     * Generate AI decisions berdasarkan high confidence signals - UPDATED
     */
    public function generateDecisionsFromSignals()
    {
        $highSignals = $this->scanHighConfidenceSignals();
        
        if ($highSignals->isEmpty()) {
            Log::info('📭 No high confidence signals found for AI decisions');
            return [];
        }

        // Ambil symbols dari high confidence signals
        $symbols = $highSignals->pluck('symbol')->toArray();
        
        Log::info("🎯 Analyzing high confidence symbols: " . implode(', ', $symbols));

        // Gunakan AITradingService yang sudah ada untuk analisa dengan GPT
        $decisions = $this->aiTradingService->generateTradingDecision($symbols);
        
        // Handle return value yang bisa berupa null, object, atau array
        if (is_array($decisions)) {
            // Multiple decisions
            foreach ($decisions as $decision) {
                Log::info("✅ AI Decision created from signals: {$decision->action} {$decision->symbol} with {$decision->confidence}% confidence");
                
                // Tambahkan signal info ke explanation
                $signalInfo = $highSignals->where('symbol', str_replace('USDT', '', $decision->symbol))->first();
                if ($signalInfo) {
                    $newExplanation = "Based on high confidence signal - Score: {$signalInfo->enhanced_score}%, Confidence: {$signalInfo->smart_confidence}%. " . $decision->explanation;
                    $decision->update(['explanation' => $newExplanation]);
                }
            }
            Log::info("📊 Total AI decisions created: " . count($decisions));
            return $decisions;
            
        } elseif (is_object($decisions)) {
            // Single decision (backward compatibility)
            Log::info("✅ AI Decision created from signals: {$decisions->action} {$decisions->symbol} with {$decisions->confidence}% confidence");
            
            // Tambahkan signal info ke explanation
            $signalInfo = $highSignals->where('symbol', str_replace('USDT', '', $decisions->symbol))->first();
            if ($signalInfo) {
                $newExplanation = "Based on high confidence signal - Score: {$signalInfo->enhanced_score}%, Confidence: {$signalInfo->smart_confidence}%. " . $decisions->explanation;
                $decisions->update(['explanation' => $newExplanation]);
            }
            return [$decisions]; // Return sebagai array untuk konsistensi
            
        } else {
            // No decisions
            Log::info("⏭️ No AI decisions generated from signals");
            return [];
        }
    }

    /**
     * Get simple signal data untuk display
     */
    public function getTopSignalsForDisplay($limit = 5)
    {
        return Signal::select('symbol', 'name', 'enhanced_score', 'smart_confidence', 'trend_strength', 'health_score')
            ->where('enhanced_score', '>', 60)
            ->where('smart_confidence', '>', 60)
            ->orderBy('smart_confidence', 'desc')
            ->orderBy('enhanced_score', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($signal) {
                return [
                    'symbol' => $signal->symbol,
                    'name' => $signal->name,
                    'score' => $signal->enhanced_score,
                    'confidence' => $signal->smart_confidence,
                    'trend' => $signal->trend_strength,
                    'health' => $signal->health_score
                ];
            });
    }

    /**
     * Untuk backward compatibility - return single decision
     */
    public function generateSingleDecisionFromSignals()
    {
        $decisions = $this->generateDecisionsFromSignals();
        return !empty($decisions) ? $decisions[0] : null;
    }
}