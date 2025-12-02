<?php
// app/Services/BinanceAccountService.php
namespace App\Services;

use App\Models\UserBinanceAccount;
use App\Models\User;
use App\Models\UserPortfolio;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class BinanceAccountService
{
    private $client;

    public function __construct()
    {
        $this->client = new Client([
            'timeout'  => 10,
            'verify'   => false, // Untuk development saja
        ]);
    }

    /**
     * Validate API credentials dengan Binance Futures API
     */
    private function validateApiCredentials($apiKey, $apiSecret, $isTestnet = true)
    {
        try {
            // GUNAKAN BASE URL YANG BENAR
            if ($isTestnet) {
                $baseUrl = 'https://testnet.binancefuture.com';
                Log::info("🔧 Validating TESTNET API credentials");
            } else {
                $baseUrl = 'https://fapi.binance.com';
                Log::info("🚀 Validating MAINNET API credentials");
            }

            $timestamp = round(microtime(true) * 1000);
            $queryString = "timestamp={$timestamp}";
            $signature = hash_hmac('sha256', $queryString, $apiSecret);
            
            Log::info("🌐 API Validation Request", [
                'base_url' => $baseUrl,
                'is_testnet' => $isTestnet,
                'timestamp' => $timestamp
            ]);
            
            $response = $this->client->get("{$baseUrl}/fapi/v2/account", [
                'headers' => [
                    'X-MBX-APIKEY' => $apiKey,
                ],
                'query' => [
                    'timestamp' => $timestamp,
                    'signature' => $signature,
                ]
            ]);
            
            $account = json_decode($response->getBody(), true);
            
            Log::info("✅ API Validation Success", [
                'total_balance' => $account['totalWalletBalance'] ?? 'N/A',
                'available_balance' => $account['availableBalance'] ?? 'N/A'
            ]);
            
            // Check jika credentials valid
            if (!isset($account['totalWalletBalance'])) {
                return [
                    'success' => false,
                    'message' => 'Invalid API credentials or missing futures access'
                ];
            }

            $permissions = [
                'canTrade' => true,
                'canTradeFutures' => true,
                'canWithdraw' => false,
            ];

            return [
                'success' => true,
                'permissions' => $permissions,
                'account_info' => [
                    'totalWalletBalance' => $account['totalWalletBalance'],
                    'availableBalance' => $account['availableBalance'],
                    'totalInitialMargin' => $account['totalInitialMargin'] ?? 0,
                    'totalMaintMargin' => $account['totalMaintMargin'] ?? 0,
                    'totalUnrealizedProfit' => $account['totalUnrealizedProfit'] ?? 0,
                    'totalMarginBalance' => $account['totalMarginBalance'] ?? 0,
                    'updateTime' => $account['updateTime'] ?? null
                ]
            ];

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $error = json_decode($response->getBody(), true);
            
            Log::error("❌ API Validation Failed", [
                'is_testnet' => $isTestnet,
                'error' => $error['msg'] ?? $e->getMessage(),
                'status_code' => $e->getCode()
            ]);
            
            return [
                'success' => false,
                'message' => 'Binance API Error: ' . ($error['msg'] ?? $e->getMessage())
            ];
        } catch (\Exception $e) {
            Log::error("❌ API Connection Failed", [
                'is_testnet' => $isTestnet,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Connect & validate Binance account
     */
    public function connectAccount($userId, $apiKey, $apiSecret, $isTestnet = true, $label = null)
    {
        try {
            Log::info("🔗 Starting Binance Connection", [
                'user_id' => $userId,
                'is_testnet' => $isTestnet,
                'has_api_key' => !empty($apiKey),
                'has_secret' => !empty($apiSecret)
            ]);

            // 1. Validate API credentials
            $validation = $this->validateApiCredentials($apiKey, $apiSecret, $isTestnet);
            
            if (!$validation['success']) {
                Log::error("❌ API Validation Failed for user {$userId}", [
                    'message' => $validation['message'],
                    'is_testnet' => $isTestnet
                ]);
                
                return [
                    'success' => false,
                    'message' => $validation['message'],
                    'error_code' => 'API_VALIDATION_FAILED'
                ];
            }

            // 2. Set default label
            if (!$label) {
                $label = $isTestnet ? 'Testnet Account' : 'Live Trading Account';
            }

            // 3. Encrypt dan store ke database
            $account = UserBinanceAccount::updateOrCreate(
                [
                    'user_id' => $userId,
                    'is_testnet' => $isTestnet,
                    'label' => $label
                ],
                [
                    'environment' => $isTestnet ? 'testnet' : 'mainnet',
                    'api_key_encrypted' => $this->encryptData($apiKey),
                    'api_secret_encrypted' => $this->encryptData($apiSecret),
                    'is_active' => true,
                    'permissions' => $validation['permissions'],
                    'balance_snapshot' => $validation['account_info']['totalWalletBalance'],
                    'last_verified' => now(),
                    'verification_status' => 'verified'
                ]
            );

            Log::info("💾 Account Saved to Database", [
                'account_id' => $account->id,
                'is_testnet' => $account->is_testnet,
                'balance' => $validation['account_info']['totalWalletBalance']
            ]);

            // 4. Test decrypt setelah save
            try {
                $testKey = $this->decryptData($account->api_key_encrypted);
                $testSecret = $this->decryptData($account->api_secret_encrypted);
                
                Log::info("🔐 Encryption/Decryption Test Success", [
                    'original_key_first_5' => substr($apiKey, 0, 5),
                    'decrypted_key_first_5' => substr($testKey, 0, 5),
                    'match' => $apiKey === $testKey
                ]);
            } catch (\Exception $e) {
                Log::error("🔐 Encryption/Decryption Test Failed: " . $e->getMessage());
            }

            // 5. Update portfolio user
            $user = User::find($userId);
            if (!$user->portfolio) {
                $portfolio = UserPortfolio::create([
                    'user_id' => $userId,
                    'real_trading_active' => true,
                    'real_trading_enabled' => false,
                    'real_balance' => $validation['account_info']['totalWalletBalance'],
                    'real_equity' => $validation['account_info']['totalMarginBalance'] ?? $validation['account_info']['totalWalletBalance'],
                    'binance_connected_at' => now(),
                    'binance_environment' => $isTestnet ? 'testnet' : 'mainnet'
                ]);
                
                Log::info("📊 New Portfolio Created", [
                    'portfolio_id' => $portfolio->id,
                    'real_trading_active' => true
                ]);
            } else {
                $user->portfolio->update([
                    'real_trading_active' => true,
                    'real_trading_enabled' => false,
                    'real_balance' => $validation['account_info']['totalWalletBalance'],
                    'real_equity' => $validation['account_info']['totalMarginBalance'] ?? $validation['account_info']['totalWalletBalance'],
                    'binance_connected_at' => now(),
                    'binance_environment' => $isTestnet ? 'testnet' : 'mainnet'
                ]);
                
                Log::info("📊 Portfolio Updated", [
                    'real_trading_active' => true,
                    'real_balance' => $validation['account_info']['totalWalletBalance']
                ]);
            }

            return [
                'success' => true,
                'message' => 'Binance ' . ($isTestnet ? 'Testnet' : 'Live') . ' account connected successfully!',
                'account' => $account,
                'balance' => $validation['account_info']['totalWalletBalance'],
                'is_testnet' => $isTestnet
            ];

        } catch (\Exception $e) {
            Log::error("💥 Binance Connection Failed", [
                'user_id' => $userId,
                'is_testnet' => $isTestnet,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
                'error_code' => 'CONNECTION_ERROR'
            ];
        }
    }

    /**
     * Get Binance instance untuk trading
     */
    // Update method getBinanceInstance() di BinanceAccountService.php
    public function getBinanceInstance($userId, $isTestnet = null)
    {
        try {
            $query = UserBinanceAccount::where('user_id', $userId)->active()->verified();
            
            // Jika spesifik testnet/mainnet diminta
            if ($isTestnet !== null) {
                $query->where('is_testnet', $isTestnet);
            }
            
            $account = $query->first();

            if (!$account) {
                throw new \Exception('No active Binance account found for user ' . $userId);
            }

            Log::info("🔧 Creating Binance Instance", [
                'user_id' => $userId,
                'account_id' => $account->id,
                'is_testnet' => $account->is_testnet,
                'label' => $account->label,
                'environment' => $account->is_testnet ? 'TESTNET' : 'MAINNET'
            ]);

            // AMBIL API KEY & SECRET DARI DATABASE
            Log::info("🔍 Checking encrypted data in database", [
                'api_key_encrypted_length' => strlen($account->api_key_encrypted),
                'api_secret_encrypted_length' => strlen($account->api_secret_encrypted),
                'api_key_sample' => substr($account->api_key_encrypted, 0, 20) . '...',
            ]);
            
            // Decrypt dari database
            $apiKey = $this->decryptData($account->api_key_encrypted);
            $apiSecret = $this->decryptData($account->api_secret_encrypted);
            
            // Validate decrypted data
            if (empty($apiKey) || empty($apiSecret)) {
                throw new \Exception('Decrypted API keys are empty or invalid');
            }
            
            Log::info("🔑 Decrypted API Keys from Database", [
                'api_key_first_5' => substr($apiKey, 0, 5) . '...',
                'secret_first_5' => substr($apiSecret, 0, 5) . '...',
                'key_length' => strlen($apiKey),
                'secret_length' => strlen($apiSecret)
            ]);

            // Untuk LIBRARY JAGGEDSOFT
            $binance = new \Binance\API($apiKey, $apiSecret, [
                'useServerTime' => true,
                'testnet' => $account->is_testnet
            ]);
            
            // Test connection dengan method yang tersedia
            try {
                // Gunakan method yang tersedia di jaggedsoft library
                // Coba price() atau time() untuk test connection
                $testPrice = $binance->price("BTCUSDT");
                
                Log::info("📡 Binance Connection Test Success", [
                    'testnet' => $account->is_testnet,
                    'btc_price' => $testPrice,
                    'status' => 'connected'
                ]);
            } catch (\Exception $e) {
                Log::warning("⚠️ Binance connection test warning: " . $e->getMessage());
                // Lanjutkan meski test gagal
            }
            
            return $binance;

        } catch (\Exception $e) {
            Log::error("❌ Binance Instance Creation Failed", [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception("Failed to initialize Binance: " . $e->getMessage());
        }
    }

    /**
     * Get futures balance untuk trading
     */
    public function getFuturesBalanceOnly($userId)
    {
        try {
            $api = $this->getBinanceInstance($userId);
            
            Log::info("💰 Fetching Futures Balance", [
                'user_id' => $userId
            ]);

            // Method yang tersedia di library jaggedsoft
            $balance = null;
            
            // Coba futuresAccount() terlebih dahulu
            if (method_exists($api, 'futuresAccount')) {
                $balance = $api->futuresAccount();
                Log::info("📊 Using futuresAccount() method");
            }
            // Fallback ke account()
            elseif (method_exists($api, 'account')) {
                $account = $api->account();
                $balance = $account;
                Log::info("📊 Using account() method");
            }
            else {
                throw new \Exception("No balance method found in Binance library");
            }

            // EXTRACT USDT BALANCE
            $usdtBalance = 0;
            $availableBalance = 0;
            
            if (isset($balance['totalWalletBalance'])) {
                $usdtBalance = floatval($balance['totalWalletBalance']);
                $availableBalance = floatval($balance['availableBalance'] ?? $usdtBalance);
            } 
            elseif (isset($balance['availableBalance'])) {
                $usdtBalance = floatval($balance['availableBalance']);
                $availableBalance = $usdtBalance;
            }
            elseif (isset($balance['USDT'])) {
                $usdtBalance = floatval($balance['USDT']['available'] ?? 0);
                $availableBalance = $usdtBalance;
            }
            else {
                // Fallback ke portfolio balance
                $user = User::find($userId);
                $usdtBalance = $user->portfolio->real_balance ?? 0;
                $availableBalance = $usdtBalance;
                Log::warning("⚠️ Using portfolio balance as fallback: " . $usdtBalance);
            }

            // Get account info
            $account = UserBinanceAccount::where('user_id', $userId)->active()->first();
            $isTestnet = $account ? $account->is_testnet : true;

            Log::info("✅ Futures Balance Retrieved", [
                'user_id' => $userId,
                'total_balance' => $usdtBalance,
                'available_balance' => $availableBalance,
                'is_testnet' => $isTestnet
            ]);

            return [
                'total' => $usdtBalance,
                'available' => $availableBalance,
                'has_minimum_balance' => $availableBalance >= 10,
                'is_testnet' => $isTestnet,
                'full_balance' => $balance
            ];

        } catch (\Exception $e) {
            Log::error("❌ Futures Balance Failed", [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'fallback' => 'Using portfolio balance'
            ]);
            
            // FALLBACK: Gunakan portfolio balance
            $user = User::find($userId);
            $portfolioBalance = $user->portfolio->real_balance ?? 0;
            $account = UserBinanceAccount::where('user_id', $userId)->active()->first();
            
            return [
                'total' => $portfolioBalance,
                'available' => $portfolioBalance,
                'has_minimum_balance' => $portfolioBalance >= 10,
                'is_testnet' => $account ? $account->is_testnet : true,
                'error' => $e->getMessage(),
                'used_fallback' => true
            ];
        }
    }

    /**
     * Get futures balance (alias untuk compatibility)
     */
    public function getFuturesBalance($userId)
    {
        return $this->getFuturesBalanceOnly($userId);
    }

    /**
     * Validate account status sebelum trading
     */
    public function validateAccountForTrading($userId)
    {
        $account = UserBinanceAccount::where('user_id', $userId)
            ->active()
            ->verified()
            ->first();

        if (!$account) {
            throw new \Exception('Binance account not active or verified');
        }

        // Check last verification (max 1 day old)
        if (!$account->last_verified || $account->last_verified->lt(now()->subDay())) {
            throw new \Exception('Binance account needs re-verification');
        }

        return $account->is_testnet;
    }

    /**
     * Test connection untuk user
     */
    // Update method testConnection() di app/Services/BinanceAccountService.php
    public function testConnection($userId)
    {
        try {
            $api = $this->getBinanceInstance($userId);
            
            // Test dengan price() karena ping() tidak ada di jaggedsoft
            $price = $api->price("BTCUSDT");
            
            // Test dengan time() - method ini mengembalikan array
            $timeData = $api->time();
            $serverTime = isset($timeData['serverTime']) ? $timeData['serverTime'] : null;
            
            $account = UserBinanceAccount::where('user_id', $userId)->active()->first();
            
            return [
                'success' => true,
                'message' => 'Connection successful',
                'btc_price' => $price,
                'server_time' => $serverTime ? date('Y-m-d H:i:s', $serverTime / 1000) : 'N/A',
                'is_testnet' => $account ? $account->is_testnet : true
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
                'error_details' => $e->getTraceAsString()
            ];
        }
    }

    /**
     * Update balance snapshot
     */
    public function updateBalanceSnapshot($userId)
    {
        try {
            $account = UserBinanceAccount::where('user_id', $userId)->active()->first();
            
            if (!$account) {
                return false;
            }

            // Decrypt keys
            $apiKey = $this->decryptData($account->api_key_encrypted);
            $apiSecret = $this->decryptData($account->api_secret_encrypted);

            $validation = $this->validateApiCredentials($apiKey, $apiSecret, $account->is_testnet);
            
            if ($validation['success']) {
                // Update account balance
                $account->update([
                    'balance_snapshot' => $validation['account_info']['totalWalletBalance'],
                    'last_verified' => now()
                ]);

                // Update portfolio juga
                $user = User::find($userId);
                if ($user->portfolio) {
                    $user->portfolio->update([
                        'real_balance' => $validation['account_info']['totalWalletBalance'],
                        'real_equity' => $validation['account_info']['totalMarginBalance'] ?? $validation['account_info']['totalWalletBalance']
                    ]);
                }

                return $validation['account_info'];
            }

            return false;

        } catch (\Exception $e) {
            Log::error("Balance snapshot failed for user {$userId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Disconnect Binance account
     */
    public function disconnectAccount($userId, $isTestnet = null)
    {
        try {
            $query = UserBinanceAccount::where('user_id', $userId);
            
            if ($isTestnet !== null) {
                $query->where('is_testnet', $isTestnet);
            }
            
            $query->update([
                'is_active' => false,
                'verification_status' => 'disconnected'
            ]);

            // Nonaktifkan real trading jika semua account disconnected
            $activeAccounts = UserBinanceAccount::where('user_id', $userId)->active()->count();
            
            if ($activeAccounts === 0) {
                $user = User::find($userId);
                if ($user->portfolio) {
                    $user->portfolio->update([
                        'real_trading_active' => false,
                        'real_trading_enabled' => false
                    ]);
                }
            }

            return [
                'success' => true,
                'message' => 'Binance account disconnected successfully'
            ];

        } catch (\Exception $e) {
            Log::error("Disconnect account failed: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Disconnect failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete Binance account
     */
    public function deleteAccount($userId, $isTestnet = null)
    {
        try {
            $query = UserBinanceAccount::where('user_id', $userId);
            
            if ($isTestnet !== null) {
                $query->where('is_testnet', $isTestnet);
            }
            
            $accounts = $query->get();
            
            foreach ($accounts as $account) {
                // Soft delete atau update status
                $account->update([
                    'is_active' => false,
                    'verification_status' => 'deleted',
                    'deleted_at' => now()
                ]);
            }

            // Update portfolio status
            $user = User::find($userId);
            if ($user->portfolio) {
                $user->portfolio->update([
                    'real_trading_active' => false,
                    'real_trading_enabled' => false,
                    'binance_connected_at' => null
                ]);
            }

            Log::info("🗑️ Binance Account Deleted", [
                'user_id' => $userId,
                'is_testnet' => $isTestnet,
                'deleted_count' => $accounts->count()
            ]);

            return [
                'success' => true,
                'message' => 'Binance account disconnected successfully',
                'deleted_count' => $accounts->count()
            ];

        } catch (\Exception $e) {
            Log::error("❌ Delete Account Failed", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to delete account: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get user's connected accounts
     */
    public function getUserAccounts($userId)
    {
        return UserBinanceAccount::where('user_id', $userId)
            ->where('is_active', true)
            ->orderBy('is_testnet', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Test API keys dari database untuk debugging
     */
    public function testDatabaseApiKeys($userId)
    {
        try {
            $account = UserBinanceAccount::where('user_id', $userId)->active()->first();
            
            if (!$account) {
                return [
                    'success' => false,
                    'message' => 'No Binance account found'
                ];
            }
            
            // Decrypt dari database
            $apiKey = $this->decryptData($account->api_key_encrypted);
            $apiSecret = $this->decryptData($account->api_secret_encrypted);
            
            return [
                'success' => true,
                'data' => [
                    'api_key_first_10' => substr($apiKey, 0, 10) . '...',
                    'api_secret_first_10' => substr($apiSecret, 0, 10) . '...',
                    'key_length' => strlen($apiKey),
                    'secret_length' => strlen($apiSecret),
                    'is_testnet' => $account->is_testnet,
                    'label' => $account->label,
                    'encrypted_key_length' => strlen($account->api_key_encrypted),
                    'encrypted_secret_length' => strlen($account->api_secret_encrypted)
                ]
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Decryption failed: ' . $e->getMessage(),
                'error_details' => $e->getTraceAsString()
            ];
        }
    }

    /**
     * Helper: Encrypt data dengan error handling
     */
    private function encryptData($data)
    {
        try {
            if (empty($data)) {
                throw new \Exception('Cannot encrypt empty data');
            }
            
            return Crypt::encryptString($data);
        } catch (\Exception $e) {
            Log::error("Encryption failed: " . $e->getMessage());
            throw new \Exception('Encryption failed: ' . $e->getMessage());
        }
    }

    /**
     * Helper: Decrypt data dengan error handling
     */
    private function decryptData($encryptedData)
    {
        try {
            if (empty($encryptedData)) {
                throw new \Exception('Cannot decrypt empty data');
            }
            
            return Crypt::decryptString($encryptedData);
        } catch (\Exception $e) {
            Log::error("Decryption failed: " . $e->getMessage());
            throw new \Exception('Decryption failed: ' . $e->getMessage());
        }
    }
    // Tambahkan method ini di BinanceAccountService.php
    /**
     * Check available methods in Binance library
     */
    public function checkAvailableMethods($userId)
    {
        try {
            $api = $this->getBinanceInstance($userId);
            
            $methods = get_class_methods($api);
            
            // Filter untuk methods yang relevan
            $relevantMethods = array_filter($methods, function($method) {
                return stripos($method, 'future') !== false || 
                       stripos($method, 'order') !== false ||
                       stripos($method, 'price') !== false ||
                       stripos($method, 'balance') !== false ||
                       stripos($method, 'account') !== false;
            });
            
            return [
                'success' => true,
                'total_methods' => count($methods),
                'relevant_methods' => array_values($relevantMethods)
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    /**
     * Fix corrupted encrypted data
     */
    public function fixEncryptedData($userId, $newApiKey, $newApiSecret, $isTestnet = true)
    {
        try {
            $account = UserBinanceAccount::where('user_id', $userId)
                ->where('is_testnet', $isTestnet)
                ->first();
                
            if (!$account) {
                return [
                    'success' => false,
                    'message' => 'Account not found'
                ];
            }
            
            // Update dengan data baru terenkripsi
            $account->update([
                'api_key_encrypted' => $this->encryptData($newApiKey),
                'api_secret_encrypted' => $this->encryptData($newApiSecret),
                'last_verified' => now()
            ]);
            
            // Test decrypt
            $testKey = $this->decryptData($account->api_key_encrypted);
            $testSecret = $this->decryptData($account->api_secret_encrypted);
            
            Log::info("🔧 Encrypted Data Fixed", [
                'user_id' => $userId,
                'original_key_match' => $newApiKey === $testKey,
                'original_secret_match' => $newApiSecret === $testSecret
            ]);
            
            return [
                'success' => true,
                'message' => 'Encrypted data fixed successfully'
            ];
            
        } catch (\Exception $e) {
            Log::error("Fix encrypted data failed: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Fix failed: ' . $e->getMessage()
            ];
        }
    }
}