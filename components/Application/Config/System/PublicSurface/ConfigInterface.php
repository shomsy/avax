<?php

declare(strict_types=1);

namespace Avax\Components\Application\Config\System\PublicSurface;

interface ConfigInterface
{
    public function get(string $key, mixed $default = null): mixed;

    public function has(string $key): bool;

    public function set(string $key, mixed $value): void;

    public function load(string $path, ?string $namespace = null): void;

    public function all(): array;
}
