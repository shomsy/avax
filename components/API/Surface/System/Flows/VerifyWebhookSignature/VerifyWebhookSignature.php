<?php

declare(strict_types=1);

namespace Avax\Components\API\Surface\System\Flows\VerifyWebhookSignature;

use Avax\Components\API\Surface\System\Capabilities\Webhooks\WebhookSignature;

final readonly class VerifyWebhookSignature
{
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
