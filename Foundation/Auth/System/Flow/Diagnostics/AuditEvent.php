<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Diagnostics;

use DateTimeImmutable;

/**
 * Immutable audit record emitted by auth flows.
 */
final readonly class AuditEvent
{
    /**
     * @param array<string, scalar|null> $context
     */
    public function __construct(
        public string            $name,
        public DateTimeImmutable $occurredAt,
        public array             $context = []
    ) {}
}
