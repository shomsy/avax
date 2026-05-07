<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation;

final class InMemoryFederatedIdentityLinkStore implements FederatedIdentityLinkStoreInterface
{
    /** @var array<string, FederatedIdentityLink> */
    private array $links = [];

    public function save(FederatedIdentityLink $federatedIdentityLink) : void
    {
        $this->links[$this->key(connectionId: $federatedIdentityLink->connectionId, subject: $federatedIdentityLink->subject)] = $federatedIdentityLink;
    }

    private function key(string $connectionId, string $subject) : string
    {
        return $connectionId . '|' . $subject;
    }

    public function find(string $connectionId, string $subject) : ?FederatedIdentityLink
    {
        return $this->links[$this->key(connectionId: $connectionId, subject: $subject)] ?? null;
    }
}
