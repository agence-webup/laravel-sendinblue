<?php

namespace Webup\LaravelSendinBlue;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Mail\MailManager;
use Illuminate\Support\ServiceProvider;
use SendinBlue\Client\Api\TransactionalEmailsApi;
use SendinBlue\Client\Configuration;

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
            return new SendinBlueTransport($this->app->make(TransactionalEmailsApi::class));
        });
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(TransactionalEmailsApi::class, function ($app) {
            $config = Configuration::getDefaultConfiguration()->setApiKey($app['config']['services.sendinblue.key_identifier'], $app['config']['services.sendinblue.key']);

            return new TransactionalEmailsApi(
                new GuzzleClient,
                $config
            );
        });
    }
}
