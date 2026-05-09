<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Parallelism\System\Flows\RunWorkInParallel;

use Avax\Components\Operations\Parallelism\System\Configuration\BuildParallelRuntime;
use Avax\Components\Operations\Parallelism\System\Configuration\ParallelRuntimeInterface;
use Avax\Components\Operations\Parallelism\System\Foundation\ParallelResult;
use Closure;

final readonly class RunWorkInParallel
{
    private ParallelRuntimeInterface $runtime;

    public function __construct(
        private BuildParallelRuntime $builder = new BuildParallelRuntime(),
    )
    {
        $this->runtime = $this->builder->build();
    }

    /**
     * @param array<string|int, Closure(): mixed> $work
     */
    public function run(array $work, int|null $maxWorkers = null) : ParallelResult
    {
        if (empty($work)) {
            return new ParallelResult(
                values         : [],
                failures       : [],
                startedWorkers : 0,
                finishedWorkers: 0,
                failedWorkers  : 0,
            );
        }

        return $this->runtime->run(work: $work, maxWorkers: $maxWorkers);
    }
}
