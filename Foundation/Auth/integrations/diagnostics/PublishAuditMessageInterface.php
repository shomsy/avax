<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Diagnostics;

interface PublishAuditMessageInterface
{
    /**
     * @param array<string, mixed> $message
     */
    public function publish(string $topic, array $message) : void;
}
