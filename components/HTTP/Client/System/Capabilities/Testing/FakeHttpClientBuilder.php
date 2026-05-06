<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Client\System\Capabilities\Testing;

use Throwable;

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
    public function withBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * Start recording a response for a GET request.
     */
    public function whenGet(string $url): self
    {
        $this->pendingMethod = 'GET';
        $this->pendingUrl = $url;

        return $this;
    }

    /**
     * Start recording a response for a POST request.
     */
    public function whenPost(string $url): self
    {
        $this->pendingMethod = 'POST';
        $this->pendingUrl = $url;

        return $this;
    }

    /**
     * Start recording a response for a PUT request.
     */
    public function whenPut(string $url): self
    {
        $this->pendingMethod = 'PUT';
        $this->pendingUrl = $url;

        return $this;
    }

    /**
     * Start recording a response for a DELETE request.
     */
    public function whenDelete(string $url): self
    {
        $this->pendingMethod = 'DELETE';
        $this->pendingUrl = $url;

        return $this;
    }

    /**
     * Start recording a response for any method.
     */
    public function whenAny(string $url): self
    {
        $this->pendingMethod = '*';
        $this->pendingUrl = $url;

        return $this;
    }

    /**
     * Respond with a JSON response.
     *
     * @param  mixed  $data  Data to JSON encode
     * @param  int  $status  HTTP status code
     */
    public function respondWithJson(mixed $data = [], int $status = 200): self
    {
        $key = sprintf('%s:%s', $this->pendingMethod, $this->pendingUrl);
        $this->responses[$key] = RecordedHttpResponse::json($this->pendingUrl, $data, $status);

        return $this;
    }

    /**
     * Respond with a specific status code.
     */
    public function respondWithStatus(int $status = 200, string $body = ''): self
    {
        $key = sprintf('%s:%s', $this->pendingMethod, $this->pendingUrl);
        $this->responses[$key] = new RecordedHttpResponse(
            urlPattern: $this->pendingUrl,
            method: $this->pendingMethod,
            statusCode: $status,
            body: $body,
        );

        return $this;
    }

    /**
     * Respond with a custom recorded response.
     */
    public function respondWith(RecordedHttpResponse $recordedHttpResponse): self
    {
        $key = sprintf('%s:%s', $this->pendingMethod, $this->pendingUrl);
        $this->responses[$key] = $recordedHttpResponse;

        return $this;
    }

    /**
     * Respond with an exception.
     */
    public function respondWithException(Throwable $throwable): self
    {
        $key = sprintf('%s:%s', $this->pendingMethod, $this->pendingUrl);
        $this->responses[$key] = RecordedHttpResponse::throws($this->pendingUrl, $throwable);

        return $this;
    }

    /**
     * Add a raw response mapping.
     *
     * @param  array<string, RecordedHttpResponse>  $responses
     */
    public function withResponses(array $responses): self
    {
        foreach ($responses as $key => $response) {
            $this->responses[$key] = $response;
        }

        return $this;
    }

    /**
     * Build the FakeHttpClient instance.
     */
    public function build(): FakeHttpClient
    {
        return new FakeHttpClient(
            responses: $this->responses,
            baseUrl: $this->baseUrl,
        );
    }
}
