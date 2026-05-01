<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Client\System\Capabilities\Testing;

use Throwable;

/**
 * RecordedHttpResponse - A recorded HTTP response for testing.
 *
 * Used by FakeHttpClient to define expected responses for specific URLs.
 * Supports matching by URL and HTTP method.
 */
final readonly class RecordedHttpResponse
{
    /**
     * @param string                $urlPattern URL pattern to match (exact or regex)
     * @param string                $method     HTTP method to match (e.g., 'GET', 'POST')
     * @param int                   $statusCode HTTP status code to return
     * @param array<string, string> $headers    Headers to return
     * @param string                $body       Response body
     * @param float                 $delayMs    Artificial delay in milliseconds (for testing timeouts)
     * @param bool                  $useRegex   Whether urlPattern is a regex
     * @param Throwable|null        $exception  Exception to throw instead of returning a response
     */
    public function __construct(
        public string $urlPattern,
        public string $method = '*',
        public int   $statusCode = 200,
        public array $headers = [],
        public string $body = '',
        public float $delayMs = 0.0,
        public bool  $useRegex = false,
        public Throwable|null $exception = null,
    ) {}

    /**
     * Create a successful response recording.
     *
     * @param string $url URL to match
     * @param string $body Response body
     * @param array<string, string> $headers Headers to include
     */
    public static function ok(
        string $url,
        string $body = '',
        array $headers = [],
    ) : self
    {
        return new self(
            urlPattern: $url,
            method    : '*',
            statusCode: 200,
            body      : $body,
            headers   : $headers,
        );
    }

    /**
     * Create a JSON response recording.
     *
     * @param string $url    URL to match
     * @param mixed  $data   Data to JSON encode
     * @param int    $status HTTP status code
     */
    public static function json(
        string $url,
        mixed $data = [],
        int $status = 200,
    ) : self
    {
        return new self(
            urlPattern: $url,
            method    : '*',
            statusCode: $status,
            body      : json_encode($data, JSON_THROW_ON_ERROR),
            headers   : ['Content-Type' => 'application/json'],
        );
    }

    /**
     * Create an error response recording.
     *
     * @param string $url    URL to match
     * @param int    $status HTTP status code
     * @param string $message Error message in body
     */
    public static function error(
        string $url,
        int $status = 500,
        string $message = 'Internal Server Error',
    ) : self
    {
        return new self(
            urlPattern: $url,
            method    : '*',
            statusCode: $status,
            body      : $message,
        );
    }

    /**
     * Create a response recording that throws an exception.
     *
     * @param string $url URL to match
     * @param Throwable $exception Exception to throw
     */
    public static function throws(string $url, Throwable $exception) : self
    {
        return new self(
            urlPattern: $url,
            method    : '*',
            exception : $exception,
        );
    }

    /**
     * Create a response with an artificial delay.
     *
     * @param string $url     URL to match
     * @param float  $delayMs Delay in milliseconds
     */
    public static function delayed(string $url, float $delayMs) : self
    {
        return new self(
            urlPattern: $url,
            method    : '*',
            delayMs   : $delayMs,
        );
    }

    /**
     * Check if this recorded response matches the given request.
     *
     * @param string $url The request URL
     * @param string $method The request HTTP method
     */
    public function matches(string $url, string $method = 'GET') : bool
    {
        // Check method match (* matches all)
        if ($this->method !== '*' && strtoupper($this->method) !== strtoupper($method)) {
            return false;
        }

        // Check URL match
        if ($this->useRegex) {
            return (bool) preg_match($this->urlPattern, $url);
        }

        return $this->urlPattern === $url;
    }
}
