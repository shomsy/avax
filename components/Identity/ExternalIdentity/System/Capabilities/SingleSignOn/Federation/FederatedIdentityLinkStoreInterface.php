<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationSupport;

interface FederatedIdentityLinkStoreInterface
{
    public function save(FederatedIdentityLink $link) : void;

    public function find(string $connectionId, string $subject) : FederatedIdentityLink|null;
}
