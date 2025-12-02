<?php
// App\Jobs\CleanFilledOrdersJob.php
namespace App\Jobs;

use App\Models\PendingOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CleanFilledOrdersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        // Hapus orders yang sudah filled lebih dari 1 jam yang lalu
        $deleted = PendingOrder::where('status', 'FILLED')
            ->where('updated_at', '<', now()->subHour())
            ->delete();
            
        Log::info("🧹 Cleaned {$deleted} filled orders from database");
    }
}