<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation;

interface FederationConnectionStoreInterface
{
    public function save(FederationConnection $connection) : void;

    public function find(string $connectionId) : FederationConnection|null;

    public function findByDomain(string $domain) : FederationConnection|null;

    /**
     * @return list<FederationConnection>
     */
    public function all() : array;
}
