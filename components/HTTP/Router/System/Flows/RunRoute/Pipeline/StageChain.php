<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Flows\RunRoute\Pipeline;

use Avax\Components\Application\Container\DI\ContainerInterface;
use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\Components\HTTP\Router\System\Foundation\Exceptions\RouterException;
use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * Builds the middleware/stage execution chain.
 */
final class StageChain
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly LoggerInterface    $logger
    ) {}

    public function create(array $stages, array $middleware, Closure $core) : Closure
    {
        $pipeline = array_merge($stages, $middleware);

        if ($pipeline === []) {
            return $core;
        }

        return array_reduce(
            array_reverse($pipeline),
            fn (Closure $next, string $class) : Closure => fn (ServerRequest $request) : ResponseInterface => $this->invoke($class, $next, $request),
            $core
        );
    }

    private function invoke(string $class, Closure $next, ServerRequest $request) : ResponseInterface
    {
        $instance = $this->container->get($class);

        if (! method_exists($instance, 'handle')) {
            throw new RuntimeException("Middleware or stage [{$class}] must have a handle() method.");
        }

        return $instance->handle($request, $next);
    }
}
