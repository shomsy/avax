<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Support;

use Avax\HTTP\Dispatcher\ControllerDispatcher;
use Avax\HTTP\Request\Request;
use Closure;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * Manages the fallback handler invoked when no route matches.
 */
final class FallbackManager
{
    private Closure|array|string|null $handler = null;

    public function __construct(private ControllerDispatcher $dispatcher) {}

    public function set(callable|array|string $handler) : void
    {
        $this->handler = is_callable(value: $handler)
            ? Closure::fromCallable(callback: $handler)
            : $handler;
    }

    public function has() : bool
    {
        return $this->handler !== null;
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \ReflectionException
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function invoke(Request $request) : ResponseInterface
    {
        if (! $this->handler) {
            throw new RuntimeException(message: 'Fallback handler is not configured.');
        }

        if ($this->handler instanceof Closure) {
            return ($this->handler)($request);
        }

        return $this->dispatcher->dispatch(action: $this->handler, request: $request);
    }
}
