# laravel-sendinblue

Laravel's mail transport for SendinBlue

[![Build Status](https://travis-ci.org/agence-webup/laravel-sendinblue.svg?branch=master)](https://travis-ci.org/agence-webup/laravel-sendinblue)

## Installation

```shell
composer require webup/laravel-sendinblue
```

## Provider

> `config/app.php`

```php
    'providers' => [
        Webup\LaravelSendinBlue\SendinBlueServiceProvider::class,
    ],
```

## Configuration

> `config/services.php`

```php
    'sendinblue' => [
       'url' => 'https://api.sendinblue.com/v2.0',
       'key' => env('SENDINBLUE_KEY'),
    ],
```

> `.env`

```
MAIL_DRIVER=sendinblue
SENDINBLUE_KEY=your-access-key
```
