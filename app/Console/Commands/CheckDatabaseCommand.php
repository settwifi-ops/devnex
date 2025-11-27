<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Signal;

class CheckDatabaseCommand extends Command
{
    protected $signature = 'check:database {id=1}';
    protected $description = 'Check database table structure and data';

    public function handle()
    {
        $signalId = $this->argument('id');
        
        $this->info("🔍 Checking Database Structure...");
        $this->info("=================================");

        // Check table structure
        $this->info("\n📊 TABLE STRUCTURE:");
        try {
            $columns = DB::select('DESCRIBE signals');
            foreach ($columns as $column) {
                $this->info("   {$column->Field} - {$column->Type} - {$column->Null} - {$column->Default}");
            }
        } catch (\Exception $e) {
            $this->error("❌ Cannot describe table: " . $e->getMessage());
        }

        // Check specific signal
        $this->info("\n📋 SIGNAL DATA (ID: {$signalId}):");
        $signal = Signal::find($signalId);
        
        if ($signal) {
            $this->info("✅ Signal exists in database");
            $this->info("   Symbol: {$signal->symbol}");
            $this->info("   AI Summary: " . ($signal->ai_summary ?? 'NULL'));
            $this->info("   AI Probability: " . ($signal->ai_probability ?? 'NULL'));
            $this->info("   Support Level: " . ($signal->support_level ?? 'NULL'));
            $this->info("   Resistance Level: " . ($signal->resistance_level ?? 'NULL'));
            $this->info("   Last Summary Count: " . ($signal->last_summary_count ?? 'NULL'));
        } else {
            $this->error("❌ Signal not found");
        }

        // Test simple update
        $this->info("\n🧪 TESTING SIMPLE UPDATE:");
        try {
            $testUpdate = $signal->update([
                'ai_summary' => 'Test update',
                'updated_at' => now()
            ]);
            
            if ($testUpdate) {
                $this->info("✅ Simple update: SUCCESS");
                
                // Reset
                $signal->update(['ai_summary' => null]);
                $this->info("✅ Reset: SUCCESS");
            } else {
                $this->error("❌ Simple update: FAILED");
            }
        } catch (\Exception $e) {
            $this->error("❌ Update test exception: " . $e->getMessage());
        }
    }
}