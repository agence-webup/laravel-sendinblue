<?php

namespace Webup\LaravelSendinBlue;

use Illuminate\Support\ServiceProvider;
use Sendinblue\Mailin;

class SendinBlueServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind(Mailin::class, function ($app) {
            return new Mailin($app['config']['services.sendinblue.url'], $app['config']['services.sendinblue.key']);
        });

        $this->app['swift.transport']->extend('sendinblue', function ($app) {
            return new SendinBlueTransport($app[Mailin::class]);
        });
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
    }
}
