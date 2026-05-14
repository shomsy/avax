<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Capabilities\MiddlewarePipeline;

use Avax\Components\Application\Container\System\Capabilities\ResolveCallable\ResolveCallable;
use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;

final readonly class BuildPipeline
{
    public function __construct(
        private ResolveCallable $callableResolver,
    ) {}

    /**
     * @param list<string|callable> $middleware
     */
    public function build(callable $handler, array $middleware) : callable
    {
        if ($middleware === []) {
            return $handler;
        }

        $pipeline = $handler;
        foreach (array_reverse($middleware) as $middlewareItem) {
            if (is_string($middlewareItem)) {
                $middlewareItem = $this->callableResolver->resolve($middlewareItem);
            }

            $next     = $pipeline;
            $mw       = $middlewareItem;
            $pipeline = static function (RequestInterface $req) use ($mw, $next) : ResponseInterface {
                $result = $mw($req, $next);
                if ($result instanceof ResponseInterface) {
                    return $result;
                }

                return $next($req);
            };
        }

        return $pipeline;
    }
}
