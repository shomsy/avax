<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\System\Configuration;

use Avax\Components\HTTP\System\PublicSurface\Http;
use Avax\Components\HTTP\Router\System\PublicSurface\Router;
use Avax\Components\HTTP\Middleware\System\Capabilities\Pipeline\MiddlewarePipeline;

final class HttpBuilder
{
    public function build(): Http
    {
        return new Http(
            new Router(),
            new MiddlewarePipeline()
        );
    }
}
