<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Parallelism\System\Flows\MapItemsInParallel;

use Avax\Components\Operations\Parallelism\System\Configuration\ParallelRuntimeInterface;
use Avax\Components\Operations\Parallelism\System\Foundation\ParallelResult;
use Closure;

final readonly class MapItemsInParallel
{
    public function __construct(
        private ParallelRuntimeInterface $runtime,
    ) {
    }

    /**
     * Map items through a callable in parallel.
     *
     * @param iterable<mixed>       $items
     * @param Closure(mixed): mixed $mapper
     */
    public function map(iterable $items, callable $mapper, int|null $maxWorkers = null) : ParallelResult
    {
        $work  = [];
        $index = 0;

        foreach ($items as $item) {
            $name        = 'item_' . $index;
            $work[$name] = static fn () => $mapper($item);
            $index++;
        }

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
