<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Parallelism\System\PublicSurface;

use Avax\Components\Operations\Parallelism\System\Configuration\Builders\BuildParallelRuntime;
use Avax\Components\Operations\Parallelism\System\Configuration\ParallelRuntimeInterface;
use Avax\Components\Operations\Parallelism\System\Flows\RunWorkInParallel\RunWorkInParallel;
use Avax\Components\Operations\Parallelism\System\Foundation\ParallelResult;
use Closure;

final class Parallel
{
    private static ?ParallelRuntimeInterface $runtime = null;

    private static function runtime() : ParallelRuntimeInterface
    {
        return self::$runtime ??= (new BuildParallelRuntime())->build();
    }

    /**
     * Replace the runtime (for testing).
     */
    public static function setRuntime(ParallelRuntimeInterface $runtime) : void
    {
        self::$runtime = $runtime;
    }

    /**
     * Reset the runtime. Required for long-lived runtimes (worker mode).
     */
    public static function reset() : void
    {
        self::$runtime = null;
    }

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
        return (new RunWorkInParallel(runtime: self::runtime()))->run(work: $work, maxWorkers: $maxWorkers);
    }
}
