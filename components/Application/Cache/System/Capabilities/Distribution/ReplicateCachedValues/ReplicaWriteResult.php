<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\ReplicateCachedValues;

/**
 * Value object representing the result of writing to a single replica.
 */
final readonly class ReplicaWriteResult
{
    public function __construct(
        public int $replicaIndex,
        public string $key,
        public bool $success,
        public ?string $error,
    ) {
    }
}
