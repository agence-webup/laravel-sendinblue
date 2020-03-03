<?php

namespace Webup\LaravelSendinBlue;

use Illuminate\Mail\MailManager;
use Illuminate\Support\ServiceProvider;
use SendinBlue\Client\Api\SMTPApi;
use SendinBlue\Client\Configuration;
use GuzzleHttp\Client as GuzzleClient;

class SendinBlueServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app[MailManager::class]->extend('sendinblue', function ($app) {
            return new SendinBlueTransport($this->app->make(SMTPApi::class));
        });
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(SMTPApi::class, function ($app) {
            $config = Configuration::getDefaultConfiguration()->setApiKey($app['config']['services.sendinblue.key_identifier'], $app['config']['services.sendinblue.key']);

            return new SMTPApi(
                new GuzzleClient,
                $config
            );
        });
    }
}
