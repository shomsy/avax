<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Scim;

use SensitiveParameter;

final readonly class RegisteredScimDirectory
{
    public function __construct(
        public ScimDirectory                $directory,
        #[SensitiveParameter] public string $plainTextToken
    ) {}
}
