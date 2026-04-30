<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support;

use InvalidArgumentException;

/**
 * Normalizes token endpoint authentication method policy for OAuth clients.
 */
final readonly class OAuthTokenEndpointAuthMethodPolicy
{
    public function resolve(
        OAuthClientType              $type,
        OAuthTokenEndpointAuthMethod $requested = null,
        OAuthTokenEndpointAuthMethod $current = null,
        bool                         $workloadIdentity = false,
    ) : OAuthTokenEndpointAuthMethod
    {
        $default = $this->defaultForType(type: $type);
        $method  = $requested ?? $current ?? $default;

        if ($requested === null && $current !== null && ! $this->isCompatible(type: $type, method: $current, workloadIdentity: $workloadIdentity)) {
            $method = $default;
        }

        if (! $this->isCompatible(type: $type, method: $method, workloadIdentity: $workloadIdentity)) {
            throw new InvalidArgumentException(message: match ($type) {
                OAuthClientType::PUBLIC       => 'Public clients must use token endpoint auth method "none".',
                OAuthClientType::CONFIDENTIAL => 'Confidential clients require a token endpoint auth method.',
            });
        }

        return $method;
    }

    private function defaultForType(OAuthClientType $type) : OAuthTokenEndpointAuthMethod
    {
        return $type === OAuthClientType::PUBLIC
            ? OAuthTokenEndpointAuthMethod::NONE
            : OAuthTokenEndpointAuthMethod::CLIENT_SECRET_BASIC;
    }

    private function isCompatible(
        OAuthClientType $type,
        OAuthTokenEndpointAuthMethod $method,
        bool            $workloadIdentity,
    ) : bool
    {
        if ($type === OAuthClientType::PUBLIC) {
            return $method === OAuthTokenEndpointAuthMethod::NONE;
        }

        return $method !== OAuthTokenEndpointAuthMethod::NONE;
    }
}
