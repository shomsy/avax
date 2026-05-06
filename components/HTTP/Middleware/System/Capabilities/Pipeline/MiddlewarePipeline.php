<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Middleware\System\Capabilities\Pipeline;

use Avax\Components\HTTP\Middleware\System\PublicSurface\MiddlewareInterface;
use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;

final class MiddlewarePipeline
{
    /**
     * @var list<MiddlewareInterface>
     */
    private array $middleware = [];

    /**
     * @param  list<MiddlewareInterface>  $middleware
     */
    public function __construct(array $middleware = [])
    {
        $this->middleware = $middleware;
    }

    public function add(MiddlewareInterface $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    public function run(RequestInterface $request, callable $core): ResponseInterface
    {
        $handler = $core;

        $pipeline = $this->middleware;
        while ($mw = array_pop($pipeline)) {
            $handler = $this->wrapMiddleware($mw, $handler);
        }

        return $handler($request);
    }

    private function wrapMiddleware(MiddlewareInterface $middleware, callable $next): callable
    {
        return static fn (RequestInterface $request): ResponseInterface => $middleware->handle($request, $next);
    }
}
