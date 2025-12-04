<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\AiDecision;
use App\Services\TradingExecutionService;
use Throwable;
use Illuminate\Support\Facades\Log;

class ExecuteTradingDecisionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $decisionId;
    public $tries = 3;
    public $timeout = 60;
    public $backoff = [30, 60, 120];
    public $maxExceptions = 3;

    public function __construct($decisionId)
    {
        $this->decisionId = $decisionId;
    }

    public function handle(TradingExecutionService $executionService)
    {
        try {
            // AMBIL DATA AI DECISION TANPA EAGER LOADING
            $decision = AiDecision::findOrFail($this->decisionId);
            
            Log::info('Processing trading decision', [
                'job_id' => $this->job->getJobId(),
                'decision_id' => $this->decisionId,
                'decision_type' => $decision->decision_type,
                'status' => $decision->status
            ]);

            if ($decision->executed) {
                Log::warning('Decision already executed', [
                    'decision_id' => $this->decisionId,
                    'executed_at' => $decision->executed_at
                ]);
                return;
            }

            if ($decision->status === 'cancelled') {
                Log::warning('Decision is cancelled', [
                    'decision_id' => $this->decisionId
                ]);
                return;
            }

            // Validasi data sebelum eksekusi
            $this->validateDecision($decision);

            // Eksekusi trading
            $result = $executionService->executeDecision($decision);
            
            Log::info('Trading decision executed successfully', [
                'decision_id' => $this->decisionId,
                'result' => $result
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Decision not found', [
                'decision_id' => $this->decisionId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error in trading job', [
                'decision_id' => $this->decisionId,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Unexpected error in trading job', [
                'decision_id' => $this->decisionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Validasi data decision sebelum eksekusi
     * SESUAIKAN DENGAN FIELD YANG ADA DI TABEL ai_decisions
     */
    private function validateDecision(AiDecision $decision)
    {
        // SESUAIKAN DENGAN FIELD YANG SEBENARNYA ADA
        // Contoh field yang mungkin ada di AiDecision:
        $requiredFields = [
            'decision_type',
            'confidence_level',
            'signal_strength',
            'symbol', // jika ada field symbol untuk trading pair
            'volume', // jika ada field volume
            'price',  // jika ada field price
        ];

        foreach ($requiredFields as $field) {
            if (empty($decision->$field)) {
                throw new \InvalidArgumentException("Field {$field} is required for decision execution");
            }
        }

        // Validasi tipe decision
        $validDecisionTypes = ['buy', 'sell', 'hold', 'close'];
        if (!in_array(strtolower($decision->decision_type), $validDecisionTypes)) {
            throw new \InvalidArgumentException("Invalid decision type: {$decision->decision_type}");
        }

        // Validasi confidence level
        if ($decision->confidence_level < 0 || $decision->confidence_level > 1) {
            throw new \InvalidArgumentException("Confidence level must be between 0 and 1");
        }

        // Tambahkan validasi lain sesuai kebutuhan
        if (isset($decision->volume) && $decision->volume <= 0) {
            throw new \InvalidArgumentException("Volume must be greater than 0");
        }

        if (isset($decision->price) && $decision->price <= 0) {
            throw new \InvalidArgumentException("Price must be greater than 0");
        }
    }

    /**
     * Handle job failure
     */
    public function failed(Throwable $exception)
    {
        try {
            Log::critical('ExecuteTradingDecisionJob failed', [
                'decision_id' => $this->decisionId,
                'job_id' => $this->job ? $this->job->getJobId() : 'unknown',
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
                'attempts' => $this->attempts(),
                'failed_at' => now()->toISOString()
            ]);

            $decision = AiDecision::find($this->decisionId);
            if ($decision) {
                $decision->update([
                    'status' => 'failed',
                    'error_message' => substr($exception->getMessage(), 0, 255),
                    'executed_at' => now(),
                    'execution_attempts' => $this->attempts()
                ]);

                Log::info('Decision marked as failed', [
                    'decision_id' => $this->decisionId,
                    'status' => 'failed'
                ]);
            }
            
        } catch (Throwable $e) {
            error_log('Critical error in failed handler: ' . $e->getMessage());
            error_log('Original error: ' . $exception->getMessage());
        }
    }

    /**
     * Determine the time at which the job should timeout
     */
    public function retryUntil()
    {
        return now()->addMinutes(5);
    }

    /**
     * Calculate the number of seconds to wait before retrying the job
     */
    public function backoff()
    {
        return $this->backoff;
    }
}