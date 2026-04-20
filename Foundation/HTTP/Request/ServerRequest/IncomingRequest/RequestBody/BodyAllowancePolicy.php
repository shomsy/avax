<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody;

/**
 * BodyAllowancePolicy
 *
 * Decisions about whether a request body should be read or parsed.
 *
 * Rules:
 * - reading raw body: POST, PUT, PATCH, DELETE (default)
 * - can be overridden by presence of Content-Length or Content-Type
 */
final readonly class BodyAllowancePolicy
{
    /**
     * @param list<string> $allowedMethods
     */
    public function __construct(
        private array $allowedMethods = ['POST', 'PUT', 'PATCH', 'DELETE']
    ) {}

    public function allowsRead(string $method, array $headers): bool
    {
        // Always allowed if method is in the list
        if (in_array(strtoupper($method), $this->allowedMethods, true)) {
            return true;
        }

        // Allow if Content-Length is present and > 0
        if (isset($headers['Content-Length']) && (int) $headers['Content-Length'] > 0) {
            return true;
        }

        // Allow if Transfer-Encoding is present (chunked)
        if (isset($headers['Transfer-Encoding'])) {
            return true;
        }

        return false;
    }
}
