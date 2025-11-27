<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('performances', function (Blueprint $table) {
            $table->id();
            $table->string('symbol')->unique(); // UNIQUE KEY di symbol saja
            $table->decimal('performance_since_first', 10, 2);
            $table->integer('health_score');
            $table->integer('appearance_count');
            $table->integer('trend_strength');
            $table->decimal('hours_since_first', 8, 2);
            $table->string('momentum_phase');
            $table->string('risk_level');
            $table->timestamp('last_seen')->nullable();
            $table->decimal('current_price', 10, 4);
            $table->boolean('is_active');
            $table->integer('rank');
            $table->timestamp('data_timestamp')->nullable(); // untuk tracking kapan data diambil
            $table->timestamp('first_detection_time')->nullable();
            $table->timestamps();
            
            // Index untuk performa
            $table->index('symbol');
            $table->index('rank');
            $table->index('is_active');
            $table->index('risk_level');
        });
    }

    public function down()
    {
        Schema::dropIfExists('performances');
    }
};