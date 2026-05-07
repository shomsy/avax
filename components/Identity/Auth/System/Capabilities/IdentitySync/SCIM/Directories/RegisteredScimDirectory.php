<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories;

use SensitiveParameter;

final readonly class RegisteredScimDirectory
{
    public function __construct(
        public ScimDirectory $directory,
        #[SensitiveParameter]
        public string        $plainTextToken,
    ) {}
}
