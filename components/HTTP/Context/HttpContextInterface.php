<?php

declare(strict_types=1);

namespace Avax\HTTP\Context;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Canonical, read-only HTTP context.
 *
 * Provides normalized access to request facts across the framework.
 */
interface HttpContextInterface
{
    public function request() : ServerRequestInterface|null;

    public function scheme() : string;

    public function host() : string;

    public function baseUrl() : string;

    public function isSecure() : bool;

    public function clientIp() : string|null;

    public function userAgent() : string|null;

    public function authHeader() : string|null;

    /**
     * @return array<string, mixed>
     */
    public function cookies() : array;

    /**
     * @return array<string, mixed>
     */
    public function serverParams() : array;
}
