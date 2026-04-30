<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Logging\System\Capabilities\Logger;

final class Logger
{
    public function log(string $level, string $message) : void
    {
        error_log("[{$level}] {$message}");
    }
}
