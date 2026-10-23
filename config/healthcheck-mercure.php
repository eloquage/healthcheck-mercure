<?php

declare(strict_types=1);

return [
    'required' => env('HEALTH_MERCURE_REQUIRED', false),
    'probe_topic' => env('HEALTH_MERCURE_PROBE_TOPIC', 'https://example.com/healthcheck'),
    'timeout_seconds' => env('HEALTH_MERCURE_TIMEOUT_SECONDS', 5),
    'subscriber_token' => env('HEALTH_MERCURE_SUBSCRIBER_TOKEN'),
];
