<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Flows\RegisterBinding;

use Avax\Components\Application\Container\System\PublicSurface\Container;

final class RegisterBinding
{
    public function register(Container $container, string $id, callable $factory): void
    {
        $container->bind($id, $factory);
    }
}
