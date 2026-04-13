<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\OAuth;

use RuntimeException;

final class OAuthAuthorizationFailed extends RuntimeException
{
    public static function unauthenticated() : self
    {
        return new self('An authenticated user is required.');
    }

    public static function invalidClient() : self
    {
        return new self('Invalid OAuth client.');
    }

    public static function invalidRedirectUri() : self
    {
        return new self('Invalid redirect URI.');
    }

    public static function invalidScopes() : self
    {
        return new self('Invalid scopes.');
    }

    public static function invalidPkce() : self
    {
        return new self('PKCE is required for this client.');
    }

    public static function phishingResistantRequired() : self
    {
        return new self('Phishing-resistant authentication is required for this client.');
    }

    public static function openIdProviderNotConfigured() : self
    {
        return new self('OIDC provider support is not configured.');
    }

    public static function nonceRequired() : self
    {
        return new self('OIDC nonce is required for this authorization request.');
    }
}
