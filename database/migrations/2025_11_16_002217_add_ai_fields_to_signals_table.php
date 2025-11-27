<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('signals', function (Blueprint $table) {
            $table->boolean('is_active_signal')->default(false);
            $table->integer('last_appearance_count')->default(0);
            $table->float('ai_probability')->nullable();
            $table->text('ai_summary')->nullable();
            $table->float('support_level')->nullable();
            $table->float('resistance_level')->nullable();
            $table->string('liquidity_position')->nullable();
            $table->string('market_structure')->nullable();
            $table->string('trend_power')->nullable();
            $table->string('momentum_category')->nullable();
            $table->string('funding_direction')->nullable();
            $table->string('whale_behavior')->nullable();
        });
    }

    public function down()
    {
        Schema::table('signals', function (Blueprint $table) {
            $table->dropColumn([
                'is_active_signal',
                'last_appearance_count',
                'ai_probability',
                'ai_summary',
                'support_level',
                'resistance_level',
                'liquidity_position',
                'market_structure',
                'trend_power',
                'momentum_category',
                'funding_direction',
                'whale_behavior',
            ]);
        });
    }
};
