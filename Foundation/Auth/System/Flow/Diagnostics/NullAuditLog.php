<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Diagnostics;

/**
 * Default audit sink when external diagnostics are not configured.
 */
final class NullAuditLog implements AuditLogInterface
{
    public function record(AuditEvent $event) : void {}
}
