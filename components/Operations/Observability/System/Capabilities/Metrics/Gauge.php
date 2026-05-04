<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Capabilities\Metrics;

class Gauge extends Metric
{
    private float $value = 0;

    public function set(float $value): void
    {
        $this->value = $value;
    }

    public function getValue(): float
    {
        return $this->value;
    }
}