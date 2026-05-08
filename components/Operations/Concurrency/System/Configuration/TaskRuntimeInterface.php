<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Concurrency\System\Configuration;

use Avax\Components\Operations\Concurrency\System\Foundation\ConcurrentResult;
use Closure;

interface TaskRuntimeInterface
{
    /**
     * @param array<string|int, Closure(): mixed> $tasks
     */
    public function run(array $tasks, int|null $maxConcurrent = null) : ConcurrentResult;

    /**
     * @param list<Closure(): mixed> $tasks
     */
    public function race(array $tasks) : mixed;
}
