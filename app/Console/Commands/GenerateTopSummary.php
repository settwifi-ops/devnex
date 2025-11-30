<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Signal;
use Illuminate\Support\Facades\Http;

class GenerateTopSummary extends Command
{
    protected $signature = 'summary:generate-top';
    protected $description = 'Generate AI summary for top 5 signals by ai_probability';

    public function handle()
    {
        // Ambil 5 teratas berdasarkan ai_probability
        $signals = Signal::orderByDesc('ai_probability')
            ->limit(5)
            ->get();

        if ($signals->isEmpty()) {
            $this->warn("No signals found.");
            return;
        }

        foreach ($signals as $signal) {

            // Data yang dikirim ke GPT
            $dataText = "
                Symbol: {$signal->symbol}
                Name: {$signal->name}
                Enhanced Score: {$signal->enhanced_score}
                Smart Confidence: {$signal->smart_confidence}
                Current Price: {$signal->current_price}
                Price Change 1h: {$signal->price_change_1h}
                Price Change 24h: {$signal->price_change_24h}
                Volume Spike Ratio: {$signal->volume_spike_ratio}
                Volume Acceleration: {$signal->volume_acceleration}
                RSI Delta: {$signal->rsi_delta}
                Momentum Regime: {$signal->momentum_regime}
                Momentum Phase: {$signal->momentum_phase}
                Trend Strength: {$signal->trend_strength}
                Trend Power: {$signal->trend_power}
                Momentum Category: {$signal->momentum_category}
                Health Score: {$signal->health_score}
                Liquidity Position: {$signal->liquidity_position}
                Market Structure: {$signal->market_structure}
                Open Interest: {$signal->open_interest}
                OI Change: {$signal->oi_change}
                Funding Rate: {$signal->funding_rate}
                Funding Direction: {$signal->funding_direction}
                Whale Behavior: {$signal->whale_behavior}
                Support Level: {$signal->support_level}
                Resistance Level: {$signal->resistance_level}
            ";

            // Send prompt ke GPT
            $response = Http::withToken(env('OPENAI_API_KEY'))
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4.1-mini',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => "You are a professional crypto market analyst. Convert the following indicator data into a concise, formal paragraph explaining market bias, technical conditions, key levels, and projected price movements. Avoid using bullet points."
                        ],
                        [
                            'role' => 'user',
                            'content' => $dataText
                        ]
                    ],
                    'temperature' => 0.2
                ]);

            $summary = $response['choices'][0]['message']['content'] ?? null;

            if ($summary) {
                $signal->updateQuietly([
                    'summary' => $summary
                ]);

                $this->info("Updated summary: {$signal->symbol}");
            } else {
                $this->error("Failed to generate summary for {$signal->symbol}");
            }
        }

        $this->info("Top 5 AI summaries updated successfully!");
    }
}
