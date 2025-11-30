<?php

namespace App\Http\Controllers;

use App\Models\Performance;
use App\Services\PerformanceService;
use Illuminate\Http\Request;

class PerformanceController extends Controller
{
    protected $performanceService;

    public function __construct(PerformanceService $performanceService)
    {
        $this->performanceService = $performanceService;
    }

    public function index(Request $request)
    {
        $query = Performance::query();
        
        // Filters
        if ($request->has('symbol') && $request->symbol != '') {
            $query->where('symbol', 'like', '%' . $request->symbol . '%');
        }
        
        if ($request->has('risk_level') && $request->risk_level != '') {
            $query->where('risk_level', $request->risk_level);
        }
        
        if ($request->has('momentum_phase') && $request->momentum_phase != '') {
            $query->where('momentum_phase', $request->momentum_phase);
        }
        
        // TAMBAHKAN FILTER HIGH HEALTH DAN HIGH TREND DI SINI
        if ($request->has('filter')) {
            switch ($request->filter) {
                case 'high_health':
                    $query->where('health_score', '>', 70);
                    break;
                case 'high_trend':
                    $query->where('trend_strength', '>', 70);
                    break;
            }
        }
        
        // Sorting
        $sort = $request->get('sort', 'rank');
        $direction = $request->get('direction', 'asc');
        
        $validSorts = ['rank', 'symbol', 'performance_since_first', 'trend_strength', 'momentum_phase', 'risk_level', 'hours_since_first', 'health_score'];
        $sort = in_array($sort, $validSorts) ? $sort : 'rank';
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';
        
        $query->orderBy($sort, $direction);
        
        // Pagination
        $perPage = $request->get('per_page', 20);
        $validPerPage = [10, 20, 50, 100];
        $perPage = in_array($perPage, $validPerPage) ? $perPage : 20;
        
        $performances = $query->paginate($perPage)->withQueryString();
        
        // Stats calculation - PERBAIKI DI SINI
        $totalCount = Performance::count();
        
        // High Health: count where health_score > 70
        $highHealth = Performance::where('health_score', '>', 70)->count();
        
        // High Trend: count where trend_strength > 70  
        $highTrend = Performance::where('trend_strength', '>', 70)->count();
        
        // Low Risk: count where risk_level is LOW
        $lowRisk = Performance::where('risk_level', 'LOW')->count();
        
        // Positive Performance: count where performance_since_first >= 0
        $positivePerformance = Performance::where('performance_since_first', '>=', 0)->count();
        
        return view('performance.index', compact(
            'performances', 
            'totalCount', 
            'highHealth',
            'highTrend', 
            'lowRisk', 
            'positivePerformance'
        ));
    }

    public function show($id)
    {
        $performance = Performance::findOrFail($id);
        return view('performance.show', compact('performance'));
    }

    public function refresh()
    {
        $result = $this->performanceService->fetchAndStorePerformanceData();
        
        if ($result['success']) {
            return redirect()->route('performance.index')
                ->with('success', 'Performance data refreshed successfully! ' . $result['count'] . ' records updated.');
        }
        
        return redirect()->route('performance.index')
            ->with('error', 'Failed to refresh data: ' . $result['error']);
    }

    public function cleanup()
    {
        $result = $this->performanceService->cleanupDuplicates();
        
        return redirect()->route('performance.index')
            ->with('success', 'Cleanup completed! ' . $result['deleted'] . ' duplicate records removed.');
    }

    public function apiIndex()
    {
        $performances = $this->performanceService->getPerformanceData();
        
        return response()->json([
            'success' => true,
            'data' => $performances,
            'count' => $performances->count()
        ]);
    }
}