<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools;

final class ReadWritePool
{
    public function __construct(
        private readonly ConnectionPoolInterface $readPool,
        private readonly ConnectionPoolInterface $writePool,
        private bool $enableReadWriteSplit = true,
    ) {
    }

    public function getRead(): PooledConnection
    {
        if (! $this->enableReadWriteSplit) {
            return $this->writePool->get();
        }

        return $this->readPool->get();
    }

    public function getWrite(): PooledConnection
    {
        return $this->writePool->get();
    }

    public function releaseRead(PooledConnection $pooledConnection): void
    {
        $this->readPool->release(connection: $pooledConnection);
    }

    public function releaseWrite(PooledConnection $pooledConnection): void
    {
        $this->writePool->release(connection: $pooledConnection);
    }

    public function isReadWriteSplitEnabled(): bool
    {
        return $this->enableReadWriteSplit;
    }

    public function enableReadWriteSplit(bool $enabled = true): void
    {
        $this->enableReadWriteSplit = $enabled;
    }
}
