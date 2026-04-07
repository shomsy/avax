<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Foundation\Ids;

final class IdGenerator
{
    public function next(string $prefix = '') : string
    {
        return $prefix . bin2hex(random_bytes(8));
    }
}
