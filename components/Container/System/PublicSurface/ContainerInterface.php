<?php

declare(strict_types=1);

namespace Avax\Components\Container\System\PublicSurface;

interface ContainerInterface
{
    public function make(string $id): mixed;

    public function has(string $id): bool;

    public function call(callable $callback, array $args = []): mixed;
}