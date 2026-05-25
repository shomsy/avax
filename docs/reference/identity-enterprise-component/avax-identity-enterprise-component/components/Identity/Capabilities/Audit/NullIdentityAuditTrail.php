<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Capabilities\Audit;

final class NullIdentityAuditTrail implements IdentityAuditTrail
{
    public function record(IdentityEvent $event): void
    {
        // Intentionally empty: safe default for applications that do not configure audit storage yet.
    }
}
