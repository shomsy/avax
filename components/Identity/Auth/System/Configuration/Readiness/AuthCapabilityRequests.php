<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Configuration\Readiness;

final readonly class AuthCapabilityRequests
{
    private function __construct(
        private bool $enterpriseMode,
        private bool $oauth,
        private bool $oidcRequestObjects,
        private bool $passkey,
        private bool $federation,
        private bool $scim,
    ) {
    }

    public static function from(
        bool $enterpriseMode,
        bool $oauthClientRegistryConfigured,
        bool $authorizationCodeStoreConfigured,
        bool $oidcProviderConfigured,
        bool $oidcRequestObjectStoreConfigured,
        bool $passkeyCredentialStoreConfigured,
        bool $passkeyChallengeStoreConfigured,
        bool $passkeyRelyingPartyCustomized,
        bool $federationConnectionStoreConfigured,
        bool $federatedIdentityLinkStoreConfigured,
        bool $scimDirectoryStoreConfigured,
        bool $scimProvisionedIdentityStoreConfigured,
    ): self {
        return new self(
            enterpriseMode    : $enterpriseMode,
            oauth             : $oauthClientRegistryConfigured
                                || $authorizationCodeStoreConfigured
                                || $oidcProviderConfigured
                                || $oidcRequestObjectStoreConfigured,
            oidcRequestObjects: $oidcRequestObjectStoreConfigured,
            passkey           : $passkeyCredentialStoreConfigured
                                || $passkeyChallengeStoreConfigured
                                || $passkeyRelyingPartyCustomized,
            federation        : $federationConnectionStoreConfigured
                                || $federatedIdentityLinkStoreConfigured,
            scim              : $scimDirectoryStoreConfigured
                                || $scimProvisionedIdentityStoreConfigured,
        );
    }

    public function enterpriseMode(): bool
    {
        return $this->enterpriseMode;
    }

    public function oauth(): bool
    {
        return $this->oauth;
    }

    public function oidcRequestObjects(): bool
    {
        return $this->oidcRequestObjects;
    }

    public function passkey(): bool
    {
        return $this->passkey;
    }

    public function federation(): bool
    {
        return $this->federation;
    }

    public function scim(): bool
    {
        return $this->scim;
    }
}
