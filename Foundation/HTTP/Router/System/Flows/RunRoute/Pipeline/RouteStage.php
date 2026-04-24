<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\System\Flows\RunRoute\Pipeline;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Closure;
use Psr\Http\Message\ResponseInterface;

interface RouteStage
{
    /**
     * Executes logic before the next pipeline stage.
     *
     * @param Closure(ServerRequest): ResponseInterface $next
     */
    public function handle(ServerRequest $request, Closure $next) : ResponseInterface;
}
