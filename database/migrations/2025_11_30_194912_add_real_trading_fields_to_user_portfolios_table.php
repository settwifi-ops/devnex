<?php
// 📄 database/migrations/xxxx_add_real_trading_fields_to_user_portfolios_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('user_portfolios', function (Blueprint $table) {
            // Field untuk real trading
            $table->boolean('real_trading_active')->default(false);
            $table->boolean('real_trading_enabled')->default(false);
            $table->decimal('real_balance', 15, 8)->default(0);
            $table->decimal('real_equity', 15, 8)->default(0);
            $table->decimal('real_realized_pnl', 15, 8)->default(0);
            $table->decimal('real_floating_pnl', 15, 8)->default(0);
            $table->timestamp('binance_connected_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('user_portfolios', function (Blueprint $table) {
            $table->dropColumn([
                'real_trading_active',
                'real_trading_enabled', 
                'real_balance',
                'real_equity',
                'real_realized_pnl',
                'real_floating_pnl',
                'binance_connected_at'
            ]);
        });
    }
};