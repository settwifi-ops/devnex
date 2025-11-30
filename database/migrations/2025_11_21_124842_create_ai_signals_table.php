<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ai_signals', function (Blueprint $table) {
            $table->id();
            $table->string('symbol'); // BTCUSDT, ETHUSDT, etc
            $table->string('name'); // Bitcoin, Ethereum, etc
            $table->enum('action', ['BUY', 'SELL', 'HOLD', 'MONITOR']);
            $table->decimal('confidence', 5, 2); // 85.50
            $table->decimal('current_price', 15, 8);
            $table->decimal('target_price', 15, 8)->nullable();
            $table->decimal('signal_score', 5, 2); // Enhanced score
            $table->enum('risk_level', ['VERY_LOW','LOW', 'MEDIUM', 'HIGH']);
            $table->integer('health_score'); // 0-100
            $table->decimal('volume_spike', 8, 2); // 2.50x
            $table->string('momentum_regime'); // BULLISH, BEARISH, etc
            $table->decimal('rsi_delta', 6, 4); // 2.3456
            $table->timestamp('signal_time');
            $table->json('metadata')->nullable(); // Additional data
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            // Indexes for performance
            $table->index(['symbol', 'signal_time']);
            $table->index(['action', 'confidence']);
            $table->index('is_read');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ai_signals');
    }
};