<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support;

use SensitiveParameter;

final readonly class RegisteredScimDirectory
{
    public string        $plainTextToken;
    public ScimDirectory $directory;

    public function __construct(
        ScimDirectory                $directory,
        #[SensitiveParameter] string $plainTextToken
    )
    {
        $this->directory      = $directory;
        $this->plainTextToken = $plainTextToken;
    }
}
