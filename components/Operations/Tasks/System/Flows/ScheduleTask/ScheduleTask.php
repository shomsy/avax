<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Tasks\System\Flows\ScheduleTask;

use Avax\Components\Operations\Tasks\System\Capabilities\TaskScheduler\TaskScheduler;

final readonly class ScheduleTask
{
    public function schedule(TaskScheduler $scheduler, string $name, callable $task, string $cronExpression) : void
    {
        $id = bin2hex(random_bytes(4));
        $scheduler->schedule($id, $name, $task, $cronExpression);
    }
}
