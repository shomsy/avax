<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\System\Foundation\Values;

use ValueError;
use function strtoupper;

enum HttpMethod: string
{
    case GET     = 'GET';
    case POST    = 'POST';
    case PUT     = 'PUT';
    case PATCH   = 'PATCH';
    case DELETE  = 'DELETE';
    case HEAD    = 'HEAD';
    case OPTIONS = 'OPTIONS';
    case CONNECT = 'CONNECT';
    case TRACE   = 'TRACE';

    /**
     * Try to create an HttpMethod from a string.
     * Returns null if the method is not recognized.
     */
    public static function tryFromName(string $name) : ?self
    {
        return self::tryFrom(strtoupper($name));
    }

    /**
     * Create an HttpMethod from a string.
     *
     * @throws ValueError if the method is not recognized
     */
    public static function fromName(string $name) : self
    {
        return self::from(strtoupper($name));
    }

    /**
     * Check if a string represents a valid HTTP method.
     */
    public static function isValid(string $name) : bool
    {
        return self::tryFrom(strtoupper($name)) !== null;
    }

    /**
     * Check if the HTTP method is considered "safe" (does not modify resources).
     *
     * Safe methods: GET, HEAD, OPTIONS, TRACE
     */
    public function isSafe() : bool
    {
        return match ($this) {
            self::GET,
            self::HEAD,
            self::OPTIONS,
            self::TRACE => true,
            default     => false,
        };
    }

    /**
     * Check if the HTTP method is considered "idempotent" (multiple identical
     * requests have the same effect as a single request).
     *
     * Idempotent methods: GET, HEAD, PUT, DELETE, OPTIONS, TRACE
     */
    public function isIdempotent() : bool
    {
        return match ($this) {
            self::GET,
            self::HEAD,
            self::PUT,
            self::DELETE,
            self::OPTIONS,
            self::TRACE => true,
            default     => false,
        };
    }

    /**
     * Check if the HTTP method allows a request body.
     *
     * Methods that conventionally support a body: POST, PUT, PATCH, DELETE
     */
    public function allowsBody() : bool
    {
        return match ($this) {
            self::POST,
            self::PUT,
            self::PATCH,
            self::DELETE => true,
            default      => false,
        };
    }
}
