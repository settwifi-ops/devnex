<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('user_positions', function (Blueprint $table) {
            $table->decimal('max_profit_percentage', 8, 2)->default(0)->after('pnl_percentage');
        });
    }

    public function down()
    {
        Schema::table('user_positions', function (Blueprint $table) {
            $table->dropColumn('max_profit_percentage');
        });
    }
};