<?php

declare(strict_types=1);

namespace Avax\Container\Observability;

final class ResolutionMetrics
{
    /** @var array<string, int> */
    private array $counters = [];

    public function increment(string $name, int $by = 1) : void
    {
        $this->counters[$name] = ($this->counters[$name] ?? 0) + $by;
    }

    /** @return array<string, int> */
    public function all() : array
    {
        return $this->counters;
    }

    public function export() : string
    {
        $lines = [];

        foreach ($this->counters as $name => $value) {
            $lines[] = $name . ' ' . $value;
        }

        return implode(PHP_EOL, $lines);
    }

    public function reset() : void
    {
        $this->counters = [];
    }
}
