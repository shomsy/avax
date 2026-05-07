<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MemoryLifecycle\System\Flows\RunGarbageCollection;

final readonly class RunGarbageCollection
{
    /**
     * @return array{collected: int, before: int, after: int}
     */
    public function run() : array
    {
        $before = memory_get_usage();

        $collected = gc_collect_cycles();

        $after = memory_get_usage();

        return [
            'collected' => $collected,
            'before'    => $before,
            'after'     => $after,
            'freed'     => $before - $after,
        ];
    }
}
