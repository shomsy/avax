<?php

declare(strict_types=1);

namespace Avax\Components\Auth\Examples\Http;

use SensitiveParameter;

/**
 * Framework-neutral reference middleware for cookie-auth CSRF protection.
 */
final readonly class RequireCsrfProtection
{
    private string $cookieName;

    public function __construct(
        string|null                          $cookieName = null,
        #[SensitiveParameter] private string $headerName = 'x-csrf-token'
    )
    {
        $cookieName       ??= 'csrf_token';
        $this->cookieName = $cookieName;
    }

    /**
     * @param array<string, mixed> $server
     * @param array<string, mixed> $headers
     * @param array<string, mixed> $cookies
     */
    public function execute(
        string                           $method,
        array|null                       $server = null,
        #[SensitiveParameter] array|null $headers = null,
        array                            $cookies = []
    ) : bool
    {
        $server  ??= [];
        $headers ??= [];
        if ($this->isSafeMethod(method: $method)) {
            return true;
        }

        $origin  = $this->readValue(values: $server, key: 'HTTP_ORIGIN');
        $referer = $this->readValue(values: $server, key: 'HTTP_REFERER');
        $host    = $this->readValue(values: $server, key: 'HTTP_HOST');

        if (! $this->sameOrigin(originLikeValue: $origin, host: $host) && ! $this->sameOrigin(originLikeValue: $referer, host: $host)) {
            return false;
        }

        $cookieToken = $this->readValue(values: $cookies, key: $this->cookieName);
        $headerToken = $this->readValue(values: $headers, key: $this->headerName);

        if ($cookieToken === null || $headerToken === null) {
            return false;
        }

        return hash_equals(known_string: $cookieToken, user_string: $headerToken);
    }

    private function isSafeMethod(string $method) : bool
    {
        return in_array(needle: strtoupper(string: $method), haystack: ['GET', 'HEAD', 'OPTIONS'], strict: true);
    }

    /**
     * @param array<string, mixed> $values
     */
    private function readValue(array $values, string $key) : string|null
    {
        foreach ($values as $candidateKey => $value) {
            if (strcasecmp(string1: $candidateKey, string2: $key) !== 0) {
                continue;
            }

            if (is_array(value: $value)) {
                $value = reset(array: $value);
            }

            return is_scalar(value: $value) ? (string) $value : null;
        }

        return null;
    }

    private function sameOrigin(string|null $originLikeValue, string|null $host) : bool
    {
        if ($originLikeValue === null || $host === null) {
            return false;
        }

        $parts = parse_url(url: $originLikeValue);

        if (! is_array(value: $parts)) {
            return false;
        }

        return strcasecmp(string1: (string) ($parts['host'] ?? ''), string2: $host) === 0;
    }
}
