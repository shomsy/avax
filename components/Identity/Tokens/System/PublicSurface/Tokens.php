<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\PublicSurface;

use Avax\Components\Identity\Tokens\System\Flows\AuthorizeToken\AuthorizeTokenRequest;
use Avax\Components\Identity\Tokens\System\Flows\ExchangeToken\ExchangeAuthorizationCode;
use Avax\Components\Identity\Tokens\System\Flows\IntrospectToken\IntrospectToken;
use Avax\Components\Identity\Tokens\System\Flows\RevokeToken\RevokeToken;
use stdClass;

/**
 * Tokens - Main entry point for Identity/Tokens component.
 */
final readonly class Tokens implements TokensInterface
{
    public function __construct(
        private AuthorizeTokenRequest     $authorizeTokenRequest,
        private ExchangeAuthorizationCode $exchangeAuthorizationCode,
        private IntrospectToken           $introspectToken,
        private RevokeToken               $revokeToken,
    ) {}

    public function authorize(array $request) : stdClass
    {
        return $this->authorizeTokenRequest->execute(request: $request);
    }

    public function exchangeCode(string $code) : stdClass
    {
        return $this->exchangeAuthorizationCode->execute($code);
    }

    public function introspect(string $token) : stdClass
    {
        return $this->introspectToken->execute(token: $token);
    }

    public function revoke(string $token) : void
    {
        $this->revokeToken->execute(token: $token);
    }

    public function issue(string $sub) : void
    {
    }
}
