<?php

declare(strict_types=1);

namespace Avax\Components\Application\Facade\System\PublicSurface;

use Avax\Components\Application\Facade\System\Foundation\BaseFacade;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterInterface;

final class Route extends BaseFacade
{
    protected static string $accessor = RouterInterface::class;
}
