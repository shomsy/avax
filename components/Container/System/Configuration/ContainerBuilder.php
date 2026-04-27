<?php

declare(strict_types=1);

namespace Avax\Components\Container\System\Configuration;

final class ContainerBuilder
{
    private array $bindings = [];

    public function bind(string $id, callable $factory): self
    {
        $this->bindings[$id] = $factory;

        return $this;
    }

    public function build(): \Avax\Components\Container\System\PublicSurface\Container
    {
        $container = new \Avax\Components\Container\System\PublicSurface\Container();

        foreach ($this->bindings as $id => $factory) {
            $container->bind($id, $factory);
        }

        return $container;
    }
}