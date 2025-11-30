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
        Schema::table('signals', function (Blueprint $table) {
            $table->decimal('open_interest', 20, 2)->nullable();
            $table->decimal('oi_change', 10, 2)->nullable();
            $table->decimal('funding_rate', 10, 6)->nullable();
            $table->text('summary')->nullable();
        });
    }

};
