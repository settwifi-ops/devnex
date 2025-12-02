<?php

namespace App\Http\Controllers;

use App\Models\AiDecision;
use Illuminate\Http\Request;

class AiDecisionController extends Controller
{
    public function index()
    {
        $decisions = AiDecision::with('signal')
            ->where('confidence', '>=', 50)
            ->orderBy('confidence', 'desc')
            ->orderBy('decision_time', 'desc')
            ->paginate(20);

        return view('ai-decisions.index', compact('decisions'));
    }

    public function show($id)
    {
        $decision = AiDecision::findOrFail($id);
        $marketData = $decision->market_data ?: [];

        return view('ai-decisions.show', compact('decision', 'marketData'));
    }

    public function execute($id)
    {
        $decision = AiDecision::findOrFail($id);
        
        // Logic untuk execute decision
        $decision->update(['executed' => true]);
        
        return response()->json([
            'success' => true,
            'message' => 'AI Decision executed successfully',
            'decision' => $decision
        ]);
    }

    public function analysis($id)
    {
        $decision = AiDecision::findOrFail($id);
        $marketData = $decision->market_data ?: [];
        
        return response()->json([
            'decision' => $decision,
            'market_data' => $marketData,
            'analysis' => $this->generateAnalysis($decision, $marketData)
        ]);
    }

    private function generateAnalysis($decision, $marketData)
    {
        // Generate analysis based on decision and market data
        return [
            'strengths' => $this->getStrengths($decision, $marketData),
            'risks' => $this->getRisks($decision, $marketData),
            'recommendation' => $this->getRecommendation($decision, $marketData)
        ];
    }

    private function getStrengths($decision, $marketData)
    {
        $strengths = [];
        
        if ($decision->confidence >= 80) {
            $strengths[] = 'High confidence level';
        }
        
        if (isset($marketData['rsi']) && $marketData['rsi'] < 30 && $decision->action === 'BUY') {
            $strengths[] = 'Oversold condition with RSI: ' . $marketData['rsi'];
        }
        
        if (isset($marketData['volume_change_24h']) && $marketData['volume_change_24h'] > 20) {
            $strengths[] = 'High volume activity';
        }
        
        return $strengths;
    }

    private function getRisks($decision, $marketData)
    {
        $risks = [];
        
        if ($decision->confidence < 70) {
            $risks[] = 'Moderate confidence level';
        }
        
        if (isset($marketData['volatility']) && $marketData['volatility'] > 5) {
            $risks[] = 'High market volatility';
        }
        
        return $risks;
    }

    private function getRecommendation($decision, $marketData)
    {
        if ($decision->confidence >= 80) {
            return 'Strong recommendation to ' . $decision->action;
        } elseif ($decision->confidence >= 60) {
            return 'Moderate recommendation to ' . $decision->action;
        } else {
            return 'Consider alternative positions';
        }
    }
}