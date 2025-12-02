<?php
// app/Providers/AppServiceProvider.php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(\App\Services\CurrencyService::class);
        $this->app->singleton(\App\Services\MidtransService::class);
    }

    public function boot()
    {
        //
    }
}