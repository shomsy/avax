<?php
declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Flows\ExchangeToken;

/**
 * ExchangeAuthorizationCode - OAuth2 flow to exchange code for tokens.
 */
final readonly class ExchangeAuthorizationCode
{
    public function execute(string $code) : object
    {
        // Logic to verify code and issue tokens. Sourced from avax.txt OAuth2 implementation.
        return (object)[
            'access_token' => '...',
            'refresh_token' => '...',
            'expires_in' => 3600
        ];
    }
}
