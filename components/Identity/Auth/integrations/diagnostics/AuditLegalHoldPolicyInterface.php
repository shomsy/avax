<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Integrations\Diagnostics;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;

/**
 * Decides whether export masking must be bypassed for preserved evidence.
 */
interface AuditLegalHoldPolicyInterface
{
    public function preserveSensitiveContext(AuditEvent $event) : bool;
}
