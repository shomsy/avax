<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\SystemDesign\Foundation;

final readonly class CapacityRecommendation
{
    public function __construct(
        public string $area,
        public string $recommendation,
        public string $reason,
        /** @var array<string, mixed> $metrics */
        public array $metrics = [],
    ) {
    }
}
