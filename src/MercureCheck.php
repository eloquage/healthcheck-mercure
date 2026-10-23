<?php

declare(strict_types=1);

namespace IllumaLaw\HealthCheckMercure;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;
use Throwable;

final class MercureCheck extends Check
{
    public function run(): Result
    {
        $publicUrl = config('broadcasting.connections.mercure.public_url');
        $fallbackUrl = config('broadcasting.connections.mercure.url');
        $hubUrl = is_string($publicUrl) && $publicUrl !== ''
            ? $publicUrl
            : (is_string($fallbackUrl) ? $fallbackUrl : '');

        if ($hubUrl === '') {
            return $this->unavailable(__('healthcheck-mercure::messages.not_configured'), 'Not configured');
        }

        $configuredTopic = config('healthcheck-mercure.probe_topic');
        $topic = is_string($configuredTopic) ? $configuredTopic : 'https://example.com/healthcheck';
        $configuredTimeout = config('healthcheck-mercure.timeout_seconds');
        $timeoutSeconds = max(1, is_numeric($configuredTimeout) ? (int) $configuredTimeout : 5);
        $configuredToken = config('healthcheck-mercure.subscriber_token');
        $subscriberToken = is_string($configuredToken) ? $configuredToken : '';
        $startedAt = microtime(true);

        try {
            $request = Http::timeout($timeoutSeconds)
                ->withOptions(['stream' => true])
                ->withHeaders(['Accept' => 'text/event-stream']);

            if ($subscriberToken !== '') {
                $request = $request->withToken($subscriberToken);
            }

            $response = $request->get($hubUrl, ['match' => $topic]);
            $status = $response->status();
            $this->closeResponseStream($response);
        } catch (Throwable $exception) {
            return $this->unavailable(__('healthcheck-mercure::messages.unreachable', [
                'message' => $exception->getMessage(),
            ]), 'Unreachable');
        }

        $responseTimeMs = (int) round((microtime(true) - $startedAt) * 1000);

        if ($status < 200 || $status >= 300) {
            $message = $status >= 500
                ? __('healthcheck-mercure::messages.server_error', ['code' => $status])
                : __('healthcheck-mercure::messages.client_error', ['code' => $status]);

            return $this->unavailable($message, 'HTTP '.$status);
        }

        return Result::make()
            ->ok(__('healthcheck-mercure::messages.connected'))
            ->shortSummary('Connected')
            ->meta(['response_time_ms' => $responseTimeMs]);
    }

    private function unavailable(string $message, string $summary): Result
    {
        $result = Result::make()->shortSummary($summary);

        if ((bool) config('healthcheck-mercure.required', false)) {
            return $result->failed($message);
        }

        return $result->warning($message);
    }

    private function closeResponseStream(Response $response): void
    {
        $response->toPsrResponse()->getBody()->close();
    }
}
