<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\Diagnostics\System\System\Configuration;

final class DiagnosticsConfig
{
    public static function isDebug() : bool
    {
        return $_ENV['APP_DEBUG'] ?? false;
    }
}
