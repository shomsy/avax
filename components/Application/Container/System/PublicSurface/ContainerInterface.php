<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\PublicSurface;

use Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings\DependencyRegistration;
use Psr\Container\ContainerInterface as PsrContainerInterface;

interface ContainerInterface extends PsrContainerInterface
{
    public function make(string $abstract, array $parameters = []): mixed;

    public function get(string $id): mixed;

    public function has(string $id): bool;

    public function call(callable $callback, array $parameters = []): mixed;

    public function bind(string $abstract, mixed $concrete = null) : DependencyRegistration;

    public function singleton(string $abstract, mixed $concrete = null) : DependencyRegistration;

    public function scoped(string $abstract, mixed $concrete = null) : DependencyRegistration;

    public function instance(string $abstract, object $instance): void;

    public function debugGraph(string $id = '') : array;

    public function alias(string $alias, string $abstract): void;

    public function tag(string|array $abstracts, string|array $tags): void;

    public function tagged(string $tag): array;

    public function flush(): void;
}
