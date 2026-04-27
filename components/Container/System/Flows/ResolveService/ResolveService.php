<?php

declare(strict_types=1);

namespace Avax\Components\Container\System\Flows\ResolveService;

final class ResolveService
{
    public function resolve(\Avax\Components\Container\System\PublicSurface\Container $container, string $id): mixed
    {
        return $container->make($id);
    }
}