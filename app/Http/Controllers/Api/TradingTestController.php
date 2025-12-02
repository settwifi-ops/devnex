<?php
// app/Http/Controllers/Api/TradingTestController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RealTradingExecutionService;
use App\Models\AiDecision;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TradingTestController extends Controller
{
    private $tradingService;
    
    public function __construct(RealTradingExecutionService $tradingService)
    {
        $this->tradingService = $tradingService;
    }
    
    /**
     * Test single user trade execution
     */
    public function testSingleUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'decision_id' => 'required|exists:ai_decisions,id'
        ]);
        
        $user = User::find($request->user_id);
        $decision = AiDecision::find($request->decision_id);
        
        try {
            $result = $this->tradingService->executeForUser($user, $decision);
            
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            Log::error("Test trade failed: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
    
    /**
     * Test batch trade execution
     */
    public function testBatchTrade(Request $request)
    {
        $request->validate([
            'decision_id' => 'required|exists:ai_decisions,id'
        ]);
        
        $decision = AiDecision::find($request->decision_id);
        
        try {
            $result = $this->tradingService->executeRealTrade($decision);
            
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            Log::error("Test batch trade failed: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get trading statistics
     */
    public function getStats(Request $request)
    {
        $userId = $request->get('user_id');
        
        $stats = $this->tradingService->getTradingStatistics($userId);
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
    
    /**
     * Force refresh user data
     */
    public function forceRefresh(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);
        
        $result = $this->tradingService->forceRefreshUserData($request->user_id);
        
        return response()->json($result);
    }
    
    /**
     * Check pending orders
     */
    public function checkOrders()
    {
        $result = $this->tradingService->checkPendingOrders();
        
        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }
}