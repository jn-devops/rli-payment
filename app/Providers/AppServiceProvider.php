<?php

namespace App\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use App\Interfaces\PaymentGateway;
use App\Classes\AUBPaymate;
use Lorisleiva\Actions\Facades\Actions;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PaymentGateway::class, function (Application $application) {
            return new AUBPaymate();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            Actions::registerCommands('app/Actions');
        }
    }
}
