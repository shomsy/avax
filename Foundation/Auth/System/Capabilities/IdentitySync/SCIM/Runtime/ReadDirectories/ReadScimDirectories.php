<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Scim\ReadDirectories;

use Avax\Auth\System\Capabilities\Scim\ScimDirectory;
use Avax\Auth\System\Capabilities\Scim\ScimDirectoryStoreInterface;

final readonly class ReadScimDirectories
{
    private ScimDirectoryStoreInterface $directoryStore;

    public function __construct(
        ScimDirectoryStoreInterface $directoryStore
    )
    {
        $this->directoryStore = $directoryStore;
    }

    /**
     * @return list<ScimDirectory>
     */
    public function execute(string|null $tenantSlug = null) : array
    {
        $directories = $this->directoryStore->all();

        if ($tenantSlug === null) {
            return $directories;
        }

        return array_values(array_filter(
                                $directories,
                                static fn (ScimDirectory $directory) : bool => $directory->tenantSlug === trim($tenantSlug)
                            ));
    }
}
