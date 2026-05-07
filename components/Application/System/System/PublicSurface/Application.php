<?php

declare(strict_types=1);

namespace Avax\Components\Application\System\System\PublicSurface;

final readonly class Application
{
    public static function bootstrap() : self
    {
        return new self();
    }
}
