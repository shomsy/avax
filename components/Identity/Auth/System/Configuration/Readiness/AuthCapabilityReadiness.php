<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Configuration\Readiness;

use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationRuntimeInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyRuntimeInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Store\RefreshTokenStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\ProvisionableUserSourceInterface;
use SensitiveParameter;

final readonly class AuthCapabilityReadiness
{
    private function __construct(
        private bool $oauth,
        private bool $passkey,
        private bool $federation,
        private bool $scim,
    ) {
    }

    public static function from(
        #[SensitiveParameter]
        JwtIdentityInterface|null $jwtIdentity,
        #[SensitiveParameter]
        RefreshTokenStoreInterface|null $refreshTokenStore,
        PasskeyRuntimeInterface|null $passkeyRuntime,
        FederationRuntimeInterface|null $federationRuntime,
        ProvisionableUserSourceInterface|null $provisionableUserSource,
    ) : self
    {
        return new self(
            oauth     : $jwtIdentity             !== null && $refreshTokenStore !== null,
            passkey   : $passkeyRuntime          !== null,
            federation: $federationRuntime       !== null,
            scim      : $provisionableUserSource !== null,
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
