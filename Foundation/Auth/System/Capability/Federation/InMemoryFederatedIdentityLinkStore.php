<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Federation;

final class InMemoryFederatedIdentityLinkStore implements FederatedIdentityLinkStoreInterface
{
    /** @var array<string, FederatedIdentityLink> */
    private array $links = [];

    public function save(FederatedIdentityLink $link) : void
    {
        $this->links[$this->key(connectionId: $link->connectionId, subject: $link->subject)] = $link;
    }

    public function find(string $connectionId, string $subject) : FederatedIdentityLink|null
    {
        return $this->links[$this->key(connectionId: $connectionId, subject: $subject)] ?? null;
    }

    private function key(string $connectionId, string $subject) : string
    {
        return $connectionId . '|' . $subject;
    }
}
