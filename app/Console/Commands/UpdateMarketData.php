<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Signal;
use App\Services\SignalSummaryService;

class UpdateMarketData extends Command
{
    protected $signature = 'market:update';
    protected $description = 'Update Open Interest, Funding Rate, and Summary from Binance Futures';

    public function handle()
    {
        $this->info("🔄 Updating from Binance Futures...");

        // Ambil seluruh coin dari tabel signals
        $signals = Signal::all();

        foreach ($signals as $signal) {
            try {
                $pair = strtoupper($signal->symbol) . "USDT";

                // =============================
                // 1) FETCH OPEN INTEREST
                // =============================
                $oiResponse = Http::get(
                    "https://fapi.binance.com/futures/data/openInterestHist",
                    [
                        "symbol" => $pair,
                        "period" => "5m",
                        "limit" => 1
                    ]
                );

                $openInterest = null;
                $oiChange     = 0;

                if ($oiResponse->ok() && isset($oiResponse[0])) {
                    $oiData       = $oiResponse[0];
                    $openInterest = $oiData['sumOpenInterest'] ?? null;
                    $oiChange     = $oiData['sumOpenInterestChange'] ?? 0;
                } else {
                    $this->warn("⚠️ OI unavailable: $pair");
                }

                // =============================
                // 2) FETCH FUNDING RATE
                // =============================
                $fundingResponse = Http::get(
                    "https://fapi.binance.com/fapi/v1/fundingRate",
                    [
                        "symbol" => $pair,
                        "limit" => 1
                    ]
                );

                $fundingRate = 0;

                if ($fundingResponse->ok() && isset($fundingResponse[0]['fundingRate'])) {
                    $fundingRate = (float) $fundingResponse[0]['fundingRate'];
                } else {
                    $this->warn("⚠️ Funding unavailable: $pair");
                }


                // =============================
                // 4) UPDATE DATABASE
                // =============================
                $signal->open_interest = $openInterest;
                $signal->oi_change     = $oiChange;
                $signal->funding_rate  = $fundingRate;
                $signal->save();



                $this->info("✔ Updated: {$signal->symbol}");

            } catch (\Exception $e) {
                $this->error("❌ Error on {$signal->symbol}: " . $e->getMessage());
            }
        }

        $this->info("✅ Update Completed!");
    }

}
