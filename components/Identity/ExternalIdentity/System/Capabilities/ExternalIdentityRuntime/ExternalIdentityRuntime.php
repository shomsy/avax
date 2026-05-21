<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\ExternalIdentityRuntime;

use Avax\Components\Identity\ExternalIdentity\System\Capabilities\ExternalIdentityLink\ExternalIdentityLinkStoreInterface;

/**
 * ExternalIdentityRuntime owns external identity link behavior for one assembled runtime.
 */
final readonly class ExternalIdentityRuntime
{
    public function __construct(
        private ExternalIdentityLinkStoreInterface $linkStore,
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
}
