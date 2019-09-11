<?php

namespace Webup\LaravelSendinBlue;

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
        $this->app['swift.transport']->extend('sendinblue', function ($app) {
            return new SendinBlueTransport($app[SMTPApi::class]);
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
            // $config = SendinBlue\Client\Configuration::getDefaultConfiguration()->setApiKeyPrefix('api-key', 'Bearer');

            return new SMTPApi(
                new GuzzleClient,
                $config
            );
        });
    }
}
