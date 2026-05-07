<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\ApiVersioning\System\System\PublicSurface;

use DateTimeInterface;

final readonly class ApiVersionResolved
{
    public function __construct(
        public int                $version,
        public bool               $deprecated,
        public ?DateTimeInterface $sunset = null,
    ) {}
}
