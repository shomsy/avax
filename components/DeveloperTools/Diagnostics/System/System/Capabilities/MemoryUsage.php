<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\Diagnostics\System\System\Capabilities;

final class MemoryUsage
{
    public static function execute() : int
    {
        return memory_get_usage(true);
    }
}
