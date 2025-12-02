<?php
// Update app/Console/Commands/DebugBinanceAccount.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BinanceAccountService;
use App\Models\UserBinanceAccount;

class DebugBinanceAccount extends Command
{
    protected $signature = 'debug:binance {user_id}';
    protected $description = 'Debug Binance account for user';

    public function handle(BinanceAccountService $service)
    {
        $userId = $this->argument('user_id');
        
        $this->info("Debugging Binance account for user {$userId}...");
        
        // 1. Check database data
        $account = UserBinanceAccount::where('user_id', $userId)->first();
        
        if (!$account) {
            $this->error("❌ No Binance account found for user {$userId}");
            return;
        }
        
        $this->info("✅ Account found:");
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $account->id],
                ['Label', $account->label],
                ['Testnet', $account->is_testnet ? 'Yes' : 'No'],
                ['Active', $account->is_active ? 'Yes' : 'No'],
                ['Verified', $account->verification_status],
                ['Last Verified', $account->last_verified?->format('Y-m-d H:i:s') ?? 'Never'],
                ['Encrypted Key Length', strlen($account->api_key_encrypted)],
                ['Encrypted Secret Length', strlen($account->api_secret_encrypted)],
                ['Is Valid', $account->is_valid ? 'Yes' : 'No'],
                ['Status Label', $account->status_label],
            ]
        );
        
        // 2. Test decrypt
        $this->info("\n🔐 Testing decryption...");
        $testResult = $service->testDatabaseApiKeys($userId);
        
        if ($testResult['success']) {
            $this->info("✅ Decryption successful:");
            $this->table(
                ['Field', 'Value'],
                [
                    ['API Key (first 10)', $testResult['data']['api_key_first_10']],
                    ['API Secret (first 10)', $testResult['data']['api_secret_first_10']],
                    ['Key Length', $testResult['data']['key_length']],
                    ['Secret Length', $testResult['data']['secret_length']],
                    ['Encrypted Key Length', $testResult['data']['encrypted_key_length']],
                    ['Encrypted Secret Length', $testResult['data']['encrypted_secret_length']],
                ]
            );
        } else {
            $this->error("❌ Decryption failed: " . $testResult['message']);
            if (isset($testResult['error_details'])) {
                $this->warn("Error details: " . $testResult['error_details']);
            }
        }
        
        // 3. Test connection via model (direct test)
        $this->info("\n📡 Testing Binance connection via model...");
        try {
            $modelTestResult = $account->testConnection();
            
            if ($modelTestResult['success']) {
                $this->info("✅ Model Connection Test:");
                $this->table(
                    ['Field', 'Value'],
                    [
                        ['Success', 'Yes'],
                        ['Testnet', $modelTestResult['testnet'] ? 'Yes' : 'No'],
                        ['BTC Price', '$' . number_format($modelTestResult['btc_price'], 2)],
                        ['Server Time', $modelTestResult['server_time']],
                        ['Message', $modelTestResult['message']],
                    ]
                );
            } else {
                $this->error("❌ Model Connection failed: " . $modelTestResult['message']);
            }
        } catch (\Exception $e) {
            $this->error("❌ Model Connection test error: " . $e->getMessage());
        }
        
        // 4. Test connection via service
        $this->info("\n📡 Testing Binance connection via service...");
        try {
            $connectionResult = $service->testConnection($userId);
            
            if ($connectionResult['success']) {
                $this->info("✅ Service Connection Test:");
                $this->table(
                    ['Field', 'Value'],
                    [
                        ['Success', 'Yes'],
                        ['Testnet', $connectionResult['is_testnet'] ? 'Yes' : 'No'],
                        ['BTC Price', '$' . number_format($connectionResult['btc_price'], 2)],
                        ['Server Time', $connectionResult['server_time']],
                        ['Message', $connectionResult['message']],
                    ]
                );
            } else {
                $this->error("❌ Service Connection failed: " . $connectionResult['message']);
                if (isset($connectionResult['error_details'])) {
                    $this->warn("Error details: " . $connectionResult['error_details']);
                }
            }
        } catch (\Exception $e) {
            $this->error("❌ Service Connection test error: " . $e->getMessage());
        }
        
        // 5. Test getFuturesBalance
        $this->info("\n💰 Testing Futures Balance...");
        try {
            $balanceResult = $service->getFuturesBalanceOnly($userId);
            
            $this->info("✅ Balance Check:");
            $this->table(
                ['Field', 'Value'],
                [
                    ['Total Balance', '$' . number_format($balanceResult['total'], 2)],
                    ['Available Balance', '$' . number_format($balanceResult['available'], 2)],
                    ['Has Minimum ($10)', $balanceResult['has_minimum_balance'] ? 'Yes' : 'No'],
                    ['Testnet', $balanceResult['is_testnet'] ? 'Yes' : 'No'],
                    ['Used Fallback', isset($balanceResult['used_fallback']) && $balanceResult['used_fallback'] ? 'Yes' : 'No'],
                ]
            );
            
            if (isset($balanceResult['error'])) {
                $this->warn("⚠️ Balance check had error: " . $balanceResult['error']);
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Balance check failed: " . $e->getMessage());
        }
    }
}