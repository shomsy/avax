<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\PublicSurface;

final readonly class SystemDesignKit
{
    public static function design(): self
    {
        return new self();
    }
}
