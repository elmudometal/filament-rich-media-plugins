# A collection of standalone Filament rich editor plugins (LinkButton, Image) compatible with v4 and v5.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/elmudodev/filament-rich-media-plugins.svg?style=flat-square)](https://packagist.org/packages/elmudodev/filament-rich-media-plugins)
[![GitHub Tests Action Status](https://github.com/spatie/package-filament-rich-media-plugins-laravel/actions/workflows/run-tests.yml/badge.svg)](https://github.com/elmudometal/filament-rich-media-plugins/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://github.com/spatie/package-filament-rich-media-plugins-laravel/actions/workflows/fix-php-code-style-issues.yml/badge.svg)](https://github.com/elmudometal/filament-rich-media-plugins/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/elmudodev/filament-rich-media-plugins.svg?style=flat-square)](https://packagist.org/packages/elmudodev/filament-rich-media-plugins)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/filament-rich-media-plugins.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/filament-rich-media-plugins)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require elmudo-dev/filament-rich-media-plugins
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="filament-rich-media-plugins-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="filament-rich-media-plugins-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="filament-rich-media-plugins-views"
```

## Usage

```php
$filamentRichMediaPlugins = new ElmudoDev\FilamentRichMediaPlugins();
echo $filamentRichMediaPlugins->echoPhrase('Hello, ElmudoDev!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Hernan Soto](https://github.com/elmudometal)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
