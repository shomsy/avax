<?php

declare(strict_types=1);

namespace Avax\DataLayer\System\PublicSurface;

final readonly class DataLayer
{
    public static function access(): self
    {
        return new self();
    }
}
