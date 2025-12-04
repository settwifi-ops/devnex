<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\AiDecision;
use App\Models\User;
use App\Services\RealTradingExecutionService; // Ganti ke service yang benar
use Throwable;
use Illuminate\Support\Facades\Log;

class ExecuteTradingDecisionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $decisionId;
    public $userIds; // Tambahkan property untuk user IDs
    public $tries = 3;
    public $timeout = 300; // Perpanjang timeout untuk trading
    public $backoff = [30, 60, 120];
    public $maxExceptions = 3;

    /**
     * Create a new job instance.
     */
    public function __construct($decisionId, $userIds = [])
    {
        $this->decisionId = $decisionId;
        $this->userIds = is_array($userIds) ? $userIds : [];
    }

    /**
     * Execute the job.
     */
    public function handle(RealTradingExecutionService $executionService)
    {
        try {
            Log::info('🎯 Starting ExecuteTradingDecisionJob', [
                'job_id' => $this->job->getJobId(),
                'decision_id' => $this->decisionId,
                'user_ids' => $this->userIds
            ]);

            // 1. Load AI Decision TANPA eager loading yang salah
            $decision = AiDecision::findOrFail($this->decisionId);
            
            Log::info('📊 AI Decision loaded', [
                'decision_id' => $decision->id,
                'symbol' => $decision->symbol,
                'action' => $decision->action,
                'price' => $decision->price,
                'confidence' => $decision->confidence_score
            ]);

            // 2. Validasi decision
            $this->validateDecision($decision);

            // 3. Cek apakah decision sudah dieksekusi
            if ($decision->executed_at) {
                Log::warning('⚠️ Decision already executed', [
                    'decision_id' => $this->decisionId,
                    'executed_at' => $decision->executed_at
                ]);
                return [
                    'success' => false,
                    'message' => 'Decision already executed',
                    'executed_at' => $decision->executed_at
                ];
            }

            // 4. Jika ada user IDs spesifik, execute untuk user tersebut
            if (!empty($this->userIds)) {
                Log::info('👤 Executing for specific users', [
                    'user_count' => count($this->userIds)
                ]);
                
                $results = [];
                foreach ($this->userIds as $userId) {
                    try {
                        $user = User::with(['portfolio', 'binanceAccounts'])->find($userId);
                        
                        if (!$user) {
                            Log::warning('User not found', ['user_id' => $userId]);
                            continue;
                        }
                        
                        $result = $executionService->executeForUser($user, $decision);
                        $results[] = $result;
                        
                        Log::info("Trade executed for user {$userId}", [
                            'success' => $result['success'] ?? false,
                            'order_id' => $result['order_id'] ?? null
                        ]);
                        
                    } catch (\Exception $e) {
                        Log::error("Failed to execute for user {$userId}: " . $e->getMessage());
                        $results[] = [
                            'user_id' => $userId,
                            'success' => false,
                            'error' => $e->getMessage()
                        ];
                    }
                }
                
                // Update decision status
                $decision->update([
                    'executed_at' => now(),
                    'execution_count' => $decision->execution_count + 1
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Executed for specific users',
                    'results' => $results
                ];
            }
            
            // 5. Jika tidak ada user IDs, execute untuk semua eligible users
            Log::info('🚀 Executing real trade for all eligible users', [
                'symbol' => $decision->symbol,
                'action' => $decision->action
            ]);
            
            $result = $executionService->executeRealTrade($decision);
            
            // Update decision status
            if ($result['success'] ?? false) {
                $decision->update([
                    'executed_at' => now(),
                    'execution_count' => $decision->execution_count + 1,
                    'notes' => "Executed for " . ($result['total_users'] ?? 0) . " users"
                ]);
            }
            
            Log::info('✅ Trading decision executed', [
                'decision_id' => $this->decisionId,
                'result' => $result
            ]);
            
            return $result;

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('❌ Decision not found', [
                'decision_id' => $this->decisionId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('❌ Database error in trading job', [
                'decision_id' => $this->decisionId,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('❌ Unexpected error in trading job', [
                'decision_id' => $this->decisionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Validasi data decision
     */
    private function validateDecision(AiDecision $decision): void
    {
        // Validasi field yang diperlukan untuk trading
        $requiredFields = [
            'symbol' => 'Symbol',
            'action' => 'Action',
            'price' => 'Price'
        ];

        foreach ($requiredFields as $field => $label) {
            if (empty($decision->$field)) {
                throw new \InvalidArgumentException("{$label} is required for trading decision");
            }
        }

        // Validasi action
        $validActions = ['BUY', 'SELL', 'buy', 'sell'];
        if (!in_array(strtoupper($decision->action), $validActions)) {
            throw new \InvalidArgumentException("Invalid action: {$decision->action}. Must be BUY or SELL");
        }

        // Validasi price
        if ($decision->price <= 0) {
            throw new \InvalidArgumentException("Price must be greater than 0");
        }

        // Validasi symbol format
        if (!str_contains($decision->symbol, 'USDT')) {
            Log::warning("Symbol may not be correct format: {$decision->symbol}");
        }

        Log::info('✅ Decision validation passed', [
            'symbol' => $decision->symbol,
            'action' => $decision->action,
            'price' => $decision->price
        ]);
    }

    /**
     * Handle job failure
     */
    public function failed(Throwable $exception): void
    {
        try {
            Log::critical('💥 ExecuteTradingDecisionJob failed', [
                'decision_id' => $this->decisionId,
                'user_ids' => $this->userIds,
                'job_id' => $this->job ? $this->job->getJobId() : 'unknown',
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'attempts' => $this->attempts(),
                'failed_at' => now()->toISOString()
            ]);

            // Update decision status jika ada
            $decision = AiDecision::find($this->decisionId);
            if ($decision) {
                $decision->update([
                    'execution_status' => 'FAILED',
                    'error_message' => substr($exception->getMessage(), 0, 500),
                    'last_error_at' => now(),
                    'execution_attempts' => $this->attempts()
                ]);

                Log::info('📝 Decision marked as FAILED', [
                    'decision_id' => $this->decisionId,
                    'status' => 'FAILED'
                ]);
            }

            // TODO: Add notification system here
            // Example: Notification::send($adminUsers, new TradingJobFailed($this->decisionId, $exception));

        } catch (Throwable $e) {
            // Fallback logging jika ada error dalam failed handler
            Log::emergency('🔥 Critical error in failed handler', [
                'original_error' => $exception->getMessage(),
                'handler_error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Determine the time at which the job should timeout
     */
    public function retryUntil()
    {
        return now()->addMinutes(10); // Perpanjang untuk trading
    }

    /**
     * Calculate the number of seconds to wait before retrying the job
     */
    public function backoff()
    {
        return $this->backoff;
    }

    /**
     * Get the display name for the job
     */
    public function displayName(): string
    {
        return "Execute Trading Decision #{$this->decisionId}";
    }

    /**
     * Get the tags for the job
     */
    public function tags(): array
    {
        return [
            'trading',
            'decision:' . $this->decisionId,
            'symbol:' . (AiDecision::find($this->decisionId)->symbol ?? 'unknown')
        ];
    }
}