<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Flows\ResolveRequest\Matching;

use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;

/**
 * Interface for route matching strategies.
 */
interface RouteMatcherInterface
{
    /**
     * @return array|null [RouteDefinition, matches]
     */
    public function match(array $routes, ServerRequest $request) : array|null;
}
