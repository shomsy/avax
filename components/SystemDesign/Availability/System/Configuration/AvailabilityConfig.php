<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Availability\System\Configuration;

final readonly class AvailabilityConfig
{
    public function __construct(
        public bool $enabled = true,
    )
    {
    }
}
