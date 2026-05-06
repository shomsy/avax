<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\ReplicateCachedValues;

/**
 * Value object representing the result of syncing a single replica.
 */
final readonly class ReplicaSyncResult
{
    public function __construct(
        public int $replicaIndex,
        public string $key,
        public bool $wasInSync,
        public bool $syncSuccess,
    ) {
    }
}
