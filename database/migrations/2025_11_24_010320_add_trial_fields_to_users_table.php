<?php
// database/migrations/2024_01_01_add_trial_fields_to_users_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('premium_ends_at')->nullable();
            $table->enum('subscription_tier', ['trial', 'premium'])->default('trial');
            $table->string('login_token')->nullable();
            $table->string('country_code')->default('ID');
            $table->boolean('is_trial_used')->default(false);
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'trial_ends_at',
                'premium_ends_at', 
                'subscription_tier',
                'login_token',
                'country_code',
                'is_trial_used'
            ]);
        });
    }
};