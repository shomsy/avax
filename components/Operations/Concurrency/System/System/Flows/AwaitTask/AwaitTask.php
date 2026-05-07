<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Concurrency\System\System\Flows\AwaitTask;

final readonly class AwaitTask
{
    /**
     * @template TResult
     *
     * @param callable(): TResult $task
     *
     * @return TResult
     */
    public function await(callable $task) : mixed
    {
        return $task();
    }
}
