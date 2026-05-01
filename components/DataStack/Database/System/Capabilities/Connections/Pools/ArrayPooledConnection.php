<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools;

use Override;

final class ArrayPooledConnection implements PooledConnection
{
    private readonly float $createdAt;

    private float $lastUsedAt;

    private int $executeCount = 0;

    public function __construct(
        private readonly array $config = [],
    ) {
        $this->createdAt = microtime(as_float: true);
        $this->lastUsedAt = $this->createdAt;
    }

    #[Override]
    public function getResource(): object
    {
        $this->lastUsedAt = microtime(as_float: true);

        return (object) ['config' => $this->config];
    }

    #[Override]
    public function isValid(): bool
    {
        return true;
    }

    #[Override]
    public function getCreatedAt(): float
    {
        return $this->createdAt;
    }

    #[Override]
    public function getLastUsedAt(): float
    {
        return $this->lastUsedAt;
    }

    #[Override]
    public function executeCount(): int
    {
        return $this->executeCount;
    }

    public function recordExecution(): void
    {
        $this->executeCount++;
        $this->lastUsedAt = microtime(as_float: true);
    }
}
