<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\HandleIncomingHttp;

use Avax\Components\HTTP\Dispatcher\System\PublicSurface\ControllerDispatcher;
use Avax\Components\HTTP\Request\System\Capabilities\IncomingRequest\ServerRequest;
use Closure;
use Psr\Http\Message\ResponseInterface;

final readonly class RunHttpRoute
{
    public function __construct(private ControllerDispatcher $controllerDispatcher)
    {
    }

    public function run(MatchedHttpRoute $matchedHttpRoute): ResponseInterface
    {
        return $this->controllerDispatcher->dispatch(
            action       : $matchedHttpRoute->route()->action(),
            serverRequest: $matchedHttpRoute->request(),
        );
    }

    /**
     * @param  Closure|array<mixed>|string  $fallback
     */
    public function runFallback(Closure|array|string $fallback, ServerRequest $serverRequest): ResponseInterface
    {
        return $this->controllerDispatcher->dispatch(
            action       : $fallback,
            serverRequest: $serverRequest,
        );
    }
}
