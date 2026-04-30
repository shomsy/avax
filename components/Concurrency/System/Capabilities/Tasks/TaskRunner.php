<?php

declare(strict_types=1);

namespace Avax\Components\Concurrency\System\Capabilities\Tasks;

use Closure;
use Fiber;

final readonly class TaskRunner
{
    /**
     * @param list<Closure(): mixed> $tasks
     *
     * @return list<mixed>
     */
    public function runAll(array $tasks) : array
    {
        if (! class_exists(Fiber::class)) {
            return $this->runAllSync($tasks);
        }

        return $this->runAllFibers($tasks);
    }

    /**
     * @param list<Closure(): mixed> $tasks
     *
     * @return list<mixed>
     */
    private function runAllSync(array $tasks) : array
    {
        $results = [];

        foreach ($tasks as $task) {
            $results[] = $task();
        }

        return $results;
    }

    /**
     * @param list<Closure(): mixed> $tasks
     *
     * @return list<mixed>
     */
    private function runAllFibers(array $tasks) : array
    {
        $fibers = array_map(
            static fn (callable $task) => new Fiber($task),
            $tasks,
        );

        $results = array_fill(0, count($tasks), null);

        while ( $this->hasActiveFibers($fibers) ) {
            foreach ($fibers as $index => $fiber) {
                if (! $fiber->isStarted()) {
                    $fiber->start();

                    if ($fiber->isTerminated()) {
                        $results[$index] = $fiber->getReturn();
                    }
                } elseif (! $fiber->isTerminated()) {
                    $fiber->resume();

                    if ($fiber->isTerminated()) {
                        $results[$index] = $fiber->getReturn();
                    }
                }
            }
        }

        return $results;
    }

    /**
     * @param list<Fiber> $fibers
     */
    private function hasActiveFibers(array $fibers) : bool
    {
        foreach ($fibers as $fiber) {
            if (! $fiber->isTerminated()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<Closure(): mixed> $tasks
     */
    public function race(array $tasks) : mixed
    {
        if (! class_exists(Fiber::class)) {
            return $this->raceSync($tasks);
        }

        return $this->raceFibers($tasks);
    }

    /**
     * @param list<Closure(): mixed> $tasks
     */
    private function raceSync(array $tasks) : mixed
    {
        foreach ($tasks as $task) {
            $result = $task();

            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }

    /**
     * @param list<Closure(): mixed> $tasks
     */
    private function raceFibers(array $tasks) : mixed
    {
        foreach ($tasks as $task) {
            $fiber = new Fiber($task);
            $fiber->start();

            if ($fiber->isTerminated()) {
                return $fiber->getReturn();
            }
        }

        return null;
    }
}
