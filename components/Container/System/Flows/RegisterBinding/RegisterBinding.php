<?php

declare(strict_types=1);

namespace Avax\Components\Container\System\Flows\RegisterBinding;

final class RegisterBinding
{
    public function register(\Avax\Components\Container\System\PublicSurface\Container $container, string $id, callable $factory): void
    {
        $container->bind($id, $factory);
    }
}