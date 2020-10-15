<?php

namespace Dbfun\JwtApi;

use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class JwtApiServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/jwtapi.php', 'jwtapi');

        // Register the service the package provides.
        $this->app->singleton('jwtapi', function ($app) {
            return new JwtApi;
        });

        Passport::tokensExpireIn(Carbon::now()->addSeconds(config("jwtapi.accessTokenTtl")));
        Passport::refreshTokensExpireIn(Carbon::now()->addSeconds(config("jwtapi.refreshTokenTtl")));
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['jwtapi'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__ . '/../config/jwtapi.php' => config_path('jwtapi.php'),
        ], 'jwtapi.config');
    }
}
