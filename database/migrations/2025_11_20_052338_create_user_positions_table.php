<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('portfolio_id')->constrained('user_portfolios')->onDelete('cascade'); // KOLOM BARU
            $table->foreignId('ai_decision_id')->constrained()->onDelete('cascade');
            $table->string('symbol');
            $table->enum('position_type', ['LONG', 'SHORT'])->default('LONG'); // KOLOM BARU
            $table->decimal('qty', 15, 8); // quantity yang dibeli
            $table->decimal('avg_price', 15, 8); // average entry price
            $table->decimal('current_price', 15, 8);
            $table->decimal('investment', 15, 2); // jumlah USD yang diinvest
            $table->decimal('floating_pnl', 15, 2)->default(0);
            $table->decimal('pnl_percentage', 8, 2)->default(0);
            $table->decimal('take_profit', 15, 8)->nullable(); // KOLOM BARU
            $table->decimal('stop_loss', 15, 8)->nullable(); // KOLOM BARU
            $table->enum('status', ['OPEN', 'CLOSED'])->default('OPEN');
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            
            // Index untuk performa
            $table->index(['user_id', 'status']);
            $table->index(['portfolio_id', 'status']); // INDEX BARU
            $table->index(['status', 'position_type']); // INDEX BARU
            $table->index('symbol');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_positions');
    }
};