<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Support;

use SensitiveParameter;

interface ScimDirectoryStoreInterface
{
    public function save(ScimDirectory $directory) : void;

    public function find(string $directoryId) : ScimDirectory|null;

    /**
     * @return list<ScimDirectory>
     */
    public function all() : array;

    public function verifyToken(
        string $directoryId,
        #[SensitiveParameter]
        string $plainTextToken,
    ) : bool;
}
