<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Capabilities\MetricsCollector;

use Avax\Components\Operations\Observability\System\Capabilities\Metrics\Metric;

/**
 * Extended histogram with full stats tracking.
 */
final class ExtendedHistogram extends Metric
{
    /** @var list<float> */
    private array $values = [];

    public function __construct(string $name, string $unit = '')
    {
        parent::__construct(name: $name);
    }

    public function observe(float $value) : void
    {
        $this->values[] = $value;
    }

    /**
     * @return array{count: int, sum: float, min: float|null, max: float|null}
     */
    public function stats() : array
    {
        if ($this->values === []) {
            return ['count' => 0, 'sum' => 0.0, 'min' => null, 'max' => null];
        }

        return [
            'count' => count($this->values),
            'sum' => array_sum($this->values),
            'min' => min($this->values),
            'max' => max($this->values),
        ];
    }
}
