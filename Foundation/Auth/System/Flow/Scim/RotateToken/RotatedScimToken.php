<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\RotateToken;

use Avax\Auth\System\Capability\Scim\ScimDirectory;
use SensitiveParameter;

final readonly class RotatedScimToken
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
