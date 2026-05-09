<?php

declare(strict_types=1);

namespace Avax\Components\API\ApiBlueprint\System\Capabilities\Webhooks;

final readonly class WebhookSignature
{
    /**
     * @param array<string, mixed> $payload
     */
    public static function verify(array $payload, string $signature, string $secret) : bool
    {
        $expected = self::generate($payload, $secret);

        return hash_equals($expected, $signature);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function generate(array $payload, string $secret) : string
    {
        $body = json_encode($payload, JSON_THROW_ON_ERROR);

        return hash_hmac('sha256', $body, $secret);
    }

    public static function headerName() : string
    {
        return 'X-Webhook-Signature';
    }

    public static function eventHeaderName() : string
    {
        return 'X-Webhook-Event';
    }
}
