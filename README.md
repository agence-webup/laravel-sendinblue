# laravel-sendinblue

Laravel's mail transport for SendinBlue

[![Build Status](https://travis-ci.org/agence-webup/laravel-sendinblue.svg?branch=master)](https://travis-ci.org/agence-webup/laravel-sendinblue)

## Summary
- [laravel-sendinblue](#laravel-sendinblue)
  - [Summary](#summary)
  - [Installation](#installation)
  - [Configuration](#configuration)
  - [Regarding additional features](#regarding-additional-features)

## Installation

```shell
composer require webup/laravel-sendinblue
```

**Compatibility**

| Version       | Laravel       | Sendinblue Api |
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

# if you need to set the guzzle proxy config
# those are example values
HTTP_PROXY="tcp://localhost:8125"
HTTPS_PROXY="tcp://localhost:9124"
NO_PROXY=.mit.edu,foo.com
```

## Regarding additional features

This library aims to provide a Laravel-compatible interface for SendInBlue. That means it cannot provide features outside of the scope of Laravel transporters.

If you need features like tagging, or specific SendInBlue beta SMTP Template batching, you should directly use the official SendInBlue PHP library.
