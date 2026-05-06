<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Capabilities\Metrics;

class Histogram extends Metric
{
    /**
     * @var array<float>
     */
    private array $values = [];

    public function record(float $value): void
    {
        $this->values[] = $value;
    }

    public function count(): int
    {
        return count($this->values);
    }

    public function avg(): float
    {
        return $this->values !== []
            ? array_sum($this->values) / count($this->values)
            : 0.0;
    }
}
