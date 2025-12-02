<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AiDecision;
use App\Services\TradingExecutionService;

class TestRealTrading extends Command
{
    protected $signature = 'test:realtrading {symbol=BTCUSDT} {action=BUY} {price=50000}';
    protected $description = 'Test real trading execution with manual AI decision';

    public function handle()
    {
        $symbol = $this->argument('symbol');
        $action = $this->argument('action');
        $price = (float) $this->argument('price');

        $this->info("🎯 Creating test AI decision: {$action} {$symbol} @ \${$price}");

        // Create test decision
        $decision = AiDecision::create([
            'symbol' => $symbol,
            'action' => $action,
            'price' => $price,
            'confidence' => 85,
            'timeframe' => '15m',
            'explanation' => 'Test manual execution via command',
            'executed' => false,
        ]);

        $this->info("✅ AI Decision created: #{$decision->id}");

        // Execute
        $service = app(TradingExecutionService::class);
        $result = $service->executeDecision($decision);

        $this->info("🚀 Execution completed!");
        $this->info("📊 Check storage/logs/laravel.log for details");

        return Command::SUCCESS;
    }
}