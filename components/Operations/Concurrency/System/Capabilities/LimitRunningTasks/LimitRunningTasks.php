<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Concurrency\System\Capabilities\LimitRunningTasks;

use Closure;

final readonly class LimitRunningTasks
{
    public function __construct(
        private int $maxConcurrent = 8,
    ) {}

    public function limit(int $maxConcurrent) : self
    {
        return new self($maxConcurrent);
    }

    /**
     * @param array<string|int, Closure(): mixed> $tasks
     *
     * @return array<int, array<string|int, Closure(): mixed>>
     */
    public function chunk(array $tasks, int|null $maxConcurrent = null) : array
    {
        $limit = $maxConcurrent ?? $this->maxConcurrent;
        $total = count($tasks);

        if ($limit <= 0 || $total === 0) {
            return [$tasks];
        }

        $chunks = [];
        $keys   = array_keys($tasks);
        $values = array_values($tasks);

        for ($i = 0; $i < $total; $i += $limit) {
            $chunk = [];
            for ($j = $i; $j < min($i + $limit, $total); $j++) {
                $chunk[$keys[$j]] = $values[$j];
            }
            $chunks[] = $chunk;
        }

        return $chunks;
    }

    public function effectiveLimit(int|null $maxConcurrent = null) : int
    {
        return $maxConcurrent ?? $this->maxConcurrent;
    }
}
