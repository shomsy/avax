<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit;

/**
 * Default audit sink when external diagnostics are not configured.
 */
final class NullAuditLog implements AuditLogInterface
{
    public function record(AuditEvent $auditEvent) : void {}
}
