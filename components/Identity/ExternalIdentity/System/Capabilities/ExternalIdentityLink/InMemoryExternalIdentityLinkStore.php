<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\ExternalIdentityLink;

/**
 * In-memory external identity link store — suitable for testing and simple setups.
 * NOT suitable for long-lived workers without per-request reset.
 */
final class InMemoryExternalIdentityLinkStore implements ExternalIdentityLinkStoreInterface
{
    /**
     * @var array<string, array<string, array<string, mixed>>>
     */
    private array $links = [];

    public function link(string $userId, string $provider, array $externalData) : void
    {
        $this->links[$userId][$provider] = $externalData;
    }

    public function resolve(string $userId, string $provider) : array|null
    {
        return $this->links[$userId][$provider] ?? null;
    }
}
