<?php

declare(strict_types=1);

namespace Avax\Components\Container\System\Flows\ResolveService;

use Avax\Components\Container\System\PublicSurface\Container;

final class ResolveService
{
    public function resolve(Container $container, string $id): mixed
    {
        return $container->make($id);
    }
}