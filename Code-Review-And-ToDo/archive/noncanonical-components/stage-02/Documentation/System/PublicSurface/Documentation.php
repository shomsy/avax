<?php

declare(strict_types=1);

namespace Avax\Components\Documentation\System\PublicSurface;

final readonly class Documentation
{
    public static function generate() : self
    {
        return new self();
    }
}
