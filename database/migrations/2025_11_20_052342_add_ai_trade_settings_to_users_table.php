<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('ai_trade_global_enabled')->default(false);
            $table->json('ai_trade_preferences')->nullable(); // untuk setting tambahan
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['ai_trade_global_enabled', 'ai_trade_preferences']);
        });
    }
};