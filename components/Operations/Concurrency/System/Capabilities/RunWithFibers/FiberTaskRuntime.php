<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Concurrency\System\Capabilities\RunWithFibers;

use Avax\Components\Operations\Concurrency\System\Capabilities\CaptureTaskFailure\CaptureTaskFailure;
use Avax\Components\Operations\Concurrency\System\Capabilities\LimitRunningTasks\LimitRunningTasks;
use Avax\Components\Operations\Concurrency\System\Configuration\TaskRuntimeInterface;
use Avax\Components\Operations\Concurrency\System\Foundation\ConcurrentResult;
use Closure;
use Fiber;
use Throwable;

final readonly class FiberTaskRuntime implements TaskRuntimeInterface
{
    private CaptureTaskFailure $captureFailure;
    private LimitRunningTasks  $limiter;

    public function __construct()
    {
        $this->captureFailure = new CaptureTaskFailure();
        $this->limiter        = new LimitRunningTasks();
    }

    /**
     * @param array<string|int, Closure(): mixed> $tasks
     */
    public function run(array $tasks, int|null $maxConcurrent = null) : ConcurrentResult
    {
        if (empty($tasks)) {
            return new ConcurrentResult(
                values        : [],
                failures      : [],
                startedTasks  : 0,
                finishedTasks : 0,
                failedTasks   : 0,
                cancelledTasks: 0,
            );
        }

        $limit          = $this->limiter->effectiveLimit($maxConcurrent);
        $values         = [];
        $failures       = [];
        $startedTasks   = 0;
        $finishedTasks  = 0;
        $failedTasks    = 0;
        $cancelledTasks = 0;

        $taskChunks = $this->limiter->chunk($tasks, $limit);

        foreach ($taskChunks as $chunk) {
            $fibers  = [];
            $results = [];
            $errors  = [];

            foreach ($chunk as $name => $action) {
                $fibers[$name] = $this->createTaskFiber($action, $name, $results, $errors);
                $startedTasks++;
            }

            $this->runFibers($fibers);

            foreach ($results as $name => $value) {
                $values[$name] = $value;
                $finishedTasks++;
            }

            foreach ($errors as $name => $exception) {
                $failures[] = $this->captureFailure->capture($name, $exception);
                $failedTasks++;
                $finishedTasks++;
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
     * @param list<Closure(): mixed> $tasks
     */
    public function race(array $tasks) : mixed
    {
        if (empty($tasks)) {
            return null;
        }

        $fibers  = [];
        $results = [];

        foreach ($tasks as $index => $action) {
            $ignoredErrors  = [];
            $fibers[$index] = $this->createTaskFiber($action, $index, $results, $ignoredErrors);
        }

        $this->runFibersUntilFirstResult($fibers, $results);

        foreach ($results as $result) {
            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }

    /**
     * @param array<string|int, mixed>     $results
     * @param array<string|int, Throwable> $errors
     *
     * @return Fiber<mixed, mixed, mixed, mixed>
     */
    private function createTaskFiber(
        Closure    $action,
        string|int $name,
        array      &$results,
        array      &$errors,
    ) : Fiber
    {
        return new Fiber(function () use ($action, $name, &$results, &$errors) : void {
            try {
                $results[$name] = $action();
            } catch (Throwable $e) {
                $errors[$name] = $e;
            }
        });
    }

    /**
     * @param array<string|int, Fiber<mixed, mixed, mixed, mixed>> $fibers
     */
    private function runFibers(array $fibers) : void
    {
        $active = $fibers;

        while ( ! empty($active) ) {
            $remaining = [];

            foreach ($active as $key => $fiber) {
                $this->advanceFiber($fiber);

                if ($fiber->isTerminated()) {
                    continue;
                }

                $remaining[$key] = $fiber;
            }

            $active = $remaining;
        }
    }

    /**
     * @param array<int, Fiber<mixed, mixed, mixed, mixed>> $fibers
     * @param array<string|int, mixed>                      $results
     */
    private function runFibersUntilFirstResult(array $fibers, array &$results) : void
    {
        $active = $fibers;

        while ( ! empty($active) && empty($results) ) {
            $remaining = [];

            foreach ($active as $key => $fiber) {
                $this->advanceFiber($fiber);

                if ($fiber->isTerminated()) {
                    continue;
                }

                $remaining[$key] = $fiber;
            }

            $active = $remaining;
        }
    }

    /**
     * @param Fiber<mixed, mixed, mixed, mixed> $fiber
     */
    private function advanceFiber(Fiber $fiber) : void
    {
        if (! $fiber->isStarted()) {
            $fiber->start();

            return;
        }

        if ($fiber->isSuspended()) {
            $fiber->resume();
        }
    }
}
