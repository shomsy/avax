<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Capabilities\Metrics;

class Metric
{
    public function __construct(
        public readonly string $name,
        public readonly array $tags = [],
    ) {
    }
}
