<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Parallelism\System\PublicSurface;

use Avax\Components\Operations\Parallelism\System\Flows\RunWorkInParallel\RunWorkInParallel;
use Avax\Components\Operations\Parallelism\System\Foundation\ParallelResult;
use Closure;

final readonly class Parallel
{
    /**
     * Map items through a callable in parallel.
     *
     * @param iterable<mixed>       $items
     * @param Closure(mixed): mixed $mapper
     */
    public static function map(iterable $items, callable $mapper, int|null $maxWorkers = null) : ParallelResult
    {
        $work  = [];
        $index = 0;

        foreach ($items as $item) {
            $name        = 'item_' . $index;
            $work[$name] = static fn () => $mapper($item);
            $index++;
        }

        return self::run(work: $work, maxWorkers: $maxWorkers);
    }

    /**
     * Run multiple work units in parallel using the current process runtime.
     *
     * @param array<string|int, Closure(): mixed> $work
     */
    public static function run(array $work, int|null $maxWorkers = null) : ParallelResult
    {
        return (new RunWorkInParallel())->run(work: $work, maxWorkers: $maxWorkers);
    }
}
