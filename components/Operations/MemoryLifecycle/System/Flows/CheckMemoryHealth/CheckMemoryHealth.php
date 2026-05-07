<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MemoryLifecycle\System\Flows\CheckMemoryHealth;

use Avax\Components\Operations\MemoryLifecycle\System\Capabilities\Health\CheckMemoryLifecycleHealth;
use Avax\Components\Operations\MemoryLifecycle\System\Capabilities\Health\MemoryLifecycleHealthReport;

final readonly class CheckMemoryHealth
{
    public function check() : MemoryLifecycleHealthReport
    {
        return (new CheckMemoryLifecycleHealth())->check();
    }
}
