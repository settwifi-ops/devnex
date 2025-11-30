<?php

namespace App\Services;

class IndicatorService
{
    /**
     * Compute custom indicators from klines.
     * Return array dengan indikator minimal:
     * - volume (1h, 4h, 24h)
     * - long_short_ratio
     * - taker_buy_ratio
     */
    public function computeFromKlines(array $k1h, array $k4h): array
    {
        // Contoh sederhana: hitung volume rata-rata
        $vol1h = array_sum(array_map(fn($k) => $k[5], $k1h)) / count($k1h);
        $vol4h = array_sum(array_map(fn($k) => $k[5], $k4h)) / count($k4h);

        // Dummy ratio (real implement bisa dari data lain)
        $longShortRatio = null;
        $takerBuyRatio = null;

        return [
            'volume' => [
                'vol_1h' => $vol1h,
                'vol_4h' => $vol4h,
            ],
            'long_short_ratio' => $longShortRatio,
            'taker_buy_ratio' => $takerBuyRatio,
        ];
    }
}
