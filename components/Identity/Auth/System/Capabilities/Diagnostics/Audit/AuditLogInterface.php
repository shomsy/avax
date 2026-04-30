<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit;

/**
 * Sink for auth audit events.
 */
interface AuditLogInterface
{
    public function record(AuditEvent $event) : void;
}
