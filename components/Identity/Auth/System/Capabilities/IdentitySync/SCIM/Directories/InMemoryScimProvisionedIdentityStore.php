<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Support;

final class InMemoryScimProvisionedIdentityStore implements ScimProvisionedIdentityStoreInterface
{
    /** @var array<string, ScimProvisionedIdentity> */
    private array $identities = [];

    public function save(ScimProvisionedIdentity $identity): void
    {
        $this->identities[$this->key(directoryId: $identity->directoryId, externalId: $identity->externalId)] = $identity;
    }

    private function key(string $directoryId, string $externalId): string
    {
        return $directoryId . ':' . strtolower(string: trim(string: $externalId));
    }

    public function find(string $directoryId, string $externalId): ?ScimProvisionedIdentity
    {
        return $this->identities[$this->key(directoryId: $directoryId, externalId: $externalId)] ?? null;
    }

    public function allForDirectory(string $directoryId): array
    {
        return array_values(array: array_filter(
            array   : $this->identities,
            callback: static fn (ScimProvisionedIdentity $identity): bool => $identity->directoryId === $directoryId,
        ));
    }

    public function remove(string $directoryId, string $externalId): void
    {
        unset($this->identities[$this->key(directoryId: $directoryId, externalId: $externalId)]);
    }
}
