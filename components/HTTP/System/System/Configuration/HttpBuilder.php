<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\System\Configuration;

use Avax\Components\HTTP\Middleware\System\System\Capabilities\Pipeline\MiddlewarePipeline;
use Avax\Components\HTTP\Router\System\System\PublicSurface\Router;
use Avax\Components\HTTP\System\System\PublicSurface\Http;

final class HttpBuilder
{
    public function build() : Http
    {
        return new Http(
            new Router(),
            new MiddlewarePipeline(),
        );
    }
}
