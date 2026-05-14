<?php

declare(strict_types=1);

namespace Avax\Tests\Support\Http\Client;

use Avax\Components\HTTP\Client\System\Capabilities\Requests\OutboundRequest;
use Avax\Components\HTTP\Client\System\Capabilities\Responses\ClientResponse;
use Avax\Components\HTTP\Client\System\Foundation\Failure\HttpRequestFailed;
use Avax\Components\HTTP\Client\System\PublicSurface\HttpClientInterface;
use Throwable;

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

    public static function create() : FakeHttpClientBuilder
    {
        return new FakeHttpClientBuilder();
    }

    /**
     * @param array<string, RecordedHttpResponse> $responses
     */
    public static function fromResponses(array $responses, string|null $baseUrl = null) : self
    {
        return new self(responses: $responses, baseUrl: $baseUrl);
    }

    public function getBaseUrl() : ?string
    {
        return $this->baseUrl;
    }

    public function getDefaultTimeout() : int
    {
        return 30000;
    }

    public function get(string $url, array $headers = [], array $options = []) : ClientResponse
    {
        return $this->send(new OutboundRequest(
                               method : 'GET',
                               url    : $this->resolveUrl($url),
                               headers: $headers,
                           ));
    }

    public function send(OutboundRequest $outboundRequest) : ClientResponse
    {
        $this->recordedRequests[] = $outboundRequest;

        $matchingResponse = $this->findMatchingResponse($outboundRequest);

        if (! $matchingResponse instanceof RecordedHttpResponse) {
            throw new HttpRequestFailed(
                message: sprintf('No recorded response found for %s %s', $outboundRequest->method, $outboundRequest->url),
                url    : $outboundRequest->url,
                method : $outboundRequest->method,
            );
        }

        if ($matchingResponse->delayMs > 0) {
            usleep((int) ($matchingResponse->delayMs * 1000));
        }

        if ($matchingResponse->exception instanceof Throwable) {
            throw $matchingResponse->exception;
        }

        return ClientResponse::fromRaw(
            statusCode: $matchingResponse->statusCode,
            headers   : $matchingResponse->headers,
            body      : $matchingResponse->body,
        );
    }

    private function findMatchingResponse(OutboundRequest $outboundRequest) : ?RecordedHttpResponse
    {
        foreach ($this->responses as $response) {
            if ($response->matches($outboundRequest->url, $outboundRequest->method)) {
                return $response;
            }
        }

        return null;
    }

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

    public function post(string $url, mixed $body = null, array $headers = [], array $options = []) : ClientResponse
    {
        return $this->send(new OutboundRequest(
                               method : 'POST',
                               url    : $this->resolveUrl($url),
                               body   : $body,
                               headers: $headers,
                           ));
    }

    public function put(string $url, mixed $body = null, array $headers = [], array $options = []) : ClientResponse
    {
        return $this->send(new OutboundRequest(
                               method : 'PUT',
                               url    : $this->resolveUrl($url),
                               body   : $body,
                               headers: $headers,
                           ));
    }

    public function patch(string $url, mixed $body = null, array $headers = [], array $options = []) : ClientResponse
    {
        return $this->send(new OutboundRequest(
                               method : 'PATCH',
                               url    : $this->resolveUrl($url),
                               body   : $body,
                               headers: $headers,
                           ));
    }

    public function delete(string $url, array $headers = [], array $options = []) : ClientResponse
    {
        return $this->send(new OutboundRequest(
                               method : 'DELETE',
                               url    : $this->resolveUrl($url),
                               headers: $headers,
                           ));
    }

    public function head(string $url, array $headers = [], array $options = []) : ClientResponse
    {
        return $this->send(new OutboundRequest(
                               method : 'HEAD',
                               url    : $this->resolveUrl($url),
                               headers: $headers,
                           ));
    }

    public function options(string $url, array $headers = [], array $options = []) : ClientResponse
    {
        return $this->send(new OutboundRequest(
                               method : 'OPTIONS',
                               url    : $this->resolveUrl($url),
                               headers: $headers,
                           ));
    }

    /**
     * @return list<OutboundRequest>
     */
    public function getRecordedRequests() : array
    {
        return $this->recordedRequests;
    }

    public function getLastRequest() : ?OutboundRequest
    {
        return end($this->recordedRequests) ?: null;
    }

    public function getRequestCount() : int
    {
        return count($this->recordedRequests);
    }

    public function clearRequests() : void
    {
        $this->recordedRequests = [];
    }
}
