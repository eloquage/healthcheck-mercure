<?php

declare(strict_types=1);

use IllumaLaw\HealthCheckMercure\HealthcheckMercureServiceProvider;
use Illuminate\Support\ServiceProvider;

it('registers the config publish path under the correct tag', function () {
    $provider = app()->getProvider(HealthcheckMercureServiceProvider::class);
    $source = dirname((new ReflectionClass($provider))->getFileName()).'/../config/healthcheck-mercure.php';

    expect(ServiceProvider::pathsToPublish($provider::class, 'healthcheck-mercure-config'))
        ->toBe([$source => config_path('healthcheck-mercure.php')]);
});

it('loads config with the correct default', function () {
    expect(config('healthcheck-mercure.required'))->toBeFalse();
});
