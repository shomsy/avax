<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport;

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
