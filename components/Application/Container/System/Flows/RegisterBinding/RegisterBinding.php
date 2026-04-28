<?php

declare(strict_types=1);

namespace Avax\Components\Container\System\Flows\RegisterBinding;

use Avax\Components\Container\System\PublicSurface\Container;

final class RegisterBinding
{
    public function register(Container $container, string $id, callable $factory): void
    {
        $container->bind($id, $factory);
    }
}