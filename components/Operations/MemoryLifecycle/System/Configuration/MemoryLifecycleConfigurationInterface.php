<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MemoryLifecycle\System\Configuration;

interface MemoryLifecycleConfigurationInterface
{
    public function getBudgetBytes(): int;

    public function isTrackingEnabled(): bool;

    public function isAutoSnapshotEnabled(): bool;
}
