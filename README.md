# Healthcheck mercure for Laravel

[![Tests](https://github.com/eloquage/healthcheck-mercure/actions/workflows/run-tests.yml/badge.svg)](https://github.com/eloquage/healthcheck-mercure/actions)
[![Packagist License](https://img.shields.io/badge/Licence-MIT-blue)](http://choosealicense.com/licenses/mit/)
[![Latest Stable Version](https://img.shields.io/packagist/v/illuma-law/healthcheck-mercure?label=Version)](https://packagist.org/packages/illuma-law/healthcheck-mercure)

A focused mercure health check for Spatie's [Laravel Health](https://spatie.be/docs/laravel-health/v1/introduction) package.

This package provides a simple, direct health check to verify that your application's Mercure hub is reachable and responding to HTTP requests.

## Features

- **Reachability Check:** Verifies that your Laravel application can successfully connect to the configured Mercure hub URL.
- **HTTP Status Validation:** Ensures the Mercure hub returns a successful HTTP status code (2xx).
- **Configurable Timeout:** Respects your configured timeout settings to prevent the health check from hanging.

## Installation

Require this package with composer:

```shell
composer require illuma-law/healthcheck-mercure
```

## Usage & Integration

Register the check inside your application's health service provider (e.g. `AppServiceProvider` or a dedicated `HealthServiceProvider`), alongside your other Spatie Laravel Health checks:

### Basic Registration

```php
use IllumaLaw\HealthCheckMercure\MercureCheck;
use Spatie\Health\Facades\Health;

Health::checks([
    MercureCheck::new(),
]);
```

### Expected Result States

The check interacts with the Spatie Health dashboard and JSON endpoints using these states:

- **Ok:** Mercure hub is reachable and returned a 2xx status code.
- **Failed:** Mercure hub was unreachable, timed out, or returned an error status code (4xx/5xx).

## Testing

Run the test suite:

```shell
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
