<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestCookies;

/**
 * RequestCookies - Capability Owner: Manages HTTP request cookies.
 *
 * Semantic rules:
 * - key presence uses `array_key_exists()`.
 * - `has()` determines presence, regardless of nullability (though cookies are typically strings).
 */
final readonly class RequestCookies
{
    public function __construct(private array $cookies = []) {}

    /**
     * @return array<string, string>
     */
    public function all() : array
    {
        return $this->cookies;
    }

    public function has(string $name) : bool
    {
        return array_key_exists(key: $name, array: $this->cookies);
    }

    public function get(string $name, string|null $default = null) : string|null
    {
        return array_key_exists(key: $name, array: $this->cookies)
            ? $this->cookies[$name]
            : $default;
    }

    public function with(array $cookies) : self
    {
        return new self(cookies: $cookies);
    }
}
