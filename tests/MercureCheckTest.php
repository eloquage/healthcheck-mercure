<?php

declare(strict_types=1);

use IllumaLaw\HealthCheckMercure\MercureCheck;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Spatie\Health\Enums\Status;

beforeEach(function () {
    config()->set('broadcasting.connections.mercure.url', null);
    config()->set('broadcasting.connections.mercure.public_url', null);
    config()->set('healthcheck-mercure.probe_topic', 'https://example.com/healthcheck');
    config()->set('healthcheck-mercure.timeout_seconds', 5);
    config()->set('healthcheck-mercure.subscriber_token', null);
    config()->set('healthcheck-mercure.required', false);
});

it('warns when mercure hub url is not configured', function () {
    $result = MercureCheck::new()->run();

    expect($result->status)->toEqual(Status::warning())
        ->and($result->notificationMessage)->toContain('not configured');
});

it('fails when the hub is required and its url is not configured', function () {
    config()->set('healthcheck-mercure.required', true);

    $result = MercureCheck::new()->run();

    expect($result->status)->toEqual(Status::failed());
});

it('succeeds when mercure hub is reachable', function () {
    config()->set('broadcasting.connections.mercure.url', 'http://mercure.test');

    Http::fake([
        'mercure.test*' => Http::response('ok', 200),
    ]);

    $result = MercureCheck::new()->run();

    Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
        && $request->url() === 'http://mercure.test?match=https%3A%2F%2Fexample.com%2Fhealthcheck'
        && $request->hasHeader('Accept', 'text/event-stream'));

    expect($result->status)->toEqual(Status::ok())
        ->and($result->meta)->toHaveKey('response_time_ms');
});

it('prefers the public hub url when both urls are configured', function () {
    config()->set('broadcasting.connections.mercure.url', 'http://internal-mercure.test');
    config()->set('broadcasting.connections.mercure.public_url', 'https://mercure.example.test');

    Http::fake([
        'mercure.example.test*' => Http::response('', 200),
    ]);

    $result = MercureCheck::new()->run();

    Http::assertSent(fn (Request $request): bool => str_starts_with($request->url(), 'https://mercure.example.test'));
    expect($result->status)->toEqual(Status::ok());
});

it('warns when mercure hub returns error', function () {
    config()->set('broadcasting.connections.mercure.url', 'http://mercure.test');

    Http::fake([
        'mercure.test*' => Http::response('error', 500),
    ]);

    $result = MercureCheck::new()->run();

    expect($result->status)->toEqual(Status::warning());
});

it('fails when the hub is required and returns an error', function () {
    config()->set('broadcasting.connections.mercure.url', 'http://mercure.test');
    config()->set('healthcheck-mercure.required', true);

    Http::fake([
        'mercure.test*' => Http::response('error', 503),
    ]);

    expect(MercureCheck::new()->run()->status)->toEqual(Status::failed());
});

it('warns when mercure hub is unreachable', function () {
    config()->set('broadcasting.connections.mercure.url', 'http://mercure.test');

    Http::fake([
        'mercure.test*' => function () {
            throw new Exception('Unreachable');
        },
    ]);

    $result = MercureCheck::new()->run();

    expect($result->status)->toEqual(Status::warning());
});

it('adds the configured subscriber token to the request', function () {
    config()->set('broadcasting.connections.mercure.url', 'http://mercure.test');
    config()->set('healthcheck-mercure.subscriber_token', 'test-token');

    Http::fake([
        'mercure.test*' => Http::response('', 200),
    ]);

    MercureCheck::new()->run();

    Http::assertSent(fn (Request $request): bool => $request->hasHeader('Authorization', 'Bearer test-token'));
});
