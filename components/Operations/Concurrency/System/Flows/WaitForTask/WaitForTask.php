<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Concurrency\System\Flows\WaitForTask;

use Avax\Components\Operations\Concurrency\System\Foundation\ConcurrentTask;

final readonly class WaitForTask
{
    public function await(ConcurrentTask $task) : mixed
    {
        if ($task->isFinished()) {
            if ($task->getError() !== null) {
                throw $task->getError();
            }

            return $task->getResult();
        }

        return $task->execute();
    }
}
