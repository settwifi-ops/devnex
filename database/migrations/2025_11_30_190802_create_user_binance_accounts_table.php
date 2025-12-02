<?php
// 📄 database/migrations/xxxx_create_user_binance_accounts_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_binance_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('api_key_encrypted'); // Disimpan encrypted
            $table->text('api_secret_encrypted'); // Disimpan encrypted
            $table->string('label')->nullable(); // "Main Account", "Test Account", dll
            $table->boolean('is_active')->default(true);
            $table->json('permissions')->nullable(); // Store API permissions
            $table->decimal('balance_snapshot', 15, 8)->default(0);
            $table->timestamp('last_verified')->nullable();
            $table->string('verification_status')->default('pending'); // pending, verified, failed
            $table->timestamps();
            
            $table->unique(['user_id', 'label']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_binance_accounts');
    }
};