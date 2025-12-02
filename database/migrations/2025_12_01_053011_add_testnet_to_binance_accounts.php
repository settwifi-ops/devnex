<?php
// database/migrations/2024_01_01_000000_add_testnet_to_binance_accounts.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTestnetToBinanceAccounts extends Migration
{
    public function up()
    {
        Schema::table('user_binance_accounts', function (Blueprint $table) {
            $table->boolean('is_testnet')->default(true)->after('user_id');
            $table->string('environment')->default('testnet')->after('is_testnet');
        });
    }

    public function down()
    {
        Schema::table('user_binance_accounts', function (Blueprint $table) {
            $table->dropColumn(['is_testnet', 'environment']);
        });
    }
}