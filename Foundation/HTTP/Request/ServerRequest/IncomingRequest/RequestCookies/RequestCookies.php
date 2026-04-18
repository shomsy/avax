<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestCookies;

/**
 * Capability Owner: Manages HTTP request cookies.
 *
 * Separates cookie handling from generic parameter bags
 * to enforce clear ownership boundaries.
 */
final readonly class RequestCookies
{
    public function __construct(
        private array $cookies = []
    ) {}

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

    public function get(string $name, ?string $default = null) : ?string
    {
        return $this->cookies[$name] ?? $default;
    }

    public function with(array $cookies) : self
    {
        return new self(cookies: $cookies);
    }
}
