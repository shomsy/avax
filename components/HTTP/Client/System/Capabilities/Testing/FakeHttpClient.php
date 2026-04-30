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
        private array $responses = [],
        private array $recordedRequests = [],
        private ?string $baseUrl = null,
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
    public static function fromResponses(array $responses, string $baseUrl = null) : self
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
    public function send(OutboundRequest $request) : ClientResponse
    {
        // Record this request
        $this->recordedRequests[] = $request;

        // Apply simulated delay
        $matchingResponse = $this->findMatchingResponse($request);

        if ($matchingResponse === null) {
            throw new HttpRequestFailed(
                message: "No recorded response found for {$request->method} {$request->url}",
                url    : $request->url,
                method : $request->method,
            );
        }

        // Simulate delay if configured
        if ($matchingResponse->delayMs > 0) {
            usleep((int) ($matchingResponse->delayMs * 1000));
        }

        // Throw exception if configured
        if ($matchingResponse->exception !== null) {
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
    private function findMatchingResponse(OutboundRequest $request) : ?RecordedHttpResponse
    {
        foreach ($this->responses as $response) {
            if ($response->matches($request->url, $request->method)) {
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

/**
 * FakeHttpClientBuilder - Fluent builder for FakeHttpClient.
 */
final class FakeHttpClientBuilder
{
    /**
     * @var array<string, RecordedHttpResponse>
     */
    private array $responses = [];

    private ?string $baseUrl = null;

    private string $pendingMethod = '*';

    private string $pendingUrl = '';

    /**
     * Set the base URL for the client.
     */
    public function withBaseUrl(string $baseUrl) : self
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * Start recording a response for a GET request.
     */
    public function whenGet(string $url) : self
    {
        $this->pendingMethod = 'GET';
        $this->pendingUrl    = $url;

        return $this;
    }

    /**
     * Start recording a response for a POST request.
     */
    public function whenPost(string $url) : self
    {
        $this->pendingMethod = 'POST';
        $this->pendingUrl    = $url;

        return $this;
    }

    /**
     * Start recording a response for a PUT request.
     */
    public function whenPut(string $url) : self
    {
        $this->pendingMethod = 'PUT';
        $this->pendingUrl    = $url;

        return $this;
    }

    /**
     * Start recording a response for a DELETE request.
     */
    public function whenDelete(string $url) : self
    {
        $this->pendingMethod = 'DELETE';
        $this->pendingUrl    = $url;

        return $this;
    }

    /**
     * Start recording a response for any method.
     */
    public function whenAny(string $url) : self
    {
        $this->pendingMethod = '*';
        $this->pendingUrl    = $url;

        return $this;
    }

    /**
     * Respond with a JSON response.
     *
     * @param mixed $data   Data to JSON encode
     * @param int   $status HTTP status code
     */
    public function respondWithJson(mixed $data = [], int $status = 200) : self
    {
        $key                   = "{$this->pendingMethod}:{$this->pendingUrl}";
        $this->responses[$key] = RecordedHttpResponse::json($this->pendingUrl, $data, $status);

        return $this;
    }

    /**
     * Respond with a specific status code.
     */
    public function respondWithStatus(int $status = 200, string $body = '') : self
    {
        $key                   = "{$this->pendingMethod}:{$this->pendingUrl}";
        $this->responses[$key] = new RecordedHttpResponse(
            urlPattern: $this->pendingUrl,
            method    : $this->pendingMethod,
            statusCode: $status,
            body      : $body,
        );

        return $this;
    }

    /**
     * Respond with a custom recorded response.
     */
    public function respondWith(RecordedHttpResponse $response) : self
    {
        $key                   = "{$this->pendingMethod}:{$this->pendingUrl}";
        $this->responses[$key] = $response;

        return $this;
    }

    /**
     * Respond with an exception.
     */
    public function respondWithException(Throwable $exception) : self
    {
        $key                   = "{$this->pendingMethod}:{$this->pendingUrl}";
        $this->responses[$key] = RecordedHttpResponse::throws($this->pendingUrl, $exception);

        return $this;
    }

    /**
     * Add a raw response mapping.
     *
     * @param array<string, RecordedHttpResponse> $responses
     */
    public function withResponses(array $responses) : self
    {
        foreach ($responses as $key => $response) {
            $this->responses[$key] = $response;
        }

        return $this;
    }

    /**
     * Build the FakeHttpClient instance.
     */
    public function build() : FakeHttpClient
    {
        return new FakeHttpClient(
            responses: $this->responses,
            baseUrl  : $this->baseUrl,
        );
    }
}
