<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Configuration\CompiledCacheConfiguration;

final readonly class ConfigureCompiledCache
{
    public static function inDirectory(string $directory) : CompiledCacheConfiguration
    {
        return CompiledCacheConfiguration::inDirectory(directory: $directory);
    }

    public static function disabled() : CompiledCacheConfiguration
    {
        return CompiledCacheConfiguration::disabled();
    }
}