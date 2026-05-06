<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MemoryLifecycle\System\PublicSurface;

use Avax\Components\Operations\MemoryLifecycle\System\Capabilities\Budget\MemoryBudget;
use Avax\Components\Operations\MemoryLifecycle\System\Capabilities\Snapshot\MemorySnapshot;
use Avax\Components\Operations\MemoryLifecycle\System\Capabilities\Tracking\MemoryTracker;

final readonly class MemoryLifecycle
{
    public static function budget(int $bytes): MemoryBudget
    {
        return new MemoryBudget($bytes);
    }

    public static function snapshot(): MemorySnapshot
    {
        return new MemorySnapshot();
    }

    public static function tracker(): MemoryTracker
    {
        return new MemoryTracker();
    }
}
