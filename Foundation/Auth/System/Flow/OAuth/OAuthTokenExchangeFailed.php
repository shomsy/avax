<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\OAuth;

use RuntimeException;

final class OAuthTokenExchangeFailed extends RuntimeException
{
    public static function invalidClient() : self
    {
        return new self('Invalid OAuth client credentials.');
    }

    public static function invalidGrant() : self
    {
        return new self('Invalid OAuth grant.');
    }

    public static function invalidRedirectUri() : self
    {
        return new self('Invalid redirect URI.');
    }

    public static function invalidVerifier() : self
    {
        return new self('Invalid PKCE verifier.');
    }

    public static function invalidSenderConstraint() : self
    {
        return new self('Invalid sender constraint proof.');
    }
}
