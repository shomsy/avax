<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\Router\Routing;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Closure;
use Psr\Http\Message\ResponseInterface;

final class SampleMiddleware
{
    public function handle(ServerRequest $request, Closure $next) : ResponseInterface
    {
        return $next($request);
    }
}
