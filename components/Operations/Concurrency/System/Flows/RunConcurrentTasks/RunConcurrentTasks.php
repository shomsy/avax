<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Concurrency\System\Flows\RunConcurrentTasks;

use Avax\Components\Operations\Concurrency\System\Configuration\BuildConcurrencyRuntime;
use Avax\Components\Operations\Concurrency\System\Configuration\ConcurrencyConfig;
use Avax\Components\Operations\Concurrency\System\Configuration\TaskRuntimeInterface;
use Avax\Components\Operations\Concurrency\System\Foundation\ConcurrentResult;
use Closure;

final readonly class RunConcurrentTasks
{
    private TaskRuntimeInterface $runtime;

    public function __construct(
        private BuildConcurrencyRuntime $builder = new BuildConcurrencyRuntime(),
    )
    {
        $this->runtime = $this->builder->build(
            config: ConcurrencyConfig::fromArray([]),
        );
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

        return $this->runtime->run(tasks: $tasks, maxConcurrent: $maxConcurrent);
    }
}
