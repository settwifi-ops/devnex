<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ai_decisions', function (Blueprint $table) {
            $table->id();
            $table->string('symbol'); // BTCUSDT, ETHUSDT, dll
            $table->enum('action', ['BUY', 'SELL', 'HOLD']);
            $table->decimal('confidence', 5, 2); // 0.00 - 100.00
            $table->decimal('price', 15, 8); // harga saat keputusan
            $table->text('explanation');
            $table->json('market_data')->nullable(); // snapshot data analisis
            $table->boolean('executed')->default(false);
            $table->timestamp('decision_time')->useCurrent();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ai_decisions');
    }
};