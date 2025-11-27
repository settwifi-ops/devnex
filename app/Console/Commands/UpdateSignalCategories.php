<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Signal;

class UpdateSignalCategories extends Command
{
    protected $signature = 'signals:update-categories';
    protected $description = 'Update coin categories from CoinGecko based on signals table symbols';

    public function handle()
    {
        $this->info("🚀 Fetching CoinGecko coin list...");

        // 1️⃣ Ambil daftar semua coin dari CoinGecko
        $coinsList = Http::get('https://api.coingecko.com/api/v3/coins/list')->json();

        if (!$coinsList) {
            $this->error("❌ Gagal mengambil daftar coin dari CoinGecko.");
            return;
        }

        // 2️⃣ Buat mapping SYMBOL → ID
        $symbolToId = collect($coinsList)->mapWithKeys(function ($coin) {
            return [strtoupper($coin['symbol']) => $coin['id']];
        });

        // 3️⃣ Ambil semua data dari tabel signals
        $signals = Signal::all();
        $updated = 0;
        $failed = [];

        // 4️⃣ Pastikan folder cache ada
        $cacheDir = storage_path('app/coingecko_cache');
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        foreach ($signals as $signal) {
            $symbol = strtoupper($signal->symbol);

            if (!isset($symbolToId[$symbol])) {
                $this->warn("⚠️ Symbol {$symbol} tidak ditemukan di CoinGecko");
                $failed[] = $symbol;
                
                // Tetap update ke database sebagai Unknown
                $this->updateCategory($signal, 'Unknown');
                continue;
            }

            $id = $symbolToId[$symbol];
            $url = "https://api.coingecko.com/api/v3/coins/{$id}";

            // 📦 Cek cache dulu
            $cachePath = storage_path("app/coingecko_cache/{$id}.json");
            $data = null;
            $source = '';

            if (file_exists($cachePath)) {
                $data = json_decode(file_get_contents($cachePath), true);
                $source = 'cache';
                $this->line("♻️  {$symbol} diambil dari cache.");
            } else {
                // ⏱️ Ambil dari API dengan jeda agar tidak 429
                sleep(2);

                try {
                    $response = Http::get($url);
                    if ($response->successful()) {
                        $data = $response->json();
                        // Simpan ke cache
                        file_put_contents($cachePath, json_encode($data));
                        $source = 'API';
                        $this->line("💾 Cache disimpan untuk {$symbol}");
                    } else {
                        $this->warn("❌ Gagal ambil data {$symbol} - HTTP {$response->status()}");
                        $failed[] = $symbol;
                        
                        // Tetap update ke database sebagai Unknown
                        $this->updateCategory($signal, 'Unknown');
                        continue;
                    }
                } catch (\Exception $e) {
                    $this->error("Error {$symbol}: " . $e->getMessage());
                    $failed[] = $symbol;
                    
                    // Tetap update ke database sebagai Unknown
                    $this->updateCategory($signal, 'Unknown');
                    continue;
                }
            }

            // 🔍 Ambil kategori dari data (baik dari cache maupun API)
            $categories = $data['categories'] ?? [];
            $category = !empty($categories) ? $categories[0] : 'Unknown';

            // ✅ UPDATE KE DATABASE - baik dari cache maupun API
            if ($this->updateCategory($signal, $category)) {
                $updated++;
                $this->info("✅ {$symbol} → {$category} (from {$source})");
            } else {
                $failed[] = $symbol;
                $this->error("❌ Gagal update database untuk {$symbol}");
            }
        }

        // Simpan simbol gagal ke file log
        if (!empty($failed)) {
            file_put_contents(
                storage_path('logs/failed_coingecko_symbols.txt'),
                implode(PHP_EOL, array_unique($failed))
            );
            $this->warn("⚠️ Ada " . count(array_unique($failed)) . " simbol gagal. Disimpan di storage/logs/failed_coingecko_symbols.txt");
        }

        $this->info("🎯 Selesai! {$updated} kategori coin berhasil di-update (cache aktif).");
    }

    /**
     * Helper function untuk update category ke database
     */
    private function updateCategory(Signal $signal, string $category): bool
    {
        try {
            // Cek jika category sama, skip update untuk efisiensi
            if ($signal->category === $category) {
                $this->line("⏭️  {$signal->symbol} sudah memiliki category: {$category}");
                return true;
            }

            $signal->category = $category;
            return $signal->save();
        } catch (\Exception $e) {
            $this->error("Database error untuk {$signal->symbol}: " . $e->getMessage());
            return false;
        }
    }
}