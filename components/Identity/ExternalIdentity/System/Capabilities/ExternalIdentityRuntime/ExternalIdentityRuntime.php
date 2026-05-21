<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\ExternalIdentityRuntime;

use Avax\Components\Identity\ExternalIdentity\System\Capabilities\ExternalIdentityLink\ExternalIdentityLinkStoreInterface;
use Avax\Components\Identity\ExternalIdentity\System\PublicSurface\Federation;
use Avax\Components\Identity\ExternalIdentity\System\PublicSurface\OAuth;
use Avax\Components\Identity\ExternalIdentity\System\PublicSurface\Oidc;

/**
 * ExternalIdentityRuntime owns external identity link behavior for one assembled runtime.
 */
final readonly class ExternalIdentityRuntime
{
    public function __construct(
        private ExternalIdentityLinkStoreInterface $linkStore,
        private OAuth                              $oauth,
        private Oidc                               $oidc,
        private Federation                         $federation,
    ) {}

    /**
     * @param array<string, mixed> $externalData
     */
    public function link(string $userId, string $provider, array $externalData) : void
    {
        $this->linkStore->link(
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
        return $this->linkStore->resolve(
            userId  : $userId,
            provider: $provider,
        );
    }

    public function oauth() : OAuth
    {
        return $this->oauth;
    }

    public function oidc() : Oidc
    {
        return $this->oidc;
    }

    public function federation() : Federation
    {
        return $this->federation;
    }
}
