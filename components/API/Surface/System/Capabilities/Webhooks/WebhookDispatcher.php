<?php

declare(strict_types=1);

namespace Avax\Components\API\Surface\System\Capabilities\Webhooks;

final class WebhookDispatcher
{
    /**
     * @var array<string, list<string>>
     */
    private array $subscriptions = [];

    private WebhookRetryPolicy $retryPolicy;

    public function __construct(?WebhookRetryPolicy $retryPolicy = null)
    {
        $this->retryPolicy = $retryPolicy ?? new WebhookRetryPolicy();
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
    private function deliver(string $url, string $event, array $payload, string $signature) : array
    {
        $attempts  = 0;
        $delivered = false;

        foreach ($this->retryPolicy->attempts() as $_) {
            $attempts++;

            $result = $this->sendHttpRequest($url, $event, $payload, $signature);

            if ($result) {
                $delivered = true;
                break;
            }
        }

        return [
            'url'       => $url,
            'delivered' => $delivered,
            'attempts'  => $attempts,
        ];
    }

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
                'timeout' => $this->retryPolicy->timeout(),
            ],
        ];

        $context = stream_context_create($context);
        $result  = @file_get_contents($url, false, $context);

        return $result !== false;
    }

    /**
     * @return array<string, list<string>>
     */
    public function subscriptions() : array
    {
        return $this->subscriptions;
    }
}
