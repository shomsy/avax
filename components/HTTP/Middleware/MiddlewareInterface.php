<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Middleware;

use Avax\Components\HTTP\Middleware\System\System\PublicSurface\MiddlewareInterface as ActualMiddlewareInterface;

/**
 * Compatibility bridge: re-export the actual MiddlewareInterface
 * from System\PublicSurface so legacy imports continue to work.
 *
 * @deprecated Import from System\PublicSurface\MiddlewareInterface directly
 */
interface MiddlewareInterface extends ActualMiddlewareInterface
{
}
