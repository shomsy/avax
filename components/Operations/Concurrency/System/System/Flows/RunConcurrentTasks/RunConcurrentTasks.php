<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Concurrency\System\System\Flows\RunConcurrentTasks;

use Avax\Components\Operations\Concurrency\System\System\Capabilities\Tasks\TaskRunner;

final readonly class RunConcurrentTasks
{
    /**
     * @param list<callable(): mixed> $tasks
     *
     * @return list<mixed>
     */
    public function run(array $tasks) : array
    {
        return new TaskRunner()->runAll($tasks);
    }
}
