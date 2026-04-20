<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Federation;

interface FederatedIdentityLinkStoreInterface
{
    public function save(FederatedIdentityLink $link) : void;

    public function find(string $connectionId, string $subject) : FederatedIdentityLink|null;
}
