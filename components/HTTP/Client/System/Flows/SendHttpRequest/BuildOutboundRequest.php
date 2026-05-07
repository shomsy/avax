<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Client\System\Flows\SendHttpRequest;

use Avax\Components\HTTP\Client\System\Capabilities\Requests\OutboundRequest;
use GuzzleHttp\Psr7\Request;

/**
 * BuildOutboundRequest - Builds a PSR-7 request from an OutboundRequest.
 *
 * Transforms the framework's OutboundRequest value object into a
 * PSR-7 RequestInterface compatible object for transport layers
 * that work with PSR-7 standards.
 */
final class BuildOutboundRequest
{
    /**
     * Build a PSR-7 request from an OutboundRequest.
     *
     * @param OutboundRequest $outboundRequest The outbound request
     *
     * @return Request The PSR-7 request
     */
    public function build(OutboundRequest $outboundRequest) : Request
    {
        $body    = $this->normalizeBody($outboundRequest->body);
        $headers = $this->prepareHeaders($outboundRequest);

        return new Request(
            $outboundRequest->method,
            $outboundRequest->url,
            $headers,
            $body,
        );
    }

    /**
     * Normalize the request body to a string.
     */
    private function normalizeBody(mixed $body) : string
    {
        if ($body === null) {
            return '';
        }

        if (is_string($body)) {
            return $body;
        }

        if (is_array($body)) {
            return json_encode($body, JSON_THROW_ON_ERROR);
        }

        if (is_resource($body)) {
            $contents = stream_get_contents($body);

            return $contents !== false ? $contents : '';
        }

        return (string) $body;
    }

    /**
     * Prepare headers for the request.
     *
     * Adds Content-Type header if body is JSON and no Content-Type is set.
     *
     * @return array<string, string>
     */
    private function prepareHeaders(OutboundRequest $outboundRequest) : array
    {
        $headers = $outboundRequest->headers;

        // Auto-add Content-Type for JSON bodies
        if (
            $outboundRequest->body !== null
            && is_array($outboundRequest->body)
            && ! isset($headers['Content-Type'])
        ) {
            $headers['Content-Type'] = 'application/json';
        }

        // Auto-add Accept header if not set
        if (! isset($headers['Accept'])) {
            $headers['Accept'] = '*/*';
        }

        return $headers;
    }
}
