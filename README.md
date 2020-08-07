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
| 3.*           | 7.0 and above | v3            |
| 2.*           | 5.5 - 6.*     | v3            |
| 1.1.*         | 5.5 - 6.*     | v2            |
| 1.0.*         | 5.0 - 5.4     | v2            |


## Configuration

> `config/mail.php`

```php
    'mailers' => [
        // ...
        'sendinblue' => [
            'transport' => 'sendinblue',
            'key_identifier' => env('SENDINBLUE_KEY_IDENTIFIER', 'api-key'), // api-key or partner-key
            'key' => env('SENDINBLUE_KEY'),
        ],
    ]
```

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
MAIL_MAILER=sendinblue
SENDINBLUE_KEY=your-access-key
```
