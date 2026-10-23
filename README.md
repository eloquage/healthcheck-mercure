# Healthcheck Mercure

[![Tests](https://github.com/eloquage/healthcheck-mercure/actions/workflows/run-tests.yml/badge.svg)](https://github.com/eloquage/healthcheck-mercure/actions)
[![Latest Version](https://img.shields.io/packagist/v/illuma-law/healthcheck-mercure)](https://packagist.org/packages/illuma-law/healthcheck-mercure)
[![License](https://img.shields.io/packagist/l/illuma-law/healthcheck-mercure)](LICENSE.md)

![Mercure connection health check illustration](art/banner.png)

A Laravel Health check that opens a short-lived subscription request to your Mercure hub and checks whether the hub accepts it.

## Requirements

- PHP 8.3 or later
- Laravel 12 or 13
- [Spatie Laravel Health](https://spatie.be/docs/laravel-health)
- A Mercure hub configured as a `mercure` broadcasting connection

## Install

```bash
composer require illuma-law/healthcheck-mercure
```

## Configure

Set the `mercure` connection in `config/broadcasting.php`:

```php
'connections' => [
    'mercure' => [
        'url' => env('MERCURE_URL'),
        'public_url' => env('MERCURE_PUBLIC_URL'),
    ],
],
```

The check prefers `public_url` and falls back to `url`. Publish package settings if you want to customize the probe:

```bash
php artisan vendor:publish --tag=healthcheck-mercure-config
```

Settings in `config/healthcheck-mercure.php`:

- `required`: when `true`, a missing or unreachable hub fails the check; otherwise it warns. Default: `false`.
- `probe_topic`: topic matcher used for the subscription. Default: `https://example.com/healthcheck`.
- `timeout_seconds`: time allowed to open the subscription. Default: `5`.
- `subscriber_token`: optional bearer token for protected topics.

Environment variables are `HEALTH_MERCURE_REQUIRED`, `HEALTH_MERCURE_PROBE_TOPIC`, `HEALTH_MERCURE_TIMEOUT_SECONDS`, and `HEALTH_MERCURE_SUBSCRIBER_TOKEN`.

## Use

Register the check with your Laravel Health checks:

```php
use IllumaLaw\HealthCheckMercure\MercureCheck;
use Spatie\Health\Facades\Health;

Health::checks([
    MercureCheck::new(),
]);
```

The check uses a streaming HTTP request and closes the connection after reading the response status, so an open server-sent events stream does not wait for an event or time out. It checks the subscriber request path; it does not publish an update or prove end-to-end delivery. For transport readiness, Mercure recommends its local admin endpoint at [`/mercure/health/ready`](https://mercure.rocks/docs/production/health-monitoring).

## Tests

```bash
composer install
composer test
composer analyse
```

## License

MIT. See [LICENSE.md](LICENSE.md).
