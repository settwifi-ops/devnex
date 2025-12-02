<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoLogCommand extends Command
{
    /**
     * Nama command yang dipanggil di terminal.
     *
     * php artisan auto:log
     */
    protected $signature = 'auto:log';

    /**
     * Deskripsi command.
     */
    protected $description = 'Menulis log otomatis setiap 5 menit untuk testing scheduler';

    /**
     * Logika utama command.
     */
    public function handle()
    {
        $time = now()->format('Y-m-d H:i:s');
        Log::info("✅ AutoLogCommand dijalankan pada: {$time}");

        $this->info("Log berhasil ditulis: {$time}");
    }
}
