<?php
// app/Services/CurrencyService.php
namespace App\Services;

class CurrencyService
{
    public function convertToUSD(int $idrAmount): float
    {
        $rate = config('services.subscription.exchange_rate_usd');
        return round($idrAmount / $rate, 2);
    }

    public function getDisplayPrice(int $idrAmount, string $currency = 'USD'): string
    {
        if ($currency === 'IDR') {
            return 'Rp ' . number_format($idrAmount, 0, ',', '.');
        }
        
        $usdAmount = $this->convertToUSD($idrAmount);
        return '$' . number_format($usdAmount, 2);
    }

    public function detectDisplayCurrency(): string
    {
        return request()->input('currency', 'USD');
    }

    public function getExchangeRate(): int
    {
        return config('services.subscription.exchange_rate_usd');
    }
}