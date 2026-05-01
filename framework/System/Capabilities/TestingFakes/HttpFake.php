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
     * @var list<array{method: string, url: string, options: array<string, mixed>}>
     */
    private array $requests = [];

    /**
     * @var array<string, array{status: int, body: string, headers: array<string, list<string>>}>
     */
    private array $stubs = [];

    /**
     * @var array{status: int, body: string, headers: array<string, list<string>>}|null
     */
    private array|null $defaultResponse = null;

    /**
     * @param array<string, mixed> $options
     *
     * @return array{status: int, body: string, headers: array<string, list<string>>}
     */
    public function get(string $url, array $options = []) : array
    {
        return $this->recordRequest('GET', $url, $options);
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array{status: int, body: string, headers: array<string, list<string>>}
     */
    private function recordRequest(string $method, string $url, array $options) : array
    {
        $this->requests[] = ['method' => $method, 'url' => $url, 'options' => $options];

        $stubKey = "{$method}:{$url}";

        if (isset($this->stubs[$stubKey])) {
            return $this->stubs[$stubKey];
        }

        return $this->defaultResponse ?? ['status' => 200, 'body' => '', 'headers' => []];
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array{status: int, body: string, headers: array<string, list<string>>}
     */
    public function post(string $url, array $options = []) : array
    {
        return $this->recordRequest('POST', $url, $options);
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array{status: int, body: string, headers: array<string, list<string>>}
     */
    public function put(string $url, array $options = []) : array
    {
        return $this->recordRequest('PUT', $url, $options);
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array{status: int, body: string, headers: array<string, list<string>>}
     */
    public function delete(string $url, array $options = []) : array
    {
        return $this->recordRequest('DELETE', $url, $options);
    }

    /**
     * @param array<string, list<string>> $headers
     */
    public function stub(string $method, string $url, int $status = 200, string $body = '', array $headers = []) : self
    {
        $this->stubs["{$method}:{$url}"] = [
            'status'  => $status,
            'body'    => $body,
            'headers' => $headers,
        ];

        return $this;
    }

    /**
     * @param array<string, list<string>> $headers
     */
    public function defaultResponse(int $status = 200, string $body = '', array $headers = []) : self
    {
        $this->defaultResponse = ['status' => $status, 'body' => $body, 'headers' => $headers];

        return $this;
    }

    public function assertSent(string $method, string $url = null) : self
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
                    'HTTP %s %s was not sent',
                    strtoupper($method),
                    $url ?? 'request',
                ),
            );
        }

        return $this;
    }

    public function assertNotSent(string $method, string $url = null) : self
    {
        foreach ($this->requests as $request) {
            if (strtoupper($request['method']) === strtoupper($method)) {
                if ($url === null || $request['url'] === $url) {
                    throw new TestingFakeException(
                        sprintf(
                            'HTTP %s %s was sent unexpectedly',
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
                    'Expected %d HTTP requests, got %d',
                    $count,
                    $actual,
                ),
            );
        }

        return $this;
    }

    /**
     * @return list<array{method: string, url: string, options: array<string, mixed>}>
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
