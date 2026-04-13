<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\ReadGroups;

use Avax\Auth\System\Capability\Scim\ScimDirectoryStoreInterface;

/**
 * Reads SCIM groups from a directory.
 */
final readonly class ReadScimGroups
{
    public function __construct(
        private ScimDirectoryStoreInterface $directoryStore
    ) {}

    /**
     * @return list<ScimGroupProjection>
     */
    public function execute(string $directoryId) : array
    {
        $directory = $this->directoryStore->find(directoryId: $directoryId);

        if ($directory === null) {
            return [];
        }

        return [
            new ScimGroupProjection(
                id          : $directory->directoryId,
                displayName : $directory->name,
                members     : []
            ),
        ];
    }
}