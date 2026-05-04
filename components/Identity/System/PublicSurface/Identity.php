<?php

declare(strict_types=1);

namespace Avax\Components\Identity\System\PublicSurface;

final readonly class Identity
{
    public static function authenticate(): self
    {
        return new self();
    }
}
