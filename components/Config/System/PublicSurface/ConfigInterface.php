<?php

declare(strict_types=1);

namespace Avax\Components\Config\System\PublicSurface;

interface ConfigInterface
{
    public function get(string $key, mixed $default = null): mixed;

    public function has(string $key): bool;
}