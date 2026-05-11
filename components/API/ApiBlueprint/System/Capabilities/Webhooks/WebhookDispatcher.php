<?php

declare(strict_types=1);

namespace Avax\Components\API\ApiBlueprint\System\Capabilities\Webhooks;

use Avax\Components\API\ApiBlueprint\System\Foundation\Failure\WebhookDeliveryFailed;
use Avax\Components\Operations\Resilience\System\PublicSurface\Resilience;

final class WebhookDispatcher
{
    /**
     * @var array<string, list<string>>
     */
    private array $subscriptions = [];

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<int, array{url: string, delivered: bool, attempts: int}>
     */
    public function dispatch(string $event, array $payload) : array
    {
        $urls    = $this->subscriptions[$event] ?? [];
        $results = [];

        foreach ($urls as $url) {
            $signature = WebhookSignature::generate($payload, 'secret');
            $result    = $this->deliver($url, $event, $payload, $signature);
            $results[] = $result;
        }

        return $results;
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array{url: string, delivered: bool, attempts: int}
     */
    private function deliver(string $url, string $event, array $payload, string $signature, int $maxAttempts = 3, int $backoffMs = 1000) : array
    {
        $result = Resilience::retry(fn () => $this->sendHttpRequest($url, $event, $payload, $signature))
            ->times($maxAttempts)
            ->backoff($backoffMs)
            ->run();

        return [
            'url'       => $url,
            'delivered' => $result->success,
            'attempts'  => $result->attempts,
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function sendHttpRequest(string $url, string $event, array $payload, string $signature) : bool
    {
        $headers = [
            'Content-Type: application/json',
            'X-Webhook-Event: ' . $event,
            'X-Webhook-Signature: ' . $signature,
        ];

        $context = [
            'http' => [
                'method'  => 'POST',
                'header'  => $headers,
                'content' => json_encode($payload, JSON_THROW_ON_ERROR),
                'timeout' => 10,
            ],
        ];

        $context = stream_context_create($context);
        $result  = file_get_contents($url, false, $context);

        if ($result === false) {
            throw new WebhookDeliveryFailed("Webhook request to {$url} failed");
        }

        return true;
    }

    public function subscribe(string $event, string $url) : self
    {
        if (! isset($this->subscriptions[$event])) {
            $this->subscriptions[$event] = [];
        }

        $this->subscriptions[$event][] = $url;

        return $this;
    }

    public function unsubscribe(string $event, string $url) : self
    {
        if (isset($this->subscriptions[$event])) {
            $this->subscriptions[$event] = array_values(
                array_filter($this->subscriptions[$event], fn (string $u) : bool => $u !== $url),
            );
        }

        return $this;
    }

    /**
     * @return array<string, list<string>>
     */
    public function subscriptions() : array
    {
        return $this->subscriptions;
    }
}
