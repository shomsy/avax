<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit;

use DateTimeImmutable;

/**
 * Immutable audit record emitted by auth flows.
 */
final readonly class AuditEvent
{
    /** @var array<string, scalar|null> */
    public array $context;

    /**
     * @param array<string, scalar|null> $context
     */
    public function __construct(
        public string $name,
        public DateTimeImmutable $occurredAt,
        array $context = null,
        public string|null $correlationId = null,
    ) {
        $context ??= [];
        $this->context = $context;
    }

    public function withCorrelationId(string $correlationId): self
    {
        return new self(
            name         : $this->name,
            occurredAt   : $this->occurredAt,
            context      : $this->context,
            correlationId: $correlationId,
        );
    }
}
