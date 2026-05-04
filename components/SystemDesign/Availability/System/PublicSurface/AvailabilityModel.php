<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Availability\System\PublicSurface;

final readonly class AvailabilityModel
{
    public function __construct(
        public float $target = 0.999,
        public int   $regionCount = 1,
        public bool  $multiRegion = false,
    )
    {
    }
}
