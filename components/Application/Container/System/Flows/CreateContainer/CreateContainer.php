<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Flows\CreateContainer;

use Avax\Components\Application\Container\System\PublicSurface\Container;

final class CreateContainer
{
    public function create(): Container
    {
        return new Container();
    }
}
