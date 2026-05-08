<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Parallelism\System\Configuration;

use Avax\Components\Operations\Parallelism\System\Foundation\ParallelResult;
use Closure;

interface ParallelRuntimeInterface
{
    /**
     * @param array<string|int, Closure(): mixed> $work
     */
    public function run(array $work, int|null $maxWorkers = null) : ParallelResult;
}