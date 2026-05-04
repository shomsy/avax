<?php

declare(strict_types=1);

namespace Avax\Operations\System\PublicSurface;

final readonly class Operations
{
    public static function orchestrate(): self
    {
        return new self();
    }
}
