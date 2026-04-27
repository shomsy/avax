<?php

declare(strict_types=1);

namespace Avax\HTTP\Router;

use Avax\Components\Router\System\PublicSurface\HttpMethod as NewHttpMethod;

/**
 * @deprecated Use Avax\Components\Router\System\PublicSurface\HttpMethod instead.
 */
class HttpMethod
{
    // Bridge case-like constants if needed, but Enum to Class bridge is tricky.
    // For now, we keep it as a simple bridge or let users migrate to the enum.
}
