<?php

namespace App\Services;

use App\Models\Performance;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PerformanceService
{
    protected $apiUrl;

    public function __construct()
    {
        $this->apiUrl = 'http://127.0.0.1:8001/api/performance';
    }

    public function fetchAndStorePerformanceData()
    {
        try {
            $response = Http::timeout(30)->get($this->apiUrl);
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['performers']) && is_array($data['performers'])) {
                    $this->storePerformanceData($data['performers']);
                    return ['success' => true, 'count' => count($data['performers'])];
                }
            }
            
            return ['success' => false, 'error' => 'Failed to fetch data from API'];
            
        } catch (\Exception $e) {
            Log::error('Error fetching performance data: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function storePerformanceData($performers)
    {
        $currentTimestamp = now();
        
        foreach ($performers as $performer) {
            try {
                // UPDATE OR CREATE berdasarkan symbol saja
                Performance::updateOrCreate(
                    [
                        'symbol' => $performer['symbol'] // HANYA symbol sebagai unique key
                    ],
                    [
                        'performance_since_first' => $performer['performance_since_first'],
                        'health_score' => $performer['health_score'],
                        'appearance_count' => $performer['appearance_count'],
                        'trend_strength' => $performer['trend_strength'],
                        'hours_since_first' => $performer['hours_since_first'],
                        'momentum_phase' => $performer['momentum_phase'],
                        'risk_level' => $performer['risk_level'],
                        'last_seen' => $performer['last_seen'],
                        'current_price' => $performer['current_price'],
                        'is_active' => $performer['is_active'],
                        'rank' => $performer['rank'],
                        'data_timestamp' => $currentTimestamp, // Timestamp kapan data diambil
                        'first_detection_time' => $performer['first_detection_time']
                    ]
                );
                
            } catch (\Exception $e) {
                Log::error('Error storing performance data for symbol: ' . $performer['symbol'] . ' - ' . $e->getMessage());
                continue; // Continue dengan symbol berikutnya jika error
            }
        }
    }

    public function getPerformanceData($filters = [])
    {
        $query = Performance::query();
        
        // Filter aktif
        if (isset($filters['active_only']) && $filters['active_only']) {
            $query->where('is_active', true);
        }
        
        // Filter by symbol
        if (isset($filters['symbol']) && $filters['symbol']) {
            $query->where('symbol', 'like', '%' . $filters['symbol'] . '%');
        }
        
        // Filter risk level
        if (isset($filters['risk_level']) && $filters['risk_level']) {
            $query->where('risk_level', $filters['risk_level']);
        }
        
        // Filter momentum phase
        if (isset($filters['momentum_phase']) && $filters['momentum_phase']) {
            $query->where('momentum_phase', $filters['momentum_phase']);
        }
        
        // Order by rank
        $query->orderBy('rank', 'asc');
        
        return $query->get();
    }

    // Method untuk cleanup data duplikat (jika ada)
    public function cleanupDuplicates()
    {
        $duplicates = DB::table('performances')
            ->select('symbol', DB::raw('COUNT(*) as count'))
            ->groupBy('symbol')
            ->having('count', '>', 1)
            ->get();

        $deletedCount = 0;

        foreach ($duplicates as $duplicate) {
            $records = Performance::where('symbol', $duplicate->symbol)
                ->orderBy('updated_at', 'desc')
                ->get();

            // Keep the latest one, delete others
            for ($i = 1; $i < count($records); $i++) {
                $records[$i]->delete();
                $deletedCount++;
            }
        }

        return ['deleted' => $deletedCount];
    }
}