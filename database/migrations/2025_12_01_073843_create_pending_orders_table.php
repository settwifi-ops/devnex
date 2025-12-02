<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePendingOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('pending_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('ai_decision_id')->constrained()->onDelete('cascade');
            $table->string('symbol', 20);
            $table->string('binance_order_id')->nullable();
            $table->decimal('limit_price', 16, 8);
            $table->decimal('quantity', 16, 8);
            $table->enum('side', ['BUY', 'SELL']);
            $table->enum('position_type', ['LONG', 'SHORT']);
            $table->timestamp('expires_at');
            $table->enum('status', ['PENDING', 'FILLED', 'EXPIRED', 'CANCELLED'])->default('PENDING');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes untuk performance
            $table->index(['user_id', 'status']);
            $table->index(['symbol', 'status']);
            $table->index('expires_at');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pending_orders');
    }
}