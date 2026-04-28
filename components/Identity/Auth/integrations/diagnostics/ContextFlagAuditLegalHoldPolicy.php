<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Integrations\Diagnostics;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;

/**
 * Uses an event-context flag to preserve forensic evidence under legal hold.
 */
final readonly class ContextFlagAuditLegalHoldPolicy implements AuditLegalHoldPolicyInterface
{
    public function __construct(private string $contextKey = 'legal_hold') {}

    public function preserveSensitiveContext(AuditEvent $event) : bool
    {
        $value = $event->context[$this->contextKey] ?? null;

        return $value === true || $value === 1 || $value === '1';
    }
}
