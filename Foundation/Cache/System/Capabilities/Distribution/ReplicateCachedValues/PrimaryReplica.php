<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Distribution\ReplicateCachedValues;

final readonly class PrimaryReplica
{
    public function __construct(
        public int $index = 0
    ) {}

    public function isPrimary() : bool
    {
        return $this->index === 0;
    }
}