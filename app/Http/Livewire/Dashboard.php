<?php
namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Signal;
use App\Models\Performance;
use App\Models\AiDecision;
use App\Http\Controllers\Dashboard\MarketDashboardController;
use Carbon\Carbon;

class Dashboard extends Component
{
    public $signals = [];
    public $currentIndex = 0;
    public $currentSignal = null;
    public $regimeDistribution = [];
    public $marketMetrics = [];
    public $highConfidence = 0;
    public $positiveGain = 0;
    public $researchInsights = [];
    public $topSignals = [];
    public $currentTopSignalIndex = 0;

    // NEW - Correlation output
    public $correlationTopPositive = [];
    public $correlationTopNegative = [];

    public function mount()
    {
        $today = Carbon::today();

        // ===========================
        //  LOGIC LAMA - JANGAN DIUBAH
        // ===========================

        // Load signals untuk carousel utama
        $allSignals = Signal::all();

        $this->signals = $allSignals->map(function($signal) {
            return [
                'symbol' => $signal->symbol,
                'summary_text' => $signal->summary ?? 'No summary available',
                'market_structure' => $signal->market_structure ?? null,
                'trend_power' => $signal->trend_power ?? null,
                'momentum_category' => $signal->momentum_category ?? null,
                'liquidity_position' => $signal->liquidity_position ?? null,
                'support_level' => $signal->support_level ?? null,
                'resistance_level' => $signal->resistance_level ?? null,
                'funding_direction' => $signal->funding_direction ?? null,
                'whale_behavior' => $signal->whale_behavior ?? null,
                'ai_probability' => $signal->ai_probability ?? 0,
            ];
        })
        ->filter(fn($s) => $s['ai_probability'] >= 50)
        ->sortByDesc('ai_probability')
        ->values()
        ->toArray();

        $this->currentSignal = $this->signals[0] ?? null;

        // Ambil Market Dashboard Controller Data
        $marketData = MarketDashboardController::getDashboardData();

        $this->regimeDistribution = $marketData['regimeDistribution'];
        $this->marketMetrics = $marketData['marketMetrics'];
        $this->researchInsights = $marketData['researchInsights'];

        // High Confidence Count
        $this->highConfidence = Signal::where('smart_confidence', '>=', 80)->count();

        // Positive Gain Count
        $this->positiveGain = Performance::where('performance_since_first', '>=', 0)->count();

        // ===========================
        // MODIFIKASI: Top signals dari ai_decisions
        // ===========================
        $this->loadTopSignalsFromAiDecisions();

        $this->currentTopSignalIndex = 0;

        // =====================================
        // NEW: MINI CORRELATION CALCULATION
        // =====================================
        $this->computeCorrelation();
    }

    /**
     * Load top signals dari AI Decisions
     */
    private function loadTopSignalsFromAiDecisions()
    {
        $aiDecisions = AiDecision::forDashboard()->get();

        $this->topSignals = $aiDecisions->map(function($decision) {
            return [
                'id' => $decision->id,
                'symbol' => $decision->symbol,
                'action' => $decision->action,
                'confidence' => $decision->confidence,
                'price' => $decision->price,
                'explanation' => $decision->explanation,
                'decision_time' => $decision->decision_time,
                'market_data' => $decision->market_data,
                'url' => '#', // Temporary, bisa diubah nanti
                // Field untuk kompatibilitas
                'ai_probability' => $decision->confidence,
                'summary' => $decision->summary,
                'trend_power' => $decision->trend_power,
                'momentum_category' => $this->getMomentumFromConfidence($decision->confidence),
                'time_ago' => $decision->time_ago,
            ];
        })->toArray();

        // Fallback jika tidak ada AI decisions
        if (empty($this->topSignals)) {
            $this->loadFallbackTopSignals();
        }
    }

