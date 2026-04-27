<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\HandleIncomingHttp;

use Closure;
use components\HTTP\Dispatcher\ControllerDispatcher;
use components\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Psr\Http\Message\ResponseInterface;

final readonly class RunHttpRoute
{
    public function __construct(private ControllerDispatcher $controllerDispatcher)
    {
    }

    public function run(MatchedHttpRoute $matchedRoute): ResponseInterface
    {
        return $this->controllerDispatcher->dispatch(
            action  : $matchedRoute->route()->action,
            request : $matchedRoute->request(),
        );
    }

    public function runFallback(Closure|array|string $fallback, ServerRequest $request): ResponseInterface
    {
        return $this->controllerDispatcher->dispatch(
            action  : $fallback,
            request : $request,
        );
    }
}
