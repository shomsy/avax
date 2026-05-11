<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Client\System\Capabilities\Testing;

use Avax\Components\HTTP\Client\System\Foundation\Failure\HttpRequestFailed;
use Throwable;

final class FakeHttpClientBuilder
{
    /**
     * @var array<string, RecordedHttpResponse>
     */
    private array $responses = [];

    private string|null $baseUrl = null;

    private string $pendingMethod = '*';

    private string $pendingUrl = '';

    public function withBaseUrl(string $baseUrl) : self
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    public function whenGet(string $url) : self
    {
        $this->pendingMethod = 'GET';
        $this->pendingUrl    = $url;

        return $this;
    }

    public function whenPost(string $url) : self
    {
        $this->pendingMethod = 'POST';
        $this->pendingUrl    = $url;

        return $this;
    }

    public function whenPut(string $url) : self
    {
        $this->pendingMethod = 'PUT';
        $this->pendingUrl    = $url;

        return $this;
    }

    public function whenDelete(string $url) : self
    {
        $this->pendingMethod = 'DELETE';
        $this->pendingUrl    = $url;

        return $this;
    }

    public function whenAny(string $url) : self
    {
        $this->pendingMethod = '*';
        $this->pendingUrl    = $url;

        return $this;
    }

    public function respondWithJson(mixed $data = [], int $status = 200) : self
    {
        $key                   = sprintf('%s:%s', $this->pendingMethod, $this->pendingUrl);
        $this->responses[$key] = RecordedHttpResponse::json($this->pendingUrl, $data, $status);

        return $this;
    }

    public function respondWithStatus(int $status = 200, string $body = '') : self
    {
        $key                   = sprintf('%s:%s', $this->pendingMethod, $this->pendingUrl);
        $this->responses[$key] = new RecordedHttpResponse(
            urlPattern: $this->pendingUrl,
            method    : $this->pendingMethod,
            statusCode: $status,
            body      : $body,
        );

        return $this;
    }

    public function respondWith(RecordedHttpResponse $recordedHttpResponse) : self
    {
        $key                   = sprintf('%s:%s', $this->pendingMethod, $this->pendingUrl);
        $this->responses[$key] = $recordedHttpResponse;

        return $this;
    }

    public function respondWithException(Throwable $throwable) : self
    {
        $key                   = sprintf('%s:%s', $this->pendingMethod, $this->pendingUrl);
        $this->responses[$key] = RecordedHttpResponse::throws($this->pendingUrl, $throwable);

        return $this;
    }

    /**
     * @param array<string, RecordedHttpResponse> $responses
     */
    public function withResponses(array $responses) : self
    {
        foreach ($responses as $key => $response) {
            $this->responses[$key] = $response;
        }

        return $this;
    }

    public function build() : FakeHttpClient
    {
        return new FakeHttpClient(
            responses: $this->responses,
            baseUrl  : $this->baseUrl,
        );
    }
}
