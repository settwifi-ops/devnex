<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sectors', function (Blueprint $table) {
            $table->string('sector_id')->primary();
            $table->string('name');
            $table->double('market_cap')->nullable();
            $table->double('market_cap_change_24h')->nullable();
            $table->double('volume_24h')->nullable();
            $table->json('top_3_coins')->nullable();
            $table->json('top_3_logos')->nullable();
            $table->dateTime('updated_at_api')->nullable();
            $table->timestamps(); // created_at, updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sectors');
    }
};
