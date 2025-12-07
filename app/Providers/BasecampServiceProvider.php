<?php

namespace App\Providers;

use App\Socialite\Basecamp\BasecampProvider;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Contracts\Factory;

class BasecampServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $socialite = $this->app->make(Factory::class);

        $socialite->extend('basecamp', function ($app) use ($socialite) {
            $config = $app['config']['services.basecamp'];

            return $socialite->buildProvider(BasecampProvider::class, $config);
        });
    }
}
