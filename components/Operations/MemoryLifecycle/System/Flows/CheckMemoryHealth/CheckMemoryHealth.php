<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MemoryLifecycle\System\Flows\CheckMemoryHealth;

use Avax\Components\Operations\MemoryLifecycle\System\Capabilities\Health\CheckMemoryLifecycleHealth;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthReport;

final readonly class CheckMemoryHealth
{
    public function check() : HealthReport
    {
        return (new CheckMemoryLifecycleHealth())->check();
    }
}
