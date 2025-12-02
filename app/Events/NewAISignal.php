<?php

namespace App\Events;

use App\Models\AiSignal;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewAISignal implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $signal;
    public $userId;

    public function __construct(AiSignal $signal, $userId)
    {
        $this->signal = $signal;
        $this->userId = $userId;
    }

    public function broadcastOn()
    {
        return new Channel('user.' . $this->userId . '.notifications');
    }

    public function broadcastAs()
    {
        return 'new.signal';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->signal->id,
            'symbol' => $this->signal->symbol,
            'name' => $this->signal->name,
            'action' => $this->signal->action,
            'confidence' => (float) $this->signal->confidence,
            'price' => (float) $this->signal->current_price,
            'score' => (float) $this->signal->signal_score,
            'risk' => $this->signal->risk_level,
            'health' => $this->signal->health_score,
            'volume_spike' => (float) $this->signal->volume_spike,
            'momentum_regime' => $this->signal->momentum_regime,
            'rsi_delta' => (float) $this->signal->rsi_delta,
            'timestamp' => $this->signal->signal_time->toISOString(),
            'explanation' => $this->generateExplanation()
        ];
    }

    private function generateExplanation()
    {
        return sprintf(
            "AI %s signal • Score: %.1f/100 • Confidence: %.1f%% • Risk: %s • Health: %d/100",
            $this->signal->action,
            $this->signal->signal_score,
            $this->signal->confidence,
            $this->signal->risk_level,
            $this->signal->health_score
        );
    }
}
