<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Scheduler\System\Flows\RunDueTasks;

use Avax\Components\Operations\Scheduler\System\PublicSurface\Scheduler;
use Avax\Components\Operations\Scheduler\System\PublicSurface\SchedulerReport;

final readonly class RunDueTasks
{
    public function run(): SchedulerReport
    {
        return Scheduler::runDueTasks();
    }
}
