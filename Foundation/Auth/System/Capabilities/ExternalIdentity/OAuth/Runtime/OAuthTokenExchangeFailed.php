<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\OAuth;

use RuntimeException;

final class OAuthTokenExchangeFailed extends RuntimeException
{
    public static function invalidClient() : self
    {
        return new self(message: 'Invalid OAuth client credentials.');
    }

    public static function invalidGrant() : self
    {
        return new self(message: 'Invalid OAuth grant.');
    }

    public static function invalidRedirectUri() : self
    {
        return new self(message: 'Invalid redirect URI.');
    }

    public static function invalidVerifier() : self
    {
        return new self(message: 'Invalid PKCE verifier.');
    }

    public static function invalidSenderConstraint() : self
    {
        return new self(message: 'Invalid sender constraint proof.');
    }
}
