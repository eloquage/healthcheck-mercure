# illuma-law/healthcheck-mercure

Checks that the configured Mercure subscription endpoint accepts a short-lived SSE probe.

## Usage

```php
use IllumaLaw\HealthCheckMercure\MercureCheck;
use Spatie\Health\Facades\Health;

Health::checks([
    MercureCheck::new(),
]);
```

## Configuration

Publish config: `php artisan vendor:publish --tag="healthcheck-mercure-config"`

Options in `config/healthcheck-mercure.php`:
- `required`: make a missing or unreachable hub fail; otherwise it reports a warning.
- `probe_topic`: topic matcher used for the subscription probe.
- `timeout_seconds`: timeout for opening the subscription.
- `subscriber_token`: optional bearer token for protected topics.
