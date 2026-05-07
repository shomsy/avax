<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Capabilities\Bulkhead;

final class Bulkhead
{
    private int $active = 0;

    public function __construct(
        private readonly int $maxConcurrent = 10,
    ) {}

    /**
     * @template TResult
     * @param callable(): TResult $operation
     *
     * @return TResult
     */
    public function run(callable $operation) : mixed
    {
        if ($this->active >= $this->maxConcurrent) {
            throw new BulkheadException("Bulkhead is full ({$this->maxConcurrent} concurrent).");
        }

        $this->active++;

        try {
            return $operation();
        } finally {
            $this->active--;
        }
    }

    public function activeCount() : int
    {
        return $this->active;
    }

    public function availableSlots() : int
    {
        return max(0, $this->maxConcurrent - $this->active);
    }
}
