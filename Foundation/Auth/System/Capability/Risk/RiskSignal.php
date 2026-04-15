<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Risk;

use DateTimeImmutable;

/**
 * Stored security signal for a user.
 */
final readonly class RiskSignal
{
    public array             $context;
    public DateTimeImmutable $occurredAt;
    public string            $name;
    public int               $userId;

    /**
     * @param array<string, scalar|null> $context
     */
    public function __construct(
        int               $userId,
        string            $name,
        DateTimeImmutable $occurredAt,
        array             $context = []
    )
    {
        $this->userId     = $userId;
        $this->name       = $name;
        $this->occurredAt = $occurredAt;
        $this->context    = $context;
    }
}
