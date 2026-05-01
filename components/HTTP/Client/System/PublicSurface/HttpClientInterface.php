<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Client\System\PublicSurface;

use Avax\Components\HTTP\Client\System\Capabilities\Requests\OutboundRequest;
use Avax\Components\HTTP\Client\System\Capabilities\Responses\ClientResponse;
use Avax\Components\HTTP\Client\System\Foundation\Failure\HttpRequestFailed;

/**
 * Interface defining the HTTP client contract.
 *
 * Provides methods for all standard HTTP verbs and a generic send() method
 * for full control over outbound requests.
 */
interface HttpClientInterface
{
    /**
     * Send a GET request.
     *
     * @param string $url The URL to request
     * @param array<string, string> $headers Additional headers
     * @param array<string, mixed> $options Request options
     *
     * @throws HttpRequestFailed if the request fails
     */
    public function get(
        string $url,
        array $headers = [],
        array $options = [],
    ) : ClientResponse;

    /**
     * Send a POST request.
     *
     * @param string $url  The URL to request
     * @param mixed  $body The request body
     * @param array<string, string> $headers Additional headers
     * @param array<string, mixed> $options Request options
     *
     * @throws HttpRequestFailed if the request fails
     */
    public function post(
        string $url,
        mixed $body = null,
        array $headers = [],
        array $options = [],
    ) : ClientResponse;

    /**
     * Send a PUT request.
     *
     * @param string $url  The URL to request
     * @param mixed  $body The request body
     * @param array<string, string> $headers Additional headers
     * @param array<string, mixed> $options Request options
     *
     * @throws HttpRequestFailed if the request fails
     */
    public function put(
        string $url,
        mixed $body = null,
        array $headers = [],
        array $options = [],
    ) : ClientResponse;

    /**
     * Send a PATCH request.
     *
     * @param string $url  The URL to request
     * @param mixed  $body The request body
     * @param array<string, string> $headers Additional headers
     * @param array<string, mixed> $options Request options
     *
     * @throws HttpRequestFailed if the request fails
     */
    public function patch(
        string $url,
        mixed $body = null,
        array $headers = [],
        array $options = [],
    ) : ClientResponse;

    /**
     * Send a DELETE request.
     *
     * @param string $url The URL to request
     * @param array<string, string> $headers Additional headers
     * @param array<string, mixed> $options Request options
     *
     * @throws HttpRequestFailed if the request fails
     */
    public function delete(
        string $url,
        array $headers = [],
        array $options = [],
    ) : ClientResponse;

    /**
     * Send a HEAD request.
     *
     * @param string $url The URL to request
     * @param array<string, string> $headers Additional headers
     * @param array<string, mixed> $options Request options
     *
     * @throws HttpRequestFailed if the request fails
     */
    public function head(
        string $url,
        array $headers = [],
        array $options = [],
    ) : ClientResponse;

    /**
     * Send an OPTIONS request.
     *
     * @param string $url The URL to request
     * @param array<string, string> $headers Additional headers
     * @param array<string, mixed> $options Request options
     *
     * @throws HttpRequestFailed if the request fails
     */
    public function options(
        string $url,
        array $headers = [],
        array $options = [],
    ) : ClientResponse;

    /**
     * Send a fully customized outbound request.
     *
     * @param OutboundRequest $request The outbound request to send
     *
     * @throws HttpRequestFailed if the request fails
     */
    public function send(OutboundRequest $request) : ClientResponse;

    /**
     * Get the base URL configured for this client.
     */
    public function getBaseUrl() : string|null;

    /**
     * Get the default timeout in milliseconds.
     */
    public function getDefaultTimeout() : int;
}
