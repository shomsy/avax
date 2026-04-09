<?php

declare(strict_types=1);

namespace Avax\Container\DI\Capabilities\Diagnostics\Observability;

/**
 * Stores low-overhead numeric counters for container activity.
 */
final class ResolutionMetrics
{
    /** @var array<string, int> */
    private array $counters = [];

    /**
     * Increments one metric counter.
     */
    public function increment(string $name, int $by = 1) : void
    {
        $this->counters[$name] = ($this->counters[$name] ?? 0) + $by;
    }

    /** @return array<string, int> */
    public function all() : array
    {
        $counters = $this->counters;
        ksort($counters);

        return $counters;
    }

    /**
     * Exports metrics in a text format.
     */
    public function export() : string
    {
        $lines = [];

        foreach ($this->all() as $name => $value) {
            $lines[] = $name . ' ' . $value;
        }

        return implode(PHP_EOL, $lines);
    }

    /**
     * Clears all counters.
     */
    public function reset() : void
    {
        $this->counters = [];
    }
}
