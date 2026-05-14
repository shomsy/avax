<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Flows\ResolveContainerService;

use Avax\Components\Application\Container\System\PublicSurface\Container;

final class ResolveContainerService
{
    public function resolve(Container $container, string $id): mixed
    {
        return $container->make($id);
    }
}
