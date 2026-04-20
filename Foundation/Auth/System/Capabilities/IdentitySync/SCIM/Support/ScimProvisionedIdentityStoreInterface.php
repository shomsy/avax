<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Scim;

interface ScimProvisionedIdentityStoreInterface
{
    public function save(ScimProvisionedIdentity $identity) : void;

    public function find(string $directoryId, string $externalId) : ScimProvisionedIdentity|null;

    /**
     * @return list<ScimProvisionedIdentity>
     */
    public function allForDirectory(string $directoryId) : array;

    public function remove(string $directoryId, string $externalId) : void;
}
