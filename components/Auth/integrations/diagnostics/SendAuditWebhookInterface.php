<?php

declare(strict_types=1);

namespace Avax\Components\Auth\Integrations\Diagnostics;

interface SendAuditWebhookInterface
{
    /**
     * @param array<string, mixed> $payload
     */
    public function send(array $payload) : void;
}
