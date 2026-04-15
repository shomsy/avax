<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Diagnostics;

use Avax\Auth\System\Flow\Diagnostics\AuditEvent;

/**
 * Uses an event-context flag to preserve forensic evidence under legal hold.
 */
final readonly class ContextFlagAuditLegalHoldPolicy implements AuditLegalHoldPolicyInterface
{
    private string $contextKey;

    public function __construct(
        string $contextKey = 'legal_hold'
    )
    {
        $this->contextKey = $contextKey;
    }

    public function preserveSensitiveContext(AuditEvent $event) : bool
    {
        $value = $event->context[$this->contextKey] ?? null;

        return $value === true || $value === 1 || $value === '1';
    }
}
