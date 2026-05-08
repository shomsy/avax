<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Concurrency\System\Flows\StartTask;

use Avax\Components\Operations\Concurrency\System\Foundation\ConcurrentTask;
use Closure;

final readonly class StartTask
{
    /**
     * @template TResult
     * @param Closure(): TResult $task
     */
    public function start(Closure $task) : ConcurrentTask
    {
        return ConcurrentTask::create($task);
    }
}
