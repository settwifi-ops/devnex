<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('trade_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('ai_decision_id')->constrained()->onDelete('cascade');
            $table->foreignId('position_id')->nullable()->constrained('user_positions')->onDelete('cascade');
            $table->string('symbol');
            $table->enum('action', ['BUY', 'SELL']);
            $table->decimal('qty', 15, 8);
            $table->decimal('price', 15, 8);
            $table->decimal('amount', 15, 2); // total amount in USD
            $table->decimal('pnl', 15, 2)->nullable(); // untuk SELL action
            $table->decimal('pnl_percentage', 8, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('trade_histories');
    }
};