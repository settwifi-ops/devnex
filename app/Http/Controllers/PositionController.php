<?php

namespace App\Http\Controllers;

use App\Models\UserPosition;
use App\Services\TradingExecutionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PositionController extends Controller
{
    private $tradingService;

    public function __construct(TradingExecutionService $tradingService)
    {
        $this->tradingService = $tradingService;
    }

    /**
     * Close single position
     */
    public function close(UserPosition $position, Request $request)
    {
        // Authorization check - user can only close their own positions
        if ($position->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if ($position->status !== 'OPEN') {
            return response()->json([
                'success' => false,
                'message' => 'Position is already closed'
            ], 400);
        }

        $reason = $request->input('reason', 'Manual Close');
        
        $result = $this->tradingService->closePositionManually(
            $position->id, 
            Auth::id(), 
            $reason
        );

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Close all positions for current user
     */
    public function closeAll(Request $request)
    {
        $reason = $request->input('reason', 'Manual Close All');
        
        $result = $this->tradingService->closeAllPositions(Auth::id(), $reason);

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        return back()->with('success', "Closed {$result['closed_count']} positions. Total PNL: \${$result['total_pnl']}");
    }
}