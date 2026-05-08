<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Concurrency\System\Capabilities\RunInCurrentProcess;

use Avax\Components\Operations\Concurrency\System\Configuration\TaskRuntimeInterface;
use Avax\Components\Operations\Concurrency\System\Foundation\ConcurrentFailure;
use Avax\Components\Operations\Concurrency\System\Foundation\ConcurrentResult;
use Closure;
use Throwable;

final readonly class CurrentProcessTaskRuntime implements TaskRuntimeInterface
{
    /**
     * @param array<string|int, Closure(): mixed> $tasks
     */
    public function run(array $tasks, int|null $maxConcurrent = null) : ConcurrentResult
    {
        $values         = [];
        $failures       = [];
        $startedTasks   = 0;
        $finishedTasks  = 0;
        $failedTasks    = 0;
        $cancelledTasks = 0;

        $taskList  = $this->buildTasks($tasks);
        $batchSize = $maxConcurrent ?? count($taskList);
        $batches   = $batchSize > 0
            ? array_chunk($taskList, $batchSize, true)
            : [$taskList];

        foreach ($batches as $batch) {
            foreach ($batch as $name => $action) {
                $startedTasks++;
                try {
                    $values[$name] = $action();
                    $finishedTasks++;
                } catch (Throwable $e) {
                    $failures[] = new ConcurrentFailure(
                        name    : $name,
                        message : $e->getMessage(),
                        code    : $e->getCode(),
                        previous: $e->getPrevious(),
                    );
                    $failedTasks++;
                    $finishedTasks++;
                }
            }
        }

        return new ConcurrentResult(
            values        : $values,
            failures      : $failures,
            startedTasks  : $startedTasks,
            finishedTasks : $finishedTasks,
            failedTasks   : $failedTasks,
            cancelledTasks: $cancelledTasks,
        );
    }

    /**
     * @param array<string|int, callable(): mixed> $tasks
     *
     * @return array<string|int, Closure(): mixed>
     */
    private function buildTasks(array $tasks) : array
    {
        $built = [];

        foreach ($tasks as $name => $action) {
            if ($action instanceof Closure) {
                $built[$name] = $action;
            } elseif (is_callable($action)) {
                $built[$name] = Closure::fromCallable($action);
            }
        }

        return $built;
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
