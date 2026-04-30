<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\TestingFakes;

/**
 * Fake HTTP client for testing.
 *
 * Records all requests and allows stubbing responses.
 */
final class HttpFake
{
    /**
     * @var list<array{method: string, url: string, options: array}>
     */
    private array $requests = [];

    /**
     * @var array<string, array{status: int, body: string, headers: array}>
     */
    private array $stubs = [];

    private array|null $defaultResponse = null;

    public function get(string $url, array $options = []) : array
    {
        return $this->recordRequest('GET', $url, $options);
    }

    private function recordRequest(string $method, string $url, array $options) : array
    {
        $this->requests[] = ['method' => $method, 'url' => $url, 'options' => $options];

        $stubKey = "{$method}:{$url}";

        if (isset($this->stubs[$stubKey])) {
            return $this->stubs[$stubKey];
        }

        return $this->defaultResponse ?? ['status' => 200, 'body' => '', 'headers' => []];
    }

    public function post(string $url, array $options = []) : array
    {
        return $this->recordRequest('POST', $url, $options);
    }

    public function put(string $url, array $options = []) : array
    {
        return $this->recordRequest('PUT', $url, $options);
    }

    public function delete(string $url, array $options = []) : array
    {
        return $this->recordRequest('DELETE', $url, $options);
    }

    public function stub(string $method, string $url, int $status = 200, string $body = '', array $headers = []) : self
    {
        $this->stubs["{$method}:{$url}"] = [
            'status'  => $status,
            'body'    => $body,
            'headers' => $headers,
        ];

        return $this;
    }

    public function defaultResponse(int $status = 200, string $body = '', array $headers = []) : self
    {
        $this->defaultResponse = ['status' => $status, 'body' => $body, 'headers' => $headers];

        return $this;
    }

    public function assertSent(string $method, string|null $url = null) : self
    {
        $found = false;

        foreach ($this->requests as $request) {
            if (strtoupper($request['method']) === strtoupper($method)) {
                if ($url === null || $request['url'] === $url) {
                    $found = true;
                    break;
                }
            }
        }

        if (! $found) {
            throw new TestingFakeException(
                sprintf(
                    "HTTP %s %s was not sent",
                    strtoupper($method),
                    $url ?? 'request',
                ),
            );
        }

        return $this;
    }

    public function assertNotSent(string $method, string|null $url = null) : self
    {
        foreach ($this->requests as $request) {
            if (strtoupper($request['method']) === strtoupper($method)) {
                if ($url === null || $request['url'] === $url) {
                    throw new TestingFakeException(
                        sprintf(
                            "HTTP %s %s was sent unexpectedly",
                            strtoupper($method),
                            $url ?? 'request',
                        ),
                    );
                }
            }
        }

        return $this;
    }

    public function assertSentCount(int $count) : self
    {
        $actual = count($this->requests);

        if ($actual !== $count) {
            throw new TestingFakeException(
                sprintf(
                    "Expected %d HTTP requests, got %d",
                    $count,
                    $actual,
                ),
            );
        }

        return $this;
    }

    /**
     * @return list<array{method: string, url: string, options: array}>
     */
    public function requests() : array
    {
        return $this->requests;
    }

    public function clear() : void
    {
        $this->requests        = [];
        $this->stubs           = [];
        $this->defaultResponse = null;
    }
}
