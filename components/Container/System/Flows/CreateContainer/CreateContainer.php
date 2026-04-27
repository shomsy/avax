<?php

declare(strict_types=1);

namespace Avax\Components\Container\System\Flows\CreateContainer;

final class CreateContainer
{
    public function create(): \Avax\Components\Container\System\PublicSurface\Container
    {
        return new \Avax\Components\Container\System\PublicSurface\Container();
    }
}