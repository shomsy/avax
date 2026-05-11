<?php

declare(strict_types=1);

namespace Avax\Components\API\ApiBlueprint\System\Flows\DeliverWebhook;

use Avax\Components\API\ApiBlueprint\System\Capabilities\Webhooks\WebhookSignature;
use Avax\Components\API\ApiBlueprint\System\Foundation\Failure\WebhookDeliveryFailed;
use Avax\Components\Operations\Resilience\System\PublicSurface\Resilience;

final readonly class DeliverWebhook
{
    /**
     * @param array<string, mixed> $payload
     *
     * @return array{url: string, event: string, delivered: bool, attempts: int, signature: string}
     */
    public function deliver(string $url, string $event, array $payload, string $secret, int $maxAttempts = 3, int $backoffMs = 1000) : array
    {
        $signature = WebhookSignature::generate($payload, $secret);

        $attempts  = 0;
        $delivered = false;

        $result = Resilience::retry(fn () => $this->sendRequest($url, $event, $payload, $signature))
            ->times($maxAttempts)
            ->backoff($backoffMs)
            ->run();

        $attempts  = $result->attempts;
        $delivered = $result->success;

        return [
            'url'       => $url,
            'event'     => $event,
            'delivered' => $delivered,
            'attempts'  => $attempts,
            'signature' => $signature,
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function sendRequest(string $url, string $event, array $payload, string $signature) : bool
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
            ],
        ];

        $context = stream_context_create($context);

        $result = file_get_contents($url, false, $context);

        if ($result === false) {
            throw new WebhookDeliveryFailed("Webhook request to {$url} failed");
        }

        return true;
    }
}
