<?php

declare(strict_types=1);

namespace Avax\Components\Middleware\System\Capabilities\Pipeline;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Immutable middleware pipeline following PSR-15.
 */
final readonly class MiddlewarePipeline
{
    /**
     * @param array    $middleware   List of PSR-15 middleware
     * @param callable $finalHandler Final handler to execute (request -> response)
     */
    public function __construct(
        private array $middleware = [],
        private mixed $finalHandler = null
    ) {}

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
        $handler = new class($this->finalHandler) implements RequestHandlerInterface {
            private $handler;

            public function __construct($handler) { $this->handler = $handler; }

            public function handle(ServerRequestInterface $request) : ResponseInterface
            {
                return ($this->handler)($request);
            }
        };

        foreach (array_reverse($this->middleware) as $middleware) {
            $handler = new class($middleware, $handler) implements RequestHandlerInterface {
                private $middleware;
                private $next;

                public function __construct($middleware, $next)
                {
                    $this->middleware = $middleware;
                    $this->next       = $next;
                }

                public function handle(ServerRequestInterface $request) : ResponseInterface
                {
                    return $this->middleware->process($request, $this->next);
                }
            };
        }

        return $handler->handle($request);
    }
}