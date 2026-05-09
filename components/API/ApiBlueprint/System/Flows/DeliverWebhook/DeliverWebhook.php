<?php

declare(strict_types=1);

namespace Avax\Components\API\ApiBlueprint\System\Flows\DeliverWebhook;

use Avax\Components\API\ApiBlueprint\System\Capabilities\Webhooks\WebhookRetryPolicy;
use Avax\Components\API\ApiBlueprint\System\Capabilities\Webhooks\WebhookSignature;

final readonly class DeliverWebhook
{
    /**
     * @param array<string, mixed> $payload
     *
     * @return array{url: string, event: string, delivered: bool, attempts: int, signature: string}
     */
    public function deliver(string $url, string $event, array $payload, string $secret, ?WebhookRetryPolicy $retryPolicy = null) : array
    {
        $retryPolicy = $retryPolicy ?? new WebhookRetryPolicy();
        $signature   = WebhookSignature::generate($payload, $secret);

        $attempts  = 0;
        $delivered = false;

        foreach ($retryPolicy->attempts() as $_) {
            $attempts++;

            $result = $this->sendRequest($url, $event, $payload, $signature);

            if ($result) {
                $delivered = true;
                break;
            }
        }

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

        return @file_get_contents($url, false, $context) !== false;
    }
}
