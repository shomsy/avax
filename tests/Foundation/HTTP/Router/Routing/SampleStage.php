<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\Router\Routing;

use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\Components\HTTP\Router\System\Flows\RunRoute\Pipeline\RouteStage;
use Closure;
use Psr\Http\Message\ResponseInterface;

final class SampleStage implements RouteStage
{
    public function handle(ServerRequest $request, Closure $next) : ResponseInterface
    {
        return $next($request);
    }
}
