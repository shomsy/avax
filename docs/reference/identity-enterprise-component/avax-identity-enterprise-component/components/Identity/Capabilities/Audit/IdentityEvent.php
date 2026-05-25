<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Capabilities\Audit;

use DateTimeImmutable;

final readonly class IdentityEvent
{
    /** @param array<string, scalar|null> $context */
    public function __construct(private string $name, private DateTimeImmutable $occurredAt, private array $context = []) {}

    public function name(): string
    {
        return $this->name;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    /** @return array<string, scalar|null> */
    public function context(): array
    {
        return $this->context;
    }
}
