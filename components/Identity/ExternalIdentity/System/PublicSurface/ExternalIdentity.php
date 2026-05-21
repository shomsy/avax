<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\PublicSurface;

use Avax\Components\Identity\ExternalIdentity\System\Capabilities\ExternalIdentityRuntime\ExternalIdentityRuntime;

/**
 * ExternalIdentity — manages external identity links (OAuth, SSO, etc.).
 */
final readonly class ExternalIdentity
{
    public function __construct(
        private ExternalIdentityRuntime $runtime,
    ) {}

    /**
     * @param array<string, mixed> $externalData
     */
    public function link(string $userId, string $provider, array $externalData) : void
    {
        $this->runtime->link(
            userId      : $userId,
            provider    : $provider,
            externalData: $externalData,
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function resolve(string $userId, string $provider) : array|null
    {
        return $this->runtime->resolve(
            userId  : $userId,
            provider: $provider,
        );
    }

    public function oauth() : OAuth
    {
        return $this->runtime->oauth();
    }

    public function oidc() : Oidc
    {
        return $this->runtime->oidc();
    }

    public function federation() : Federation
    {
        return $this->runtime->federation();
    }
}
