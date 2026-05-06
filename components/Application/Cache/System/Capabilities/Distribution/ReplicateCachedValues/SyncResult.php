<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\ReplicateCachedValues;

/**
 * Value object representing the result of a sync operation.
 */
final readonly class SyncResult
{
    /** @param list<ReplicaSyncResult> $replicaSyncResults */
    public function __construct(
        public string $key,
        public bool $foundOnPrimary,
        public array $replicaSyncResults,
    ) {
    }

    /**
     * Check if all replicas are in sync.
     */
    public function allReplicasInSync(): bool
    {
        return array_all($this->replicaSyncResults, fn ($replicaSyncResult) => $replicaSyncResult->wasInSync);
    }
}
