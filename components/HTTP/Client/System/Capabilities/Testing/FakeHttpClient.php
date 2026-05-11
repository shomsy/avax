<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Client\System\Capabilities\Testing;

use Avax\Components\HTTP\Client\System\Capabilities\Requests\OutboundRequest;
use Avax\Components\HTTP\Client\System\Capabilities\Responses\ClientResponse;
use Avax\Components\HTTP\Client\System\Foundation\Failure\HttpRequestFailed;
use Avax\Components\HTTP\Client\System\PublicSurface\HttpClientInterface;
use Throwable;

/**
 * FakeHttpClient - Fake HTTP client for testing.
 *
 * Accepts an array of URL => response mappings and returns recorded responses
 * instead of making real HTTP requests. Useful for unit and integration testing.
 *
 * Usage:
 *   $client = new FakeHttpClient([
 *       'https://api.example.com/users' => RecordedHttpResponse::json(['id' => 1, 'name' => 'John']),
 *       'https://api.example.com/posts' => RecordedHttpResponse::error('https://api.example.com/posts', 500),
 *   ]);
 *
 *   // Or use the fluent builder:
 *   $client = FakeHttpClient::create()
 *       ->whenGet('https://api.example.com/users')->respondWithJson(['id' => 1])
 *       ->whenPost('https://api.example.com/users')->respondWithStatus(201)
 *       ->build();
 */
final class FakeHttpClient implements HttpClientInterface
{
    /**
     * @param array<string, RecordedHttpResponse> $responses        URL pattern => response mappings
     * @param list<OutboundRequest>               $recordedRequests All requests made through this client
     */
    public function __construct(
        private readonly array   $responses = [],
        private array            $recordedRequests = [],
        private readonly ?string $baseUrl = null,
    ) {}

    /**
     * Create a new FakeHttpClient builder.
     */
    public static function create() : FakeHttpClientBuilder
    {
        return new FakeHttpClientBuilder();
    }

    /**
     * Create from URL => response mappings.
     *
     * @param array<string, RecordedHttpResponse> $responses
     */
    public static function fromResponses(array $responses, string|null $baseUrl = null) : self
    {
        return new self(responses: $responses, baseUrl: $baseUrl);
    }

    /**
     * Get the base URL configured for this client.
     */
    public function getBaseUrl() : ?string
    {
        return $this->baseUrl;
    }

    /**
     * Get the default timeout in milliseconds.
     */
    public function getDefaultTimeout() : int
    {
        return 30000;
    }

    /**
     * Send a GET request.
     */
    public function get(string $url, array $headers = [], array $options = []) : ClientResponse
    {
        return $this->send(new OutboundRequest(
                               method : 'GET',
                               url    : $this->resolveUrl($url),
                               headers: $headers,
                           ));
    }

    /**
     * Send a fully customized outbound request.
     *
     * Records the request and returns the matching recorded response.
     *
     * @throws HttpRequestFailed if no matching response is found
     * @throws Throwable if the recorded response has an exception
     */
    public function send(OutboundRequest $outboundRequest) : ClientResponse
    {
        // Record this request
        $this->recordedRequests[] = $outboundRequest;

        // Apply simulated delay
        $matchingResponse = $this->findMatchingResponse($outboundRequest);

        if (! $matchingResponse instanceof RecordedHttpResponse) {
            throw new HttpRequestFailed(
                message: sprintf('No recorded response found for %s %s', $outboundRequest->method, $outboundRequest->url),
                url    : $outboundRequest->url,
                method : $outboundRequest->method,
            );
        }

        // Simulate delay if configured
        if ($matchingResponse->delayMs > 0) {
            usleep((int) ($matchingResponse->delayMs * 1000));
        }

        // Throw exception if configured
        if ($matchingResponse->exception instanceof Throwable) {
            throw $matchingResponse->exception;
        }

        return ClientResponse::fromRaw(
            statusCode: $matchingResponse->statusCode,
            headers   : $matchingResponse->headers,
            body      : $matchingResponse->body,
        );
    }

    /**
     * Find a matching recorded response for the given request.
     */
    private function findMatchingResponse(OutboundRequest $outboundRequest) : ?RecordedHttpResponse
    {
        foreach ($this->responses as $response) {
            if ($response->matches($outboundRequest->url, $outboundRequest->method)) {
                return $response;
            }
        }

        return null;
    }

    /**
     * Resolve a URL against the base URL.
     */
    private function resolveUrl(string $url) : string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        if ($this->baseUrl === null) {
            return $url;
        }

        return rtrim($this->baseUrl, '/') . '/' . ltrim($url, '/');
    }

    /**
     * Send a POST request.
     */
    public function post(string $url, mixed $body = null, array $headers = [], array $options = []) : ClientResponse
    {
        return $this->send(new OutboundRequest(
                               method : 'POST',
                               url    : $this->resolveUrl($url),
                               body   : $body,
                               headers: $headers,
                           ));
    }

    /**
     * Send a PUT request.
     */
    public function put(string $url, mixed $body = null, array $headers = [], array $options = []) : ClientResponse
    {
        return $this->send(new OutboundRequest(
                               method : 'PUT',
                               url    : $this->resolveUrl($url),
                               body   : $body,
                               headers: $headers,
                           ));
    }

    /**
     * Send a PATCH request.
     */
    public function patch(string $url, mixed $body = null, array $headers = [], array $options = []) : ClientResponse
    {
        return $this->send(new OutboundRequest(
                               method : 'PATCH',
                               url    : $this->resolveUrl($url),
                               body   : $body,
                               headers: $headers,
                           ));
    }

    /**
     * Send a DELETE request.
     */
    public function delete(string $url, array $headers = [], array $options = []) : ClientResponse
    {
        return $this->send(new OutboundRequest(
                               method : 'DELETE',
                               url    : $this->resolveUrl($url),
                               headers: $headers,
                           ));
    }

    /**
     * Send a HEAD request.
     */
    public function head(string $url, array $headers = [], array $options = []) : ClientResponse
    {
        return $this->send(new OutboundRequest(
                               method : 'HEAD',
                               url    : $this->resolveUrl($url),
                               headers: $headers,
                           ));
    }

    /**
     * Send an OPTIONS request.
     */
    public function options(string $url, array $headers = [], array $options = []) : ClientResponse
    {
        return $this->send(new OutboundRequest(
                               method : 'OPTIONS',
                               url    : $this->resolveUrl($url),
                               headers: $headers,
                           ));
    }

    /**
     * Get all recorded requests.
     *
     * @return list<OutboundRequest>
     */
    public function getRecordedRequests() : array
    {
        return $this->recordedRequests;
    }

    /**
     * Get the last recorded request.
     */
    public function getLastRequest() : ?OutboundRequest
    {
        return end($this->recordedRequests) ?: null;
    }

    /**
     * Get the number of requests made.
     */
    public function getRequestCount() : int
    {
        return count($this->recordedRequests);
    }

    /**
     * Clear all recorded requests.
     */
    public function clearRequests() : void
    {
        $this->recordedRequests = [];
    }
}
