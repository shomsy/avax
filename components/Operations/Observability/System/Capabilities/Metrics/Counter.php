<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Capabilities\Metrics;

class Counter extends Metric
{
    private float $value = 0;

    public function increment(float $amount = 1.0): void
    {
        $this->value += $amount;
    }

    public function getValue(): float
    {
        return $this->value;
    }
}