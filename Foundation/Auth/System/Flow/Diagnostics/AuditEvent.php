<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Diagnostics;

use DateTimeImmutable;

/**
 * Immutable audit record emitted by auth flows.
 */
final readonly class AuditEvent
{
    public string|null       $correlationId;
    public array             $context;
    public DateTimeImmutable $occurredAt;
    public string            $name;

    /**
     * @param array<string, scalar|null> $context
     */
    public function __construct(
        string            $name,
        DateTimeImmutable $occurredAt,
        array|null        $context = null,
        string|null       $correlationId = null
    )
    {
        $context             ??= [];
        $this->name          = $name;
        $this->occurredAt    = $occurredAt;
        $this->context       = $context;
        $this->correlationId = $correlationId;
    }

    public function withCorrelationId(string $correlationId) : self
    {
        return new self(
            name         : $this->name,
            occurredAt   : $this->occurredAt,
            context      : $this->context,
            correlationId: $correlationId
        );
    }
}
