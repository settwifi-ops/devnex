<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Signal;

class FundingDataService
{
    public function updateFunding($symbol)
    {
        $url = "https://fapi.binance.com/fapi/v1/premiumIndex?symbol={$symbol}USDT";
        $response = Http::get($url)->json();

        if (!$response) return;

        $funding = $response['lastFundingRate'] ?? null;

        $latest = Signal::where('symbol', $symbol)->latest()->first();

        if ($latest) {
            $latest->update([
                'funding_rate' => $funding,
            ]);
        }
    }
}
