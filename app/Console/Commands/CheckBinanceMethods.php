<?php

// app/Console/Commands/CheckBinanceMethods.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BinanceAccountService;

class CheckBinanceMethods extends Command
{
    protected $signature = 'binance:methods {user_id}';
    protected $description = 'Check available methods in Binance library';

    public function handle(BinanceAccountService $service)
    {
        $userId = $this->argument('user_id');
        
        $this->info("Checking available methods for user {$userId}...");
        
        $result = $service->checkAvailableMethods($userId);
        
        if ($result['success']) {
            $this->info("✅ Total methods: " . $result['total_methods']);
            $this->info("\n🔧 Relevant methods for trading:");
            
            foreach ($result['relevant_methods'] as $method) {
                $this->line("  • {$method}");
            }
            
            // Check important methods
            $importantMethods = [
                'futuresOrder',
                'futuresCancel',
                'futuresAccount',
                'price',
                'time',
                'balances'
            ];
            
            $this->info("\n🔍 Checking important methods:");
            
            foreach ($importantMethods as $method) {
                $api = $service->getBinanceInstance($userId);
                $exists = method_exists($api, $method);
                $status = $exists ? '✅' : '❌';
                $this->line("  {$status} {$method}");
            }
            
        } else {
            $this->error("❌ Failed: " . $result['message']);
        }
    }
}