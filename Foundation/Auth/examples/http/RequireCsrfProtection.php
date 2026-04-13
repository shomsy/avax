<?php

declare(strict_types=1);

namespace Avax\Auth\Examples\Http;

/**
 * Framework-neutral reference middleware for cookie-auth CSRF protection.
 */
final readonly class RequireCsrfProtection
{
    public function __construct(
        private string $cookieName = 'csrf_token',
        private string $headerName = 'x-csrf-token'
    ) {}

    /**
     * @param array<string, mixed> $server
     * @param array<string, mixed> $headers
     * @param array<string, mixed> $cookies
     */
    public function execute(
        string $method,
        array $server = [],
        array $headers = [],
        array $cookies = []
    ) : bool
    {
        if ($this->isSafeMethod($method)) {
            return true;
        }

        $origin = $this->readValue($server, 'HTTP_ORIGIN');
        $referer = $this->readValue($server, 'HTTP_REFERER');
        $host = $this->readValue($server, 'HTTP_HOST');

        if (! $this->sameOrigin($origin, $host) && ! $this->sameOrigin($referer, $host)) {
            return false;
        }

        $cookieToken = $this->readValue($cookies, $this->cookieName);
        $headerToken = $this->readValue($headers, $this->headerName);

        if ($cookieToken === null || $headerToken === null) {
            return false;
        }

        return hash_equals($cookieToken, $headerToken);
    }

    private function isSafeMethod(string $method) : bool
    {
        return in_array(strtoupper($method), ['GET', 'HEAD', 'OPTIONS'], true);
    }

    /**
     * @param array<string, mixed> $values
     */
    private function readValue(array $values, string $key) : string|null
    {
        foreach ($values as $candidateKey => $value) {
            if (strcasecmp($candidateKey, $key) !== 0) {
                continue;
            }

            if (is_array($value)) {
                $value = reset($value);
            }

            return is_scalar($value) ? (string) $value : null;
        }

        return null;
    }

    private function sameOrigin(string|null $originLikeValue, string|null $host) : bool
    {
        if ($originLikeValue === null || $host === null) {
            return false;
        }

        $parts = parse_url($originLikeValue);

        if (! is_array($parts)) {
            return false;
        }

        return strcasecmp((string) ($parts['host'] ?? ''), $host) === 0;
    }
}
