<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SectorController extends Controller
{
    public function index()
    {
        // Ambil data sektor, urutkan dari kenaikan tertinggi
        $sectors = DB::table('sectors')
            ->orderByDesc('market_cap_change_24h')
            ->get();

        return view('dashboard.sectors', compact('sectors'));
    }
}
