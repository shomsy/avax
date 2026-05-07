<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MemoryLifecycle\System\Flows\AllocateMemory;

use Avax\Components\Operations\MemoryLifecycle\System\Capabilities\Budget\MemoryBudget;

final readonly class AllocateMemory
{
    public function allocate(MemoryBudget $budget, int $bytes) : bool
    {
        return $budget->allocate($bytes);
    }
}