    /**
     * Fallback ke signals biasa jika tidak ada AI decisions
     */
    private function loadFallbackTopSignals()
    {
        $this->topSignals = Signal::select('symbol', 'summary', 'ai_probability', 'trend_power', 'momentum_category', 'market_structure')
            ->whereNotNull('summary')
            ->where('summary', '!=', '')
            ->where('ai_probability', '>=', 50)
            ->orderBy('ai_probability', 'desc')
            ->limit(5)
            ->get()
            ->map(function($signal) {
                return [
                    'symbol' => $signal->symbol,
                    'summary' => $signal->summary,
                    'ai_probability' => $signal->ai_probability,
                    'url' => route('signals.show', $signal->symbol),
                    'trend_power' => $signal->trend_power,
                    'momentum_category' => $signal->momentum_category,
                    'market_structure' => $signal->market_structure,
                    'action' => $this->inferActionFromTrend($signal->trend_power),
                    'confidence' => $signal->ai_probability,
                    'price' => null,
                    'explanation' => $signal->summary,
                    'decision_time' => now(),
                    'is_fallback' => true,
                ];
            })
            ->toArray();
    }

    /**
     * Infer action dari trend power (untuk fallback)
     */
    private function inferActionFromTrend($trendPower)
    {
        if (str_contains(strtolower($trendPower ?? ''), 'bull')) {
            return 'BUY';
        } elseif (str_contains(strtolower($trendPower ?? ''), 'bear')) {
            return 'SELL';
        }
        return 'HOLD';
    }

    /**
     * Get momentum category dari confidence level
     */
    private function getMomentumFromConfidence($confidence)
    {
        if ($confidence >= 80) return 'Strong Momentum';
        if ($confidence >= 60) return 'Moderate Momentum';
        return 'Weak Momentum';
    }

    private function computeCorrelation()
    {
        // Tetap menggunakan Signal untuk correlation calculation
        $recentSignals = Signal::select('symbol', 'price_change_24h')
            ->whereNotNull('price_change_24h')
            ->orderBy('ai_probability', 'desc')
            ->limit(6)
            ->get();

        if ($recentSignals->count() < 3) {
            return;
        }

        $symbols = $recentSignals->pluck('symbol')->toArray();
        $priceMap = $recentSignals->pluck('price_change_24h', 'symbol')->toArray();

        $correlations = [];

        foreach ($symbols as $a) {
            foreach ($symbols as $b) {
                if ($a === $b) continue;

                // Simple lightweight pseudo correlation
                $corValue = round(($priceMap[$a] * $priceMap[$b]) / 100, 3);

                $correlations[] = [
                    'pair' => "$a ↔ $b",
                    'value' => $corValue
                ];
            }
        }

        // Top Positive
        $this->correlationTopPositive = collect($correlations)
            ->sortByDesc('value')
            ->take(3)
            ->values()
            ->toArray();

        // Top Negative
        $this->correlationTopNegative = collect($correlations)
            ->filter(fn($c) => $c['value'] < 0)
            ->sortBy('value')
            ->take(3)
            ->values()
            ->toArray();
    }

    public function next()
    {
        if($this->currentIndex < count($this->signals) - 1) {
            $this->currentIndex++;
            $this->currentSignal = $this->signals[$this->currentIndex];
        }
    }

    public function prev()
    {
        if($this->currentIndex > 0) {
            $this->currentIndex--;
            $this->currentSignal = $this->signals[$this->currentIndex];
        }
    }

    public function nextTopSignal()
    {
        if($this->currentTopSignalIndex < count($this->topSignals) - 1) {
            $this->currentTopSignalIndex++;
        } else {
            $this->currentTopSignalIndex = 0;
        }
    }

    public function prevTopSignal()
    {
        if($this->currentTopSignalIndex > 0) {
            $this->currentTopSignalIndex--;
        } else {
            $this->currentTopSignalIndex = count($this->topSignals) - 1;
        }
    }

    public function goToTopSignal($index)
    {
        if($index >= 0 && $index < count($this->topSignals)) {
            $this->currentTopSignalIndex = $index;
        }
    }

    /**
     * Refresh top signals
     */
    public function refreshTopSignals()
    {
        $this->loadTopSignalsFromAiDecisions();
        $this->currentTopSignalIndex = 0;
        $this->dispatchBrowserEvent('top-signals-refreshed');
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}