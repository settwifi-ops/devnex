<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // 📄 database/migrations/xxxx_add_real_trading_subscription_to_users_table.php
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('real_trading_subscribed')->default(false);
            $table->timestamp('real_trading_subscription_ends')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
