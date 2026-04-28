<?php

declare(strict_types=1);

namespace Avax\Components\Container\System\Flows\CreateContainer;

use Avax\Components\Container\System\PublicSurface\Container;

final class CreateContainer
{
    public function create(): Container
    {
        return new Container();
    }
}