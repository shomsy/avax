<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadDirectories;

use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectory;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectoryStoreInterface;

final readonly class ReadScimDirectories
{
    public function __construct(private ScimDirectoryStoreInterface $scimDirectoryStore) {}

    /**
     * @return list<ScimDirectory>
     */
    public function execute(?string $tenantSlug = null) : array
    {
        $directories = $this->scimDirectoryStore->all();

        if ($tenantSlug === null) {
            return $directories;
        }

        return array_values(array: array_filter(
            array   : $directories,
            callback: static fn (ScimDirectory $scimDirectory) : bool => $scimDirectory->tenantSlug === trim(string: $tenantSlug),
        ));
    }
}
