<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MemoryLifecycle\System\Configuration;

class MemoryLifecycleConfiguration implements MemoryLifecycleConfigurationInterface
{
    public function __construct(private readonly int $budgetBytes = 128 * 1024 * 1024, private readonly bool $trackingEnabled = false, private readonly bool $autoSnapshotEnabled = true, private readonly int $snapshotIntervalSeconds = 60)
    {
    }

    public function getBudgetBytes(): int
    {
        return $this->budgetBytes;
    }

    public function isTrackingEnabled(): bool
    {
        return $this->trackingEnabled;
    }

    public function isAutoSnapshotEnabled(): bool
    {
        return $this->autoSnapshotEnabled;
    }

    public function getSnapshotIntervalSeconds(): int
    {
        return $this->snapshotIntervalSeconds;
    }
}
