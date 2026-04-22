<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RotateToken;

use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectory;
use SensitiveParameter;

final readonly class RotatedScimToken
{
    public function __construct(
        public ScimDirectory                $directory,
        #[SensitiveParameter] public string $plainTextToken
    )
    {
    }
}
