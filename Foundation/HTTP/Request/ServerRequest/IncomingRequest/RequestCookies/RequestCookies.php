<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\IncomingHttp\IncomingRequest\RequestCookies;

/**
 * Capability Owner: Manages HTTP request cookies.
 *
 * Separates cookie handling from generic parameter bags
 * to enforce clear ownership boundaries.
 */
final readonly class RequestCookies
{
    private array $cookies;

    /**
     * @param array<string, string> $cookies
     */
    public function __construct(
        array $cookies = []
    )
    {
        $this->cookies = $cookies;
    }

    /**
     * @return array<string, string>
     */
    public function all() : array
    {
        return $this->cookies;
    }

    public function has(string $name) : bool
    {
        return isset($this->cookies[$name]);
    }

    public function get(string $name, string|null $default = null) : string|null
    {
        return $this->cookies[$name] ?? $default;
    }

    /**
     * @param array<string, string> $cookies
     */
    public function with(array $cookies) : self
    {
        return new self(cookies: $cookies);
    }
}
