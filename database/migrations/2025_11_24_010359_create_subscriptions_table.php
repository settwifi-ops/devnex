<?php
// database/migrations/2024_01_01_create_subscriptions_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider'); // midtrans
            $table->string('subscription_id'); // order_id dari Midtrans
            $table->string('status'); // pending, active, canceled, expired
            $table->string('plan'); // monthly, 6months, yearly
            $table->integer('amount_idr'); // harga dalam IDR
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['subscription_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
};