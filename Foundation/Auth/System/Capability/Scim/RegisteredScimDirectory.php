<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Scim;

final readonly class RegisteredScimDirectory
{
    public function __construct(
        public ScimDirectory $directory,
        public string $plainTextToken
    ) {}
}
