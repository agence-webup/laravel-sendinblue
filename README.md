# laravel-sendinblue

Laravel's mail transport for SendinBlue

[![Build Status](https://travis-ci.org/agence-webup/laravel-sendinblue.svg?branch=master)](https://travis-ci.org/agence-webup/laravel-sendinblue)

## Summary
- [laravel-sendinblue](#laravel-sendinblue)
  - [Summary](#summary)
  - [Installation](#installation)
  - [Configuration](#configuration)
  - [Usage with Sendinblue templates](#usage-in-mailable-with-template-id)
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

## Usage in Mailable with Template Id

Using the `sendinblue()` method you may pass extra fields listed below. All fields are optional:

- `template_id` (integer)
- `tags` (array)
- `params` (array)

If you want to use the subject defined in the template, it's necessary to pass
the `SendinBlueTransport::USE_TEMPLATE_SUBJECT` placeholder in the `subject()`. You may as well override the subject
text here. Otherwise, without the `subject()` method, the subject will be derived from the class name.

Mailable requires a view - pass an empty array in the `view()` method.

```php
<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Webup\LaravelSendinBlue\SendinBlue; // <- you need this
use Webup\LaravelSendinBlue\SendinBlueTransport; // <- you need this

class MyMailable extends Mailable
{
    use Queueable;
    use SerializesModels;
    use SendinBlue; // <- you need this

    // ...

    public function build()
    {
        return $this
            ->view([])
            ->subject(SendinBlueTransport::USE_TEMPLATE_SUBJECT) // use template subject
            // ->subject('My own subject') // subject overridden
            ->sendinblue(
                [
                    'template_id' => 84,
                    'tags'        => ['offer'],
                    'params'      => [
                        'FIRSTNAME' => 'John',
                        'LINK'      => 'https://www.example.com',
                        'AMOUNT'    => '29',
                    ],
                ]
            );
    }
}
```

Params are accessbile in the SendinBlue template as:

- `{{ params.FIRSTNAME }}`
- `{{ params.LINK }}`
- `{{ params.AMOUNT }}`

You may as well use param substitution in the subject field, eg.:  
`{{ params.FIRSTNAME }}, forgot your password?!`

Note: Do not use hyphens '-' in the variable names. `{{ params.FIRST_NAME }}` will work properly, but `{{ params.FIRST-NAME }}` will fail. Source: https://github.com/sendinblue/APIv3-php-library/issues/151

## Regarding additional features

This library aims to provide a Laravel-compatible interface for SendInBlue along with support for template ids, tags and params.

If you need features like specific SendInBlue beta SMTP Template batching, you should directly use the official SendInBlue PHP library.
