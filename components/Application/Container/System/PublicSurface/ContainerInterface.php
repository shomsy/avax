<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\PublicSurface;

interface ContainerInterface
{
    public function make(string $abstract, array $parameters = []): mixed;

    public function get(string $id): mixed;

    public function has(string $id): bool;

    public function call(callable $callback, array $parameters = []): mixed;

    public function bind(string $abstract, mixed $concrete = null, bool $shared = false): void;

    public function singleton(string $abstract, mixed $concrete = null): void;

    public function scoped(string $abstract, mixed $concrete = null): void;

    public function instance(string $abstract, object $instance): void;

    public function alias(string $alias, string $abstract): void;

    public function tag(string|array $abstracts, string|array $tags): void;

    public function tagged(string $tag): array;

    public function flush(): void;

    public function debugGraph(): array;

    public function describeService(string $id): array;

    public function isLazy(string $id): bool;

    public function isDeferred(string $id): bool;
}
