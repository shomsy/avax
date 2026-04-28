<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\Capabilities\Connections\Pools;

final class ArrayPooledConnection implements PooledConnection
{
    private readonly float $createdAt;

    private float $lastUsedAt;

    private int $executeCount = 0;

    public function __construct(
        private readonly array $config = [],
    )
    {
        $this->createdAt  = microtime(as_float: true);
        $this->lastUsedAt = $this->createdAt;
    }

    public function getResource() : object
    {
        $this->lastUsedAt = microtime(as_float: true);

        return (object) ['config' => $this->config];
    }

    public function isValid() : bool
    {
        return true;
    }

    public function getCreatedAt() : float
    {
        return $this->createdAt;
    }

    public function getLastUsedAt() : float
    {
        return $this->lastUsedAt;
    }

    public function executeCount() : int
    {
        return $this->executeCount;
    }

    public function recordExecution() : void
    {
        $this->executeCount++;
        $this->lastUsedAt = microtime(as_float: true);
    }
}
