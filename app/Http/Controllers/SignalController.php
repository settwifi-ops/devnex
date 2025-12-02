<?php
// app/Http/Controllers/SignalController.php

namespace App\Http\Controllers;

use App\Models\Signal;
use App\Services\SignalService;
use App\Services\CoinSummaryService;
use Illuminate\Http\Request;
use App\Http\Controllers\Sector2Controller;

class SignalController extends Controller
{
    protected $signalService;

    public function __construct(SignalService $signalService)
    {
        $this->signalService = $signalService;
    }

    public function index(Request $request)
    {
        $filter = $request->get('filter');
        $symbol = $request->get('symbol');
        
        $query = Signal::query()
            ->filter([
                'symbol' => $symbol,
                'risk_level' => $request->get('risk_level'),
                'momentum_regime' => $request->get('momentum_regime'),
            ]);

        // Apply additional filters based on filter parameter
        if ($filter === 'score') {
            $query->where('enhanced_score', '>', 70);
        } elseif ($filter === 'high_confidence') {
            $query->where('smart_confidence', '>=', 80);
        } elseif ($filter === 'low_risk') {
            $query->whereIn('risk_level', ['LOW', 'VERY_LOW']);
        }

        $signals = $query->orderByColumn(
                $request->get('sort', 'first_detection_time'),
                $request->get('direction', 'desc')
            )
            ->paginate(20);

        // Ambil top 10 sectors
        $topSectors = Sector2Controller::getTopSectors(10);

        // Hitung stats untuk cards
        $totalSignals = Signal::count();
        $highConfidence = Signal::where('smart_confidence', '>=', 80)->count();
        $lowRisk = Signal::whereIn('risk_level', ['LOW', 'VERY_LOW'])->count();
        $scoreAbove70 = Signal::where('enhanced_score', '>', 70)->count();

        // Count untuk filtered signals (sesuai dengan filter yang aktif)
        $filteredSignalsCount = [
            'score' => $scoreAbove70,
            'high_confidence' => $highConfidence,
            'low_risk' => $lowRisk,
        ];

        return view('signals.index', compact(
            'signals', 
            'topSectors', 
            'highConfidence', 
            'lowRisk',
            'totalSignals',
            'filteredSignalsCount'
        ));
    }

    public function show($symbol)
    {
        $signal = Signal::where('symbol', $symbol)->firstOrFail();
        return view('signals.show', compact('signal'));
    }

    public function refresh()
    {
        $success = $this->signalService->fetchAndStoreSignals();
        
        if ($success) {
            return redirect()->route('signals.index')
                ->with('success', 'Signals updated successfully!');
        }
        
        return redirect()->route('signals.index')
            ->with('error', 'Failed to update signals.');
    }

    // API endpoint terpisah - HANYA ini yang return JSON
    public function apiIndex(Request $request)
    {
        $filter = $request->get('filter');
        $symbol = $request->get('symbol');
        
        $query = Signal::query()
            ->filter([
                'symbol' => $symbol,
                'risk_level' => $request->get('risk_level'),
                'momentum_regime' => $request->get('momentum_regime'),
            ]);

        // Apply additional filters for API
        if ($filter === 'score') {
            $query->where('enhanced_score', '>', 70);
        } elseif ($filter === 'high_confidence') {
            $query->where('smart_confidence', '>=', 80);
        } elseif ($filter === 'low_risk') {
            $query->whereIn('risk_level', ['LOW', 'VERY_LOW']);
        }

        $signals = $query->orderByColumn(
                $request->get('sort', 'enhanced_score'),
                $request->get('direction', 'desc')
            )
            ->paginate($request->get('per_page', 20));

        return response()->json($signals);
    }
}