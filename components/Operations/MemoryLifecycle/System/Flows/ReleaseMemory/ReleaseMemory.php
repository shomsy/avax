<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MemoryLifecycle\System\Flows\ReleaseMemory;

use Avax\Components\Operations\MemoryLifecycle\System\Capabilities\Budget\MemoryBudget;

final readonly class ReleaseMemory
{
    public function release(MemoryBudget $budget, int $bytes) : void
    {
        $budget->release($bytes);
    }
}
