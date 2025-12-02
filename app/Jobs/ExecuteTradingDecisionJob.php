<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\AiDecision;
use App\Services\TradingExecutionService;

class ExecuteTradingDecisionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $decisionId;
    public $tries = 3;
    public $timeout = 60;

    public function __construct($decisionId)
    {
        $this->decisionId = $decisionId;
    }

    public function handle(TradingExecutionService $executionService)
    {
        $decision = AiDecision::find($this->decisionId);
        
        if ($decision && !$decision->executed) {
            $executionService->executeDecision($decision);
        }
    }

    public function failed(\Exception $exception)
    {
        \Log::error("ExecuteTradingDecisionJob failed: " . $exception->getMessage());
    }
}