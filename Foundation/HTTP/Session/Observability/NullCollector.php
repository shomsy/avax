<?php
declare(strict_types=1);

namespace Foundation\HTTP\Session\Observability;

final class NullCollector implements MetricCollectorInterface
{
    public function increment(string $metric, array $labels = []) : void {}

    public function observe(string $metric, float $value, array $labels = []) : void {}
}
