<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Signal;

class OIDataService
{
    public function updateOI($symbol)
    {
        $url = "https://fapi.binance.com/futures/data/openInterest?symbol={$symbol}USDT";
        $response = Http::get($url)->json();

        if (!$response) {
            return;
        }

        $oi = $response[count($response)-1]['sumOpenInterest'] ?? null;

        $latest = Signal::where('symbol', $symbol)->latest()->first();

        if ($latest) {
            $oi_prev = $latest->open_interest ?? 0;

            $latest->update([
                'open_interest' => $oi,
                'oi_change' => $oi_prev ? (($oi - $oi_prev) / $oi_prev * 100) : 0,
            ]);
        }
    }
}
