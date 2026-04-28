<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Fallback;

use Avax\Components\HTTP\Dispatcher\ControllerDispatcher;
use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Closure;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * Manages the fallback handler invoked when no route matches.
 */
final class RegisteredFallback
{
    private Closure|array|string|null $handler = null;
    private ControllerDispatcher      $dispatcher;

    public function __construct(ControllerDispatcher $dispatcher) { $this->dispatcher = $dispatcher; }

    public function set(callable|array|string $handler) : void
    {
        $this->handler = is_callable($handler) ? Closure::fromCallable($handler) : $handler;
    }

    public function has() : bool { return $this->handler !== null; }

    public function invoke(ServerRequest $request) : ResponseInterface
    {
        if (! $this->handler) {
            throw new RuntimeException('Fallback handler is not configured.');
        }
        if ($this->handler instanceof Closure) {
            return ($this->handler)($request);
        }

        return $this->dispatcher->dispatch($this->handler, $request);
    }
}
