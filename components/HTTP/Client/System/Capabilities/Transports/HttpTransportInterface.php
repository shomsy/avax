<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Client\System\Capabilities\Transports;

use Avax\Components\HTTP\Client\System\Capabilities\Requests\OutboundRequest;
use Avax\Components\HTTP\Client\System\Capabilities\Responses\ClientResponse;
use Avax\Components\HTTP\Client\System\Foundation\Failure\HttpRequestFailed;
use GuzzleHttp\Psr7\Request;

/**
 * HttpTransportInterface - Interface for HTTP transport backends.
 *
 * Defines the contract for sending outbound HTTP requests.
 * Implementations can use cURL, stream contexts, or other HTTP libraries.
 */
interface HttpTransportInterface
{
    /**
     * Send an HTTP request and return the response.
     *
     * @param OutboundRequest $request The outbound request to send
     * @return ClientResponse The HTTP response
     *
     * @throws HttpRequestFailed if the request cannot be completed
     */
    public function send(OutboundRequest $request): ClientResponse;

    /**
     * Build a PSR-7 request from the outbound request.
     *
     * This method is provided for compatibility with PSR-7 middleware
     * and other libraries that expect PSR-7 request objects.
     */
    public function buildPsr7Request(OutboundRequest $request): Request;
}
