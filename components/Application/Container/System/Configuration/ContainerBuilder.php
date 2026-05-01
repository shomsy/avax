<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Configuration;

use Avax\Components\Application\Container\System\PublicSurface\Container;

final class ContainerBuilder
{
    private array $bindings = [];

    public function bind(string $id, callable $factory): self
    {
        $this->bindings[$id] = $factory;

        return $this;
    }

    public function build(): Container
    {
        $container = new Container();

        foreach ($this->bindings as $id => $factory) {
            $container->bind($id, $factory);
        }

        return $container;
    }
}
