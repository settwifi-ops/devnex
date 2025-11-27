<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_signals_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('signals', function (Blueprint $table) {
            $table->id();
            $table->string('symbol')->unique();
            $table->string('name');
            $table->decimal('enhanced_score', 10, 8);
            $table->decimal('smart_confidence', 5, 2);
            $table->decimal('current_price', 20, 8);
            $table->decimal('price_change_1h', 10, 4)->default(0);
            $table->decimal('price_change_24h', 10, 4)->default(0);
            $table->decimal('volume_spike_ratio', 10, 4)->default(1);
            $table->decimal('volume_acceleration', 10, 4)->default(0);
            $table->decimal('rsi_delta', 10, 4)->default(0);
            $table->string('momentum_regime');
            $table->string('momentum_phase');
            $table->decimal('health_score', 5, 2);
            $table->decimal('trend_strength', 10, 4)->default(0);
            $table->string('risk_level');
            $table->integer('appearance_count')->default(1);
            $table->decimal('performance_since_first', 10, 4)->default(0);
            $table->integer('hours_since_first')->default(0);
            $table->string('latest_update');
            $table->timestamp('timestamp')->nullable(); // Ubah jadi nullable
            $table->timestamp('first_detection_time')->nullable(); // Ubah jadi nullable
            $table->timestamps();
            
            $table->index(['symbol']);
            $table->index(['enhanced_score']);
            $table->index(['smart_confidence']);
            $table->index(['created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('signals');
    }
};