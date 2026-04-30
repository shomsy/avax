<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Support;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\PasswordHashing\PasswordHasher;
use SensitiveParameter;

final class InMemoryScimDirectoryStore implements ScimDirectoryStoreInterface
{
    /** @var array<string, ScimDirectory> */
    private array $directories = [];

    public function __construct(
        #[SensitiveParameter]
        private readonly PasswordHasher $passwordHasher,
    ) {}

    public function save(ScimDirectory $directory) : void
    {
        $this->directories[$directory->directoryId] = $directory;
    }

    public function all() : array
    {
        return array_values(array: $this->directories);
    }

    public function verifyToken(
        string $directoryId,
        #[SensitiveParameter]
        string $plainTextToken,
    ) : bool
    {
        $directory = $this->find(directoryId: $directoryId);

        if ($directory === null) {
            return false;
        }

        return $this->passwordHasher->verify(password: $plainTextToken, hash: $directory->tokenHash);
    }

    public function find(string $directoryId) : ScimDirectory|null
    {
        return $this->directories[$directoryId] ?? null;
    }
}
