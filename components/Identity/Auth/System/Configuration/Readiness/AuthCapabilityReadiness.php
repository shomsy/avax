<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Configuration\Readiness;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\ProvisionableUserSourceInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyRuntimeInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationRuntimeInterface;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store\RefreshTokenStoreInterface;
use SensitiveParameter;

final readonly class AuthCapabilityReadiness
{
    private function __construct(
        private bool $oauth,
        private bool $passkey,
        private bool $federation,
        private bool $scim,
    ) {}

    public static function from(
        #[SensitiveParameter]
        ?JwtIdentityInterface             $jwtIdentity,
        #[SensitiveParameter]
        ?RefreshTokenStoreInterface $refreshTokenStore, PasskeyRuntimeInterface|null $passkeyRuntime, FederationRuntimeInterface|null $federationRuntime, ProvisionableUserSourceInterface|null $provisionableUserSource,
    ) : self
    {
        return new self(
            oauth     : $jwtIdentity instanceof JwtIdentityInterface && $refreshTokenStore instanceof RefreshTokenStoreInterface,
            passkey   : $passkeyRuntime instanceof PasskeyRuntimeInterface,
            federation: $federationRuntime instanceof FederationRuntimeInterface,
            scim      : $provisionableUserSource instanceof ProvisionableUserSourceInterface,
        );
    }

    public function oauth() : bool
    {
        return $this->oauth;
    }

    public function passkey() : bool
    {
        return $this->passkey;
    }

    public function federation() : bool
    {
        return $this->federation;
    }

    public function scim() : bool
    {
        return $this->scim;
    }
}
