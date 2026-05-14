<?php

declare(strict_types=1);

namespace Avax\Tests\Support\Http\Client;

use Throwable;

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
        public string         $urlPattern,
        public string         $method = '*',
        public int            $statusCode = 200,
        public array          $headers = [],
        public string         $body = '',
        public float          $delayMs = 0.0,
        public bool           $useRegex = false,
        public Throwable|null $exception = null,
    ) {}

    /**
     * @param array<string, string> $headers
     */
    public static function ok(string $url, string $body = '', array $headers = []) : self
    {
        return new self(
            urlPattern: $url,
            method    : '*',
            statusCode: 200,
            headers   : $headers,
            body      : $body,
        );
    }

    public static function json(string $url, mixed $data = [], int $status = 200) : self
    {
        return new self(
            urlPattern: $url,
            method    : '*',
            statusCode: $status,
            headers   : ['Content-Type' => 'application/json'],
            body      : json_encode($data, JSON_THROW_ON_ERROR),
        );
    }

    public static function error(string $url, int $status = 500, string $message = 'Internal Server Error') : self
    {
        return new self(
            urlPattern: $url,
            method    : '*',
            statusCode: $status,
            body      : $message,
        );
    }

    public static function throws(string $url, Throwable $throwable) : self
    {
        return new self(
            urlPattern: $url,
            method    : '*',
            exception : $throwable,
        );
    }

    public static function delayed(string $url, float $delayMs) : self
    {
        return new self(
            urlPattern: $url,
            method    : '*',
            delayMs   : $delayMs,
        );
    }

    public function matches(string $url, string $method = 'GET') : bool
    {
        if ($this->method !== '*' && strtoupper($this->method) !== strtoupper($method)) {
            return false;
        }

        if ($this->useRegex) {
            return (bool) preg_match($this->urlPattern, $url);
        }

        return $this->urlPattern === $url;
    }
}
