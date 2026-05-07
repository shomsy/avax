<?php

declare(strict_types=1);

namespace Avax\Components\Identity\System\System\PublicSurface;

final readonly class Identity
{
    public static function authenticate() : self
    {
        return new self();
    }
}
