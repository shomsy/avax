<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Context\System\PublicSurface;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Canonical, read-only HTTP context.
 *
 * Provides mockable access to all PHP superglobals:
 * $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES.
 */
interface HttpContextInterface
{
    /**
     * Get the underlying PSR-7 server request (if available).
     */
    public function request(): ?ServerRequestInterface;

    /**
     * Get the request scheme (http or https).
     */
    public function scheme(): string;

    /**
     * Get the request host.
     */
    public function host(): string;

    /**
     * Get the base URL (scheme + host + optional port).
     */
    public function baseUrl(): string;

    /**
     * Check if the request is using HTTPS.
     */
    public function isSecure(): bool;

    /**
     * Get the client IP address (supports forwarded headers).
     */
    public function clientIp(): ?string;

    /**
     * Get the User-Agent header value.
     */
    public function userAgent(): ?string;

    /**
     * Get the Authorization header value.
     */
    public function authHeader(): ?string;

    /**
     * Get mockable access to $_COOKIE.
     */
    public function cookies(): array;

    /**
     * Get mockable access to $_SERVER.
     */
    public function serverParams(): array;

    /**
     * Get mockable access to $_GET.
     */
    public function query() : array;

    /**
     * Get mockable access to $_POST.
     */
    public function post() : array;

    /**
     * Get mockable access to $_FILES.
     */
    public function files() : array;
}
