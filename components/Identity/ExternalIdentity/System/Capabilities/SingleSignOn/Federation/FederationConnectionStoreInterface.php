<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation;

interface FederationConnectionStoreInterface
{
    public function save(FederationConnection $federationConnection) : void;

    public function find(string $connectionId) : ?FederationConnection;

    public function findByDomain(string $domain) : ?FederationConnection;

    /**
     * @return list<FederationConnection>
     */
    public function all() : array;
}
