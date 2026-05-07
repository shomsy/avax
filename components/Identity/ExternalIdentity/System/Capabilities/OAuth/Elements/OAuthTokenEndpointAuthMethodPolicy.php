<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements;

use InvalidArgumentException;

/**
 * Normalizes token endpoint authentication method policy for OAuth clients.
 */
final readonly class OAuthTokenEndpointAuthMethodPolicy
{
    public function resolve(
        OAuthClientType               $oAuthClientType,
        ?OAuthTokenEndpointAuthMethod $requested = null,
        ?OAuthTokenEndpointAuthMethod $current = null,
        bool                          $workloadIdentity = false,
    ) : OAuthTokenEndpointAuthMethod
    {
        $oAuthTokenEndpointAuthMethod = $this->defaultForType(type: $oAuthClientType);
        $method                       = $requested ?? $current ?? $oAuthTokenEndpointAuthMethod;

        if (! $requested instanceof OAuthTokenEndpointAuthMethod && $current instanceof OAuthTokenEndpointAuthMethod && ! $this->isCompatible(workloadIdentity: $workloadIdentity, type: $oAuthClientType, method: $current)) {
            $method = $oAuthTokenEndpointAuthMethod;
        }

        if (! $this->isCompatible(workloadIdentity: $workloadIdentity, type: $oAuthClientType, method: $method)) {
            throw new InvalidArgumentException(message: match ($oAuthClientType) {
                OAuthClientType::PUBLIC       => 'Public clients must use token endpoint auth method "none".',
                OAuthClientType::CONFIDENTIAL => 'Confidential clients require a token endpoint auth method.',
            });
        }

        return $method;
    }

    private function defaultForType(OAuthClientType $oAuthClientType) : OAuthTokenEndpointAuthMethod
    {
        return $oAuthClientType === OAuthClientType::PUBLIC
            ? OAuthTokenEndpointAuthMethod::NONE
            : OAuthTokenEndpointAuthMethod::CLIENT_SECRET_BASIC;
    }

    private function isCompatible(
        OAuthClientType              $oAuthClientType,
        OAuthTokenEndpointAuthMethod $oAuthTokenEndpointAuthMethod,
        bool                         $workloadIdentity,
    ) : bool
    {
        if ($oAuthClientType === OAuthClientType::PUBLIC) {
            return $oAuthTokenEndpointAuthMethod === OAuthTokenEndpointAuthMethod::NONE;
        }

        return $oAuthTokenEndpointAuthMethod !== OAuthTokenEndpointAuthMethod::NONE;
    }
}
