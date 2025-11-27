<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_portfolios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('initial_balance', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('equity', 15, 2)->default(0);
            $table->decimal('realized_pnl', 15, 2)->default(0);
            $table->decimal('floating_pnl', 15, 2)->default(0);
            $table->enum('risk_mode', ['CONSERVATIVE', 'MODERATE', 'AGGRESSIVE'])->default('MODERATE');
            $table->decimal('risk_value', 5, 2)->default(5.00); // % risk per trade
            $table->boolean('ai_trade_enabled')->default(false);
            $table->timestamp('last_reset_at')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_portfolios');
    }
};