<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Context\System\PublicSurface;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Canonical, read-only HTTP context.
 */
interface HttpContextInterface
{
    public function request(): ?ServerRequestInterface;
    public function scheme(): string;
    public function host(): string;
    public function baseUrl(): string;
    public function isSecure(): bool;
    public function clientIp(): ?string;
    public function userAgent(): ?string;
    public function authHeader(): ?string;
    public function cookies(): array;
    public function serverParams(): array;
}
