<?php

declare(strict_types=1);

namespace Avax\Presentation\System\PublicSurface;

final readonly class Presentation
{
    public static function render(): self
    {
        return new self();
    }
}
