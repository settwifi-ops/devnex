<?php
// app/Http/Controllers/NavbarController.php
namespace App\Http\Controllers;

use App\Models\AiDecision;
use Illuminate\Http\Request;

class NavbarController extends Controller
{
    public function getAIDecisionsRunningText()
    {
        try {
            $decisions = AiDecision::latestForRunningText(5)->get();
            
            return response()->json([
                'success' => true,
                'data' => $decisions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getNavbarData()
    {
        try {
            $aiDecisions = AiDecision::latestForRunningText(5)->get();
            
            return [
                'success' => true,
                'aiDecisions' => $aiDecisions
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'aiDecisions' => []
            ];
        }
    }
}