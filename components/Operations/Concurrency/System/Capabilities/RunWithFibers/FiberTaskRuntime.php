<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Concurrency\System\Capabilities\RunWithFibers;

use Avax\Components\Operations\Concurrency\System\Capabilities\RunInCurrentProcess\CurrentProcessTaskRuntime;
use Avax\Components\Operations\Concurrency\System\Configuration\TaskRuntimeInterface;
use Avax\Components\Operations\Concurrency\System\Foundation\ConcurrentResult;
use Closure;

final readonly class FiberTaskRuntime implements TaskRuntimeInterface
{
    /**
     * @param array<string|int, Closure(): mixed> $tasks
     */
    public function run(array $tasks, int|null $maxConcurrent = null) : ConcurrentResult
    {
        $fallback = new CurrentProcessTaskRuntime();

        return $fallback->run($tasks, $maxConcurrent);
    }

    /**
     * @param list<Closure(): mixed> $tasks
     */
    public function race(array $tasks) : mixed
    {
        foreach ($tasks as $action) {
            $result = $action();
            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }
}
