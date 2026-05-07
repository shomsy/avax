<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\System\Capabilities\IdentitySync\SCIM\Directories;

interface ScimProvisionedIdentityStoreInterface
{
    public function save(ScimProvisionedIdentity $scimProvisionedIdentity) : void;

    public function find(string $directoryId, string $externalId) : ?ScimProvisionedIdentity;

    /**
     * @return list<ScimProvisionedIdentity>
     */
    public function allForDirectory(string $directoryId) : array;

    public function remove(string $directoryId, string $externalId) : void;
}
