<?php

declare(strict_types=1);

namespace Avax\Components\Middleware\System\Capabilities\Pipeline;

use Avax\HTTP\Middleware\MiddlewareGroupResolver;
use Avax\HTTP\Middleware\MiddlewareInterface;
use Avax\HTTP\Middleware\MiddlewareRegistry;
use Avax\HTTP\Middleware\MiddlewareResolver;
use Avax\HTTP\Middleware\RequestHandlerInterface;
use components\HTTP\Middleware\Psr15MiddlewarePipeline;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Middleware Pipeline — delegates to the real PSR-15 pipeline.
 *
 * The real implementation at HTTP/Middleware/ contains:
 * - Psr15MiddlewarePipeline: Immutable middleware chain with inside-out execution
 * - MiddlewareRegistry: Centralized registry with priority hints and categories
 * - MiddlewareResolver: Resolves identifiers (class names or group aliases)
 * - MiddlewareGroupResolver: Recursive group resolution
 * - 7+ concrete middleware (CORS, CSRF, RateLimiter, Tracing, Logger, Session, IP, JSON)
 *
 * This class provides the canonical Screaming Architecture entry point,
 * backed by the full HTTP/Middleware engine.
 */
final class MiddlewarePipeline
{
    private Psr15MiddlewarePipeline $pipeline;

    public function __construct(
        private readonly array $middleware = [],
        private readonly mixed $finalHandler = null,
    )
    {
        $handler = $this->wrapFinalHandler($this->finalHandler);

        $this->pipeline = Psr15MiddlewarePipeline::create($handler);

        foreach ($this->middleware as $mw) {
            $this->pipeline = $this->pipeline->withMiddleware($mw);
        }
    }

    /**
     * Create a pipeline with middleware resolved from the registry.
     *
     * @param string[]                $identifiers  Registry identifiers (e.g. 'cors', 'csrf', 'rate-limit')
     * @param RequestHandlerInterface $finalHandler Final handler
     */
    public static function fromRegistry(array $identifiers, RequestHandlerInterface $finalHandler) : self
    {
        $instances = [];
        foreach ($identifiers as $id) {
            if (MiddlewareRegistry::has($id)) {
                $instances[] = MiddlewareRegistry::create($id);
            }
        }

        return new self($instances, $finalHandler);
    }

    public function withMiddleware(object $middleware) : self
    {
        return new self([...$this->middleware, $middleware], $this->finalHandler);
    }

    public function withFinalHandler(callable $handler) : self
    {
        return new self($this->middleware, $handler);
    }

    public function execute(ServerRequestInterface $request) : ResponseInterface
    {
        return $this->pipeline->handle($request);
    }

    /**
     * Get ordered middleware identifiers by priority.
     */
    public static function defaultPriorityOrder() : array
    {
        return MiddlewareRegistry::getPriorityHints();
    }

    private function wrapFinalHandler(mixed $handler) : RequestHandlerInterface
    {
        if ($handler instanceof RequestHandlerInterface) {
            return $handler;
        }

        return new class($handler) implements RequestHandlerInterface {
            public function __construct(private readonly mixed $handler) {}

            public function handle(RequestInterface $request) : ResponseInterface
            {
                return ($this->handler)($request);
            }
        };
    }
}