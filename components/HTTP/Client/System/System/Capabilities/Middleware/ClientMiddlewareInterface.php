<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Client\System\System\Capabilities\Middleware;

use Avax\Components\HTTP\Client\System\System\Capabilities\Requests\OutboundRequest;
use Avax\Components\HTTP\Client\System\System\Capabilities\Responses\ClientResponse;
use Closure;

/**
 * ClientMiddlewareInterface - Interface for HTTP client middleware.
 *
 * Middleware wraps the request handler to add cross-cutting concerns
 * like logging, authentication, retries, and request/response transformation.
 *
 * Each middleware receives the request and a next handler, and must
 * return a ClientResponse. It can modify the request before passing
 * it to the next handler, and can modify the response before returning.
 */
interface ClientMiddlewareInterface
{
    /**
     * Process an HTTP request through this middleware.
     *
     * @param OutboundRequest                           $outboundRequest The outbound request
     * @param Closure(OutboundRequest) : ClientResponse $handler         The next handler in the chain
     *
     * @return ClientResponse The HTTP response
     */
    public function handle(OutboundRequest $outboundRequest, Closure $handler) : ClientResponse;
}
