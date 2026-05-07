<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Tasks\System\Flows\CancelTask;

use Avax\Components\Operations\Tasks\System\Capabilities\TaskScheduler\TaskScheduler;

final readonly class CancelTask
{
    public function cancel(TaskScheduler $scheduler, string $taskId) : bool
    {
        $task = $scheduler->find($taskId);

        if ($task === null) {
            return false;
        }

        $scheduler->unschedule($taskId);

        return true;
    }
}
