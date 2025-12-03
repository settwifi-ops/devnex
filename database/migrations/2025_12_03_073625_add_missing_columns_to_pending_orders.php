<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('pending_orders', function (Blueprint $table) {

            // Harga-harga SL/TP dan entry
            $table->decimal('entry_price', 16, 8)->nullable()->after('limit_price');
            $table->decimal('stop_loss_price', 16, 8)->nullable()->after('entry_price');
            $table->decimal('take_profit_price', 16, 8)->nullable()->after('stop_loss_price');

            // ID order Binance
            $table->string('main_order_id')->nullable()->after('binance_order_id');
            $table->string('sl_order_id')->nullable()->after('main_order_id');
            $table->string('tp_order_id')->nullable()->after('sl_order_id');

            // Status tambahan
            $table->timestamp('filled_at')->nullable()->after('expires_at');
            $table->timestamp('closed_at')->nullable()->after('filled_at');

            // Flags
            $table->boolean('is_active')->default(true)->after('status');
            $table->boolean('is_manual')->default(false)->after('is_active');

            // Error log
            $table->text('error_message')->nullable()->after('notes');
        });
    }

    public function down()
    {
        Schema::table('pending_orders', function (Blueprint $table) {
            $table->dropColumn([
                'entry_price',
                'stop_loss_price',
                'take_profit_price',
                'main_order_id',
                'sl_order_id',
                'tp_order_id',
                'filled_at',
                'closed_at',
                'is_active',
                'is_manual',
                'error_message',
            ]);
        });
    }

};
