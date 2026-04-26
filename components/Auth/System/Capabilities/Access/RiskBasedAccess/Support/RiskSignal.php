<?php

declare(strict_types=1);

namespace components\Auth\System\Capabilities\Access\RiskBasedAccess\Support;

use DateTimeImmutable;

/**
 * Stored security signal for a user.
 */
final readonly class RiskSignal
{
    /**
     * @param array<string, scalar|null> $context
     */
    public function __construct(public int $userId, public string $name, public DateTimeImmutable $occurredAt, public array $context = []) {}
}
