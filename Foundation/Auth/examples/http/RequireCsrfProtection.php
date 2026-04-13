<?php

declare(strict_types=1);

namespace Avax\Auth\Examples\Http;

use SensitiveParameter;

/**
 * Framework-neutral reference middleware for cookie-auth CSRF protection.
 */
final readonly class RequireCsrfProtection
{
    public function __construct(
        private string                       $cookieName = 'csrf_token',
        #[SensitiveParameter] private string $headerName = 'x-csrf-token'
    ) {}

    /**
     * @param array<string, mixed> $server
     * @param array<string, mixed> $headers
     * @param array<string, mixed> $cookies
     */
    public function execute(
        string                      $method,
        array                       $server = [],
        #[SensitiveParameter] array $headers = [],
        array                       $cookies = []
    ) : bool
    {
        if ($this->isSafeMethod(method: $method)) {
            return true;
        }

        $origin = $this->readValue(values: $server, key: 'HTTP_ORIGIN');
        $referer = $this->readValue(values: $server, key: 'HTTP_REFERER');
        $host = $this->readValue(values: $server, key: 'HTTP_HOST');

        if (! $this->sameOrigin(originLikeValue: $origin, host: $host) && ! $this->sameOrigin(originLikeValue: $referer, host: $host)) {
            return false;
        }

        $cookieToken = $this->readValue(values: $cookies, key: $this->cookieName);
        $headerToken = $this->readValue(values: $headers, key: $this->headerName);

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
