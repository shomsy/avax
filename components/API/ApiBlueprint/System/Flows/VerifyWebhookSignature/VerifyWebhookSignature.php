<?php

declare(strict_types=1);

namespace Avax\Components\API\ApiBlueprint\System\Flows\VerifyWebhookSignature;

use Avax\Components\API\ApiBlueprint\System\Capabilities\Webhooks\WebhookSignature;

final readonly class VerifyWebhookSignature
{
    /**
     * @param array<string, mixed> $payload
     *
     * @return array{valid: bool, signature: string, expected: string}
     */
    public function verify(array $payload, string $signature, string $secret) : array
    {
        $valid = WebhookSignature::verify($payload, $signature, $secret);

        return [
            'valid'     => $valid,
            'signature' => $signature,
            'expected'  => WebhookSignature::generate($payload, $secret),
        ];
    }
}
