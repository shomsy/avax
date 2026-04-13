<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\RotateToken;

use Avax\Auth\System\Capability\Scim\ScimDirectory;

final readonly class RotatedScimToken
{
    public function __construct(
        public ScimDirectory $directory,
        #[\SensitiveParameter] public string $plainTextToken
    ) {}
}
