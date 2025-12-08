<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing;

use Closure;
use Avax\HTTP\Request\Request;
use Psr\Http\Message\ResponseInterface;

interface RouteStage
{
    /**
     * Executes logic before the next pipeline stage.
     *
     * @param Request                             $request
     * @param Closure(Request): ResponseInterface $next
     *
     * @return ResponseInterface
     */
    public function handle(Request $request, Closure $next) : ResponseInterface;
}