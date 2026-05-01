<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Client\System\Capabilities\Responses;

use JsonException;

/**
 * ClientResponse - Value object representing an HTTP response.
 *
 * Immutable value object that captures the complete HTTP response including
 * status code, headers, body content, and timing information.
 */
final readonly class ClientResponse
{
    /**
     * @param int $statusCode HTTP status code (e.g., 200, 404, 500)
     * @param array<string, list<string>> $headers Response headers (name => [values])
     * @param string $body Raw response body
     * @param string $reasonPhrase HTTP reason phrase (e.g., "OK", "Not Found")
     * @param string $protocol HTTP protocol version (e.g., "1.1", "2.0")
     * @param float $transferTimeMs Transfer time in milliseconds
     * @param float $connectTimeMs Connection time in milliseconds
     * @param float $totalTimeMs Total time in milliseconds
     * @param int $redirectCount Number of redirects followed
     * @param string|null $effectiveUrl Final URL after redirects
     * @param array<string, mixed> $context Additional response context
     */
    public function __construct(
        public int $statusCode = 200,
        public array $headers = [],
        public string $body = '',
        public string $reasonPhrase = 'OK',
        public string $protocol = '1.1',
        public float $transferTimeMs = 0.0,
        public float $connectTimeMs = 0.0,
        public float $totalTimeMs = 0.0,
        public int $redirectCount = 0,
        public ?string $effectiveUrl = null,
        public array $context = [],
    ) {
    }

    /**
     * Create a response from raw data (for testing/fakes).
     *
     * @param array<string, string|string[]> $headers Headers as name => value or name => [values]
     */
    public static function fromRaw(
        int $statusCode,
        array $headers = [],
        string $body = '',
        string $reasonPhrase = '',
        float $transferTimeMs = 0.0,
    ): self {
        $normalizedHeaders = [];
        foreach ($headers as $name => $value) {
            $normalizedHeaders[$name] = is_array($value) ? $value : [$value];
        }

        return new self(
            statusCode    : $statusCode,
            headers       : $normalizedHeaders,
            body          : $body,
            reasonPhrase  : $reasonPhrase ?: self::defaultReasonPhrase($statusCode),
            transferTimeMs: $transferTimeMs,
        );
    }

    /**
     * Get a default reason phrase for a status code.
     */
    private static function defaultReasonPhrase(int $statusCode): string
    {
        return match ($statusCode) {
            200     => 'OK',
            201     => 'Created',
            204     => 'No Content',
            301     => 'Moved Permanently',
            302     => 'Found',
            304     => 'Not Modified',
            400     => 'Bad Request',
            401     => 'Unauthorized',
            403     => 'Forbidden',
            404     => 'Not Found',
            405     => 'Method Not Allowed',
            408     => 'Request Timeout',
            422     => 'Unprocessable Entity',
            429     => 'Too Many Requests',
            500     => 'Internal Server Error',
            502     => 'Bad Gateway',
            503     => 'Service Unavailable',
            504     => 'Gateway Timeout',
            default => "Status {$statusCode}",
        };
    }

    /**
     * Check if the response indicates success (2xx status code).
     */
    public function isSuccessful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    /**
     * Check if the response indicates a redirect (3xx status code).
     */
    public function isRedirect(): bool
    {
        return $this->statusCode >= 300 && $this->statusCode < 400;
    }

    /**
     * Check if the response indicates an error (4xx or 5xx).
     */
    public function hasError(): bool
    {
        return $this->isClientError() || $this->isServerError();
    }

    /**
     * Check if the response indicates a client error (4xx status code).
     */
    public function isClientError(): bool
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    /**
     * Check if the response indicates a server error (5xx status code).
     */
    public function isServerError(): bool
    {
        return $this->statusCode >= 500 && $this->statusCode < 600;
    }

    /**
     * Get the response content type.
     */
    public function getContentType(): ?string
    {
        $contentType = $this->getHeaderLine('Content-Type');
        if ($contentType === '') {
            return null;
        }

        // Strip parameters (e.g., charset=utf-8)
        return explode(';', $contentType, 2)[0];
    }

    /**
     * Get a header value as a comma-separated string.
     */
    public function getHeaderLine(string $name): string
    {
        $values = $this->headers[$name] ?? [];

        return implode(', ', $values);
    }

    /**
     * Get all values for a specific header.
     *
     * @return list<string>
     */
    public function getHeader(string $name): array
    {
        return $this->headers[$name] ?? [];
    }

    /**
     * Check if the response has a specific header.
     */
    public function hasHeader(string $name): bool
    {
        return isset($this->headers[$name]);
    }

    /**
     * Get the decoded JSON body.
     *
     * @param bool $assoc When true, return associative array; when false, return stdClass
     *
     * @throws JsonException if the body is not valid JSON
     */
    public function json(bool $assoc = true): mixed
    {
        return json_decode($this->body, $assoc, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Get the decoded body using the ResponseDecoder.
     *
     * @param string|null $format Force a specific format ('json', 'xml', 'text')
     */
    public function decoded(string $format = null): mixed
    {
        $decoder = new ResponseDecoder();

        return $decoder->decode($this, $format);
    }

    /**
     * Get the effective URL (final URL after redirects).
     */
    public function getEffectiveUrl(): string
    {
        return $this->effectiveUrl ?? '';
    }
}
