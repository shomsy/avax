<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\ApiVersioning\System\PublicSurface;

use Avax\Components\HTTP\ApiVersioning\System\Capabilities\Lifecycle\VersionRegistry;
use Avax\Components\HTTP\ApiVersioning\System\Capabilities\Resolution\VersionResolver;
use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use DateTimeInterface;

final readonly class ApiVersionResolved
{
    public function __construct(
        public int                    $version,
        public bool                   $deprecated,
        public DateTimeInterface|null $sunset = null,
    )
    {
    }
}
