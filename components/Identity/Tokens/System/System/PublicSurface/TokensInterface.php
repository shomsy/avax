<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\System\PublicSurface;

/**
 * TokensInterface - Enterprise-grade token management contract (OAuth2/OIDC).
 */
interface TokensInterface
{
    public function authorize(array $request) : object;

    public function exchangeCode(string $code) : object;

    public function introspect(string $token) : object;

    public function revoke(string $token) : void;
}
