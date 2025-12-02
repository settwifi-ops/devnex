<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Models\AiSignal;
use App\Models\UserNotification;
use App\Models\User;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route untuk receive signals dari bot - PRIVATE CHANNEL
Route::post('/push-signal', function (Request $request) {
    Log::info('📥 Signal received from bot:', $request->all());
    
    try {
        $data = $request->validate([
            'symbol' => 'required|string',
            'name' => 'required|string',
            'price' => 'required|numeric',
            'score' => 'required|numeric',
            'confidence' => 'required|numeric',
            'risk' => 'required|string',
            'health' => 'required|numeric',
            'volume_spike' => 'required|numeric',
            'momentum_regime' => 'required|string',
            'rsi_delta' => 'required|numeric',
            'timestamp' => 'required|string'
        ]);

        Log::info('✅ Signal validated:', $data);

        // Determine action sesuai enum di migration
        $action = $data['confidence'] >= 70 ? 'BUY' : 'SELL';

        // Simpan ke database AISignal
        $aiSignal = AiSignal::create([
            'symbol' => $data['symbol'],
            'name' => $data['name'],
            'action' => $action,
            'confidence' => $data['confidence'],
            'current_price' => $data['price'],
            'target_price' => null,
            'signal_score' => $data['score'],
            'risk_level' => strtoupper($data['risk']),
            'health_score' => $data['health'],
            'volume_spike' => $data['volume_spike'],
            'momentum_regime' => $data['momentum_regime'],
            'rsi_delta' => $data['rsi_delta'],
            'signal_time' => now(),
            'metadata' => [
                'source' => 'python_bot',
                'received_at' => now()->toISOString(),
                'original_timestamp' => $data['timestamp']
            ],
            'is_read' => false
        ]);

        Log::info('💾 AI Signal saved:', ['id' => $aiSignal->id]);

        // 🔄 BUAT NOTIFICATION UNTUK SETIAP USER
        $users = User::all();
        $notifications = [];
        
        foreach ($users as $user) {
            $notifications[] = [
                'user_id' => $user->id,
                'ai_signal_id' => $aiSignal->id,
                'is_read' => false,
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        
        // Bulk insert untuk performance
        UserNotification::insert($notifications);
        
        // Ambil semua notification IDs sekaligus
        $userNotifications = UserNotification::where('ai_signal_id', $aiSignal->id)
            ->get()
            ->keyBy('user_id');

        Log::info('👥 Notifications created for all users', ['count' => count($notifications)]);

        // Manual Pusher trigger - PRIVATE CHANNEL PER USER
        $pusher = new Pusher\Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => true
            ]
        );

        $basePusherData = [
            'id' => $aiSignal->id,
            'symbol' => $aiSignal->symbol,
            'name' => $aiSignal->name,
            'action' => $aiSignal->action,
            'confidence' => (float) $aiSignal->confidence,
            'price' => (float) $aiSignal->current_price,
            'score' => (float) $aiSignal->signal_score,
            'risk' => $aiSignal->risk_level,
            'health' => $aiSignal->health_score,
            'volume_spike' => (float) $aiSignal->volume_spike,
            'momentum_regime' => $aiSignal->momentum_regime,
            'rsi_delta' => (float) $aiSignal->rsi_delta,
            'timestamp' => $aiSignal->signal_time->toISOString(),
            'explanation' => "AI {$aiSignal->action} signal • Score: {$aiSignal->signal_score}/100 • Confidence: {$aiSignal->confidence}%"
        ];

        // 🔄 BROADCAST KE PRIVATE CHANNEL SETIAP USER
        $broadcastResults = [];
        foreach ($users as $user) {
            $userNotification = $userNotifications->get($user->id);
            
            if ($userNotification) {
                $userData = array_merge($basePusherData, [
                    'notification_id' => $userNotification->id
                ]);
                
                $result = $pusher->trigger("private-user-{$user->id}", 'new.signal', $userData);
                
                $broadcastResults[] = [
                    'user_id' => $user->id,
                    'success' => $result
                ];
                
                Log::info("🚀 PRIVATE SIGNAL: Sent to user {$user->id}", [
                    'user_id' => $user->id,
                    'signal_id' => $aiSignal->id,
                    'notification_id' => $userNotification->id
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Signal received and broadcasted to private channels',
            'data' => $aiSignal,
            'users_count' => $users->count(),
            'broadcast_results' => $broadcastResults
        ]);

    } catch (\Exception $e) {
        Log::error('❌ Error processing signal: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
});

// Test route untuk private channel
Route::get('/test-pusher-private', function() {
    try {
        $user = User::first();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'No user found'], 404);
        }

        $pusher = new Pusher\Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => true
            ]
        );

        $testData = [
            'id' => rand(1000, 9999),
            'symbol' => 'TESTBTC',
            'name' => 'Test Bitcoin',
            'action' => 'BUY',
            'confidence' => 85.50,
            'price' => 45000.1234,
            'score' => 88.50,
            'risk' => 'LOW',
            'health' => 95,
            'volume_spike' => 2.50,
            'momentum_regime' => 'BULLISH',
            'rsi_delta' => 2.3456,
            'timestamp' => now()->toISOString(),
            'explanation' => 'AI BUY signal • Score: 88.50/100 • Confidence: 85.50%',
            'notification_id' => rand(1000, 9999)
        ];

        $result = $pusher->trigger("private-user-{$user->id}", 'new.signal', $testData);

        Log::info('🧪 TEST: Private signal sent', [
            'user_id' => $user->id,
            'channel' => "private-user-{$user->id}"
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Test signal sent via private channel',
            'user_id' => $user->id,
            'channel' => "private-user-{$user->id}",
            'data' => $testData,
            'pusher_result' => $result
        ]);

    } catch (\Exception $e) {
        Log::error('Test failed: ' . $e->getMessage());
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
});