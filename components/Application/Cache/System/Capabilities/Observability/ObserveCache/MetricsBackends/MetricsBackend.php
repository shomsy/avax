<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Observability\ObserveCache\MetricsBackends;

interface MetricsBackend
{
    public function increment(string $metric, int $value = 1) : void;

    public function gauge(string $metric, float $value) : void;

    public function histogram(string $metric, float $value) : void;

    public function timing(string $metric, int $milliseconds) : void;

    public function flush() : void;
}