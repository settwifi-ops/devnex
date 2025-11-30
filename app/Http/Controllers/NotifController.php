<?php

namespace App\Http\Controllers;

use App\Models\AiSignal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotifController extends Controller
{
    public function pushSignal(Request $request)
    {
        Log::info('📡 Received signal', $request->all());

        try {
            // Tentukan action berdasarkan confidence
            $action = $request->confidence >= 70 ? 'BUY' : 'HOLD';
            
            // Cek apakah ada sinyal terbaru untuk coin yang sama dengan action yang sama
            $existingSignal = AiSignal::where('symbol', $request->symbol)
                ->where('action', $action)
                ->orderBy('created_at', 'desc')
                ->first();

            // Jika ada sinyal yang sama (symbol + action sama) dalam waktu tertentu
            // Anda bisa menambahkan kondisi waktu jika diperlukan, misalnya dalam 1 jam terakhir
            if ($existingSignal) {
                Log::info('🚫 Duplicate signal detected, skipping save', [
                    'symbol' => $request->symbol,
                    'action' => $action
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Duplicate signal detected, not saved'
                ]);
            }

            // Simpan ke database jika bukan duplikat
            $signal = AiSignal::create([
                'symbol' => $request->symbol,
                'name' => $request->name,
                'action' => $action,
                'confidence' => $request->confidence,
                'current_price' => $request->price,
                'signal_score' => $request->score,
                'risk_level' => $request->risk,
                'health_score' => $request->health
            ]);

            // Trigger event Pusher
            event(new \App\Events\NewAISignal($signal));

            return response()->json([
                'status' => 'success',
                'message' => 'Signal received and saved'
            ]);

        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
    }
}