<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Parallelism\System\Capabilities\LimitWorkerCount;

final readonly class LimitWorkerCount
{
    public function __construct(
        private int $maxWorkers = 4,
    ) {}

    public function limit(int $maxWorkers) : self
    {
        return new self($maxWorkers);
    }

    /**
     * @param list<mixed> $items
     *
     * @return list<list<mixed>>
     */
    public function chunkWork(array $items, int|null $maxWorkers = null) : array
    {
        $limit = $this->effectiveLimit($maxWorkers);
        $total = count($items);

        if ($limit <= 0 || $total === 0) {
            return [$items];
        }

        $chunks = [];
        for ($i = 0; $i < $total; $i += $limit) {
            $chunks[] = array_slice($items, $i, $limit);
        }

        return $chunks;
    }

    public function effectiveLimit(int|null $maxWorkers = null) : int
    {
        if ($maxWorkers !== null && $maxWorkers > 0) {
            return min($maxWorkers, $this->maxWorkers);
        }

        return $this->maxWorkers;
    }

    public function shouldStartWorker(int $activeCount, int|null $maxWorkers = null) : bool
    {
        $limit = $this->effectiveLimit($maxWorkers);

        return $activeCount < $limit;
    }
}
