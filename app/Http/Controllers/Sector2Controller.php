<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sector;
use Illuminate\Support\Facades\DB;

class Sector2Controller extends Controller
{
    /**
     * Tampilkan halaman dashboard sektor.
     */
    public function index()
    {
        $sortMode = request()->get('sort', 'inflow');

        // Query dengan calculated inflow langsung di SQL
        $query = Sector::select(
            'sector_id',
            'name',
            'market_cap',
            'market_cap_change_24h',
            'volume_24h',
            'top_3_logos',
            'updated_at_api'
        )->addSelect(DB::raw('
            CASE 
                WHEN market_cap_change_24h = -100 THEN market_cap 
                ELSE market_cap - (market_cap / (1 + market_cap_change_24h / 100)) 
            END as inflow_usd
        '));

        // Apply sorting berdasarkan mode
        if ($sortMode === 'percent') {
            $query->orderBy('market_cap_change_24h', 'desc');
        } else {
            $query->orderBy('inflow_usd', 'desc');
        }

        // Paginate dengan 10 item per halaman
        $sectors = $query->paginate(10);

        // Tambahkan parameter sort ke pagination links
        $sectors->appends(['sort' => $sortMode]);

        // Hitung statistik total dari seluruh data
        $totalStats = $this->getTotalStats();

        return view('sector.index', [
            'sectors' => $sectors,
            'sortMode' => $sortMode,
            'totalMarketCap' => $totalStats['totalMarketCap'],
            'averageChange' => $totalStats['averageChange'],
            'topPerformerChange' => $totalStats['topPerformerChange'],
            'totalSectors' => $totalStats['totalSectors']
        ]);
    }

    /**
     * Helper function untuk mendapatkan total stats
     */
    private function getTotalStats()
    {
        $totalSectors = Sector::count();
        $totalMarketCap = Sector::sum('market_cap');
        $averageChange = Sector::avg('market_cap_change_24h');
        
        $topPerformer = Sector::orderBy('market_cap_change_24h', 'desc')->first();
        $topPerformerChange = $topPerformer ? $topPerformer->market_cap_change_24h : 0;

        return [
            'totalMarketCap' => $totalMarketCap,
            'averageChange' => $averageChange,
            'topPerformerChange' => $topPerformerChange,
            'totalSectors' => $totalSectors
        ];
    }

    /**
     * Helper function untuk mendapatkan top 10 sectors (STATIC)
     */
    public static function getTopSectors($limit = 10)
    {
        try {
            $sectors = Sector::select(
                'sector_id',
                'name',
                'market_cap',
                'market_cap_change_24h',
                'volume_24h'
            )->get();

            // Hitung inflow untuk sorting
            $sectors = $sectors->map(function ($item) {
                $changePercent = $item->market_cap_change_24h ?? 0;
                
                if ($changePercent == -100) {
                    $marketCapBefore = 0;
                } else {
                    $marketCapBefore = $item->market_cap / (1 + $changePercent / 100);
                }

                $item->inflow_usd = $item->market_cap - $marketCapBefore;
                return $item;
            });

            // Ambil top 10 berdasarkan inflow
            return $sectors->sortByDesc('market_cap_change_24h')->take($limit)->pluck('name')->toArray();
            
        } catch (\Exception $e) {
            // Return empty array jika ada error
            return [];
        }
    }
}