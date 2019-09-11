# laravel-sendinblue

Laravel's mail transport for SendinBlue

[![Build Status](https://travis-ci.org/agence-webup/laravel-sendinblue.svg?branch=master)](https://travis-ci.org/agence-webup/laravel-sendinblue)

## Installation

```shell
composer require webup/laravel-sendinblue
```

**Compatibility**

| Version       | Laravel       | Sendiblue Api |
| ------------- | ------------- | ------------- |
| 2.*           | 5.5 and above | v3            |
| 1.1.*         | 5.5 and above | v2            |
| 1.0.*         | 5.0 - 5.4     | v2            |


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
        // api-key or partner-key
        'key_identifier' => env('SENDINBLUE_KEY_IDENTIFIER', 'api-key'),
        'key' => env('SENDINBLUE_KEY'),
    ],
```

> `.env`

```
MAIL_DRIVER=sendinblue
SENDINBLUE_KEY=your-access-key
```
