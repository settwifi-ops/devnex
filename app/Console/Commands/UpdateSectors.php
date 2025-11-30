<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class UpdateSectors extends Command
{
    protected $signature = 'sectors:update';
    protected $description = 'Ambil data top sektor (kategori) dari CoinGecko dan simpan ke database';

    public function handle()
    {
        $this->info("⏳ Mengambil data sektor dari CoinGecko...");

        $url = "https://api.coingecko.com/api/v3/coins/categories";

        try {
            $response = Http::timeout(15)->get($url);

            if (!$response->successful()) {
                $this->error("❌ Gagal mengambil data dari CoinGecko (HTTP error)");
                return 1;
            }

            $data = $response->json();

            if (empty($data)) {
                $this->error("❌ Data kosong dari API CoinGecko.");
                return 1;
            }

            // 🔹 Sort berdasarkan kenaikan % market cap (descending)
            usort($data, function ($a, $b) {
                return ($b['market_cap_change_24h'] ?? 0) <=> ($a['market_cap_change_24h'] ?? 0);
            });

            // 🔹 Ambil top 25
            $top25 = array_slice($data, 0, 100);

            foreach ($top25 as $sector) {
                DB::table('sectors')->updateOrInsert(
                    ['sector_id' => $sector['id']],
                    [
                        'name' => $sector['name'] ?? '',
                        'market_cap' => $sector['market_cap'] ?? 0,
                        'market_cap_change_24h' => $sector['market_cap_change_24h'] ?? 0,
                        'volume_24h' => $sector['volume_24h'] ?? 0,
                        'top_3_coins' => json_encode($sector['top_3_coins'] ?? []),
                        'top_3_logos' => json_encode($sector['top_3_coins'] ?? []),
                        'updated_at_api' => isset($sector['updated_at'])
                            ? Carbon::parse($sector['updated_at'])->toDateTimeString()
                            : now(),
                        'updated_at' => now(),
                    ]
                );
            }

            $this->info("✅ Data sektor berhasil diperbarui: " . now());
            $this->info("📈 Total kategori disimpan: " . count($top25));

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return 1;
        }
    }
}
