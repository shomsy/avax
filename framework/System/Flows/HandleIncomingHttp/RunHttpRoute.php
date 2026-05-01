<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\HandleIncomingHttp;

use Closure;
use components\HTTP\Dispatcher\ControllerDispatcher;
use components\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Psr\Http\Message\ResponseInterface;

final readonly class RunHttpRoute
{
    public function __construct(private ControllerDispatcher $controllerDispatcher) {}

    public function run(MatchedHttpRoute $matchedHttpRoute) : ResponseInterface
    {
        return $this->controllerDispatcher->dispatch(
            action : $matchedHttpRoute->route()->action,
            request: $matchedHttpRoute->request(),
        );
    }

    public function runFallback(Closure|array|string $fallback, ServerRequest $serverRequest) : ResponseInterface
    {
        return $this->controllerDispatcher->dispatch(
            action  : $fallback,
            request : $serverRequest,
        );
    }
}
