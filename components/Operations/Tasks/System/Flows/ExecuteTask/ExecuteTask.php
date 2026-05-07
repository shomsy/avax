<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Tasks\System\Flows\ExecuteTask;

use Avax\Components\Operations\Tasks\System\Capabilities\TaskRunner\TaskRunner;

final readonly class ExecuteTask
{
    public function execute(TaskRunner $runner, callable $task) : array
    {
        return $runner->run($task);
    }
}
