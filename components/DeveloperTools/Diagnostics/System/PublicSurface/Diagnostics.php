<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\Diagnostics\System\PublicSurface;

final class Diagnostics
{
    public static function health() : array
    {
        return [
            'php'         => PHP_VERSION,
            'memory'      => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
        ];
    }

    public static function dump(mixed $data) : void
    {
        var_dump($data);
    }

    public static function dd(mixed ...$data) : void
    {
        foreach ($data as $item) {
            var_dump($item);
        }

        exit(1);
    }
}
