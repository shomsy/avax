<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories;

use Avax\Components\Security\Hashing\System\Capabilities\PasswordHashing\PasswordHasher;
use SensitiveParameter;

final class InMemoryScimDirectoryStore implements ScimDirectoryStoreInterface
{
    /** @var array<string, ScimDirectory> */
    private array $directories = [];

    public function __construct(
        #[SensitiveParameter]
        private readonly PasswordHasher $passwordHasher,
    ) {}

    public function save(ScimDirectory $scimDirectory) : void
    {
        $this->directories[$scimDirectory->directoryId] = $scimDirectory;
    }

    public function all(): array
    {
        return array_values(array: $this->directories);
    }

    public function verifyToken(
        string $directoryId,
        #[SensitiveParameter]
        string $plainTextToken,
    ): bool {
        $directory = $this->find(directoryId: $directoryId);

        if (! $directory instanceof ScimDirectory) {
            return false;
        }

        return $this->passwordHasher->verify(password: $plainTextToken, hash: $directory->tokenHash);
    }

    public function find(string $directoryId): ?ScimDirectory
    {
        return $this->directories[$directoryId] ?? null;
    }
}
