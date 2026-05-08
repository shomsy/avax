<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Parallelism\System\Capabilities\RunInCurrentProcess;

use Avax\Components\Operations\Parallelism\System\Configuration\ParallelRuntimeInterface;
use Avax\Components\Operations\Parallelism\System\Foundation\ParallelFailure;
use Avax\Components\Operations\Parallelism\System\Foundation\ParallelResult;
use Closure;
use Throwable;

final readonly class CurrentProcessParallelRuntime implements ParallelRuntimeInterface
{
    /**
     * @param array<string|int, Closure(): mixed> $work
     */
    public function run(array $work, int|null $maxWorkers = null) : ParallelResult
    {
        $values          = [];
        $failures        = [];
        $startedWorkers  = 0;
        $finishedWorkers = 0;
        $failedWorkers   = 0;

        $tasks     = $this->buildTasks($work);
        $batchSize = $maxWorkers ?? count($tasks);
        $batches   = $batchSize > 0
            ? array_chunk($tasks, $batchSize, true)
            : [$tasks];

        foreach ($batches as $batch) {
            foreach ($batch as $name => $action) {
                $startedWorkers++;
                try {
                    $values[$name] = $action();
                    $finishedWorkers++;
                } catch (Throwable $e) {
                    $failures[] = new ParallelFailure(
                        name    : $name,
                        message : $e->getMessage(),
                        code    : $e->getCode(),
                        previous: $e->getPrevious(),
                    );
                    $failedWorkers++;
                    $finishedWorkers++;
                }
            }
        }

        return new ParallelResult(
            values         : $values,
            failures       : $failures,
            startedWorkers : $startedWorkers,
            finishedWorkers: $finishedWorkers,
            failedWorkers  : $failedWorkers,
        );
    }

    /**
     * @param array<string|int, callable(): mixed> $work
     *
     * @return array<string|int, Closure(): mixed>
     */
    private function buildTasks(array $work) : array
    {
        $tasks = [];

        foreach ($work as $name => $action) {
            if ($action instanceof Closure) {
                $tasks[$name] = $action;
            } elseif (is_callable($action)) {
                $tasks[$name] = Closure::fromCallable($action);
            }
        }

        return $tasks;
    }
}
