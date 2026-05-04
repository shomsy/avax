<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\System\PublicSurface;

final readonly class DeveloperTools
{
    public static function diagnose(): self
    {
        return new self();
    }
}
