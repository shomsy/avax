<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\System\Capabilities\IdentitySync\SCIM\Runtime\RotateToken;

use Avax\Components\Identity\Auth\System\System\Capabilities\IdentitySync\SCIM\Directories\ScimDirectory;
use SensitiveParameter;

final readonly class RotatedScimToken
{
    public function __construct(
        public ScimDirectory $directory,
        #[SensitiveParameter]
        public string        $plainTextToken,
    ) {}
}
