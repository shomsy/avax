<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\PublicSurface;

use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Record\IssuedToken;
use Avax\Components\Identity\Tokens\System\Flows\IssueToken\TokenSubject;

/**
 * TokensInterface - Enterprise-grade token management contract (OAuth2/OIDC).
 */
interface TokensInterface
{
    public function authorize(array $request) : object;

    public function exchangeCode(string $code) : object;

    public function introspect(string $token) : object;

    public function revoke(string $token) : void;

    public function issue(TokenSubject $subject) : IssuedToken;
}
