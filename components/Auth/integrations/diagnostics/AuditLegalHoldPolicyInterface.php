<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Diagnostics;

use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;

/**
 * Decides whether export masking must be bypassed for preserved evidence.
 */
interface AuditLegalHoldPolicyInterface
{
    public function preserveSensitiveContext(AuditEvent $event) : bool;
}
