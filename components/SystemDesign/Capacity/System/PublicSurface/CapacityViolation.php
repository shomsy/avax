<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Capacity\System\PublicSurface;

final readonly class CapacityViolation
{
    public function __construct(
        public string $type,
        public string $message,
        public float  $actual,
        public float  $allowed,
    )
    {
    }
}
