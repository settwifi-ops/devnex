<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TradingExecutedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $symbol;
    public $action;
    public $message;
    public $successCount;
    public $totalUsers;
    public $userId;
    public $type;
    public $timestamp;

    public function __construct($symbol, $action, $message, $successCount = null, $totalUsers = null, $userId = null, $type = 'trading')
    {
        $this->symbol = $symbol;
        $this->action = $action;
        $this->message = $message;
        $this->successCount = $successCount;
        $this->totalUsers = $totalUsers;
        $this->userId = $userId;
        $this->type = $type;
        $this->timestamp = now()->toDateTimeString();
    }

    public function broadcastOn(): array
    {
        if ($this->userId) {
            return [
                new PrivateChannel('private-user.' . $this->userId)
            ];
        }
        
        return [
            new Channel('trading.global'),
            new Channel('notifications.global')
        ];
    }

    public function broadcastAs(): string
    {
        return 'new.signal';
    }

    public function broadcastWith(): array
    {
        // ✅ UNTUK TRADE EXECUTIONS - FORMAT YANG DIMENGERTI FRONTEND
        return [
            'id' => uniqid(),
            'symbol' => $this->symbol,
            'name' => $this->symbol,
            'action' => $this->action,
            'confidence' => 0, // ✅ BUKAN AI SIGNAL
            'price' => 0, 
            'score' => 0, // ✅ BUKAN AI SIGNAL  
            'risk' => 'MEDIUM',
            'health' => 100,
            'volume_spike' => 0,
            'momentum_regime' => 'NEUTRAL', 
            'rsi_delta' => 0,
            'timestamp' => $this->timestamp,
            'explanation' => $this->message,
            'notification_id' => uniqid(),
            
            // ✅ EXTRA DATA UNTUK FRONTEND FILTERING
            'type' => $this->type,
            'icon' => $this->getIcon(),
            'color' => $this->getColor(),
            'original_action' => $this->action,
            'original_message' => $this->message,
            
            // ✅ TANDAI SEBAGAI TRADE EXECUTION (BUKAN AI SIGNAL)
            'is_trade_execution' => true,
            'is_ai_signal' => false
        ];
    }

    private function getIcon()
    {
        switch ($this->action) {
            case 'BUY': return '🟢';
            case 'SELL': return '🔴';
            case 'CLOSE': return '⚡';
            case 'STOP_LOSS': return '🛑';
            case 'TAKE_PROFIT': return '💰';
            case 'ERROR': return '❌';
            default: return 'ℹ️';
        }
    }

    private function getColor()
    {
        switch ($this->action) {
            case 'BUY': return 'green';
            case 'SELL': return 'red';
            case 'CLOSE': return 'blue';
            case 'STOP_LOSS': return 'red';
            case 'TAKE_PROFIT': return 'green';
            case 'ERROR': return 'red';
            default: return 'gray';
        }
    }
}