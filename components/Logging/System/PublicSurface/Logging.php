<?php

declare(strict_types=1);

namespace Avax\Components\Logging\System\PublicSurface;

final class Logging implements LoggingInterface
{
    public function log(string $level, string $message, array $context = []): void
    {
        error_log("[{$level}] {$message}");
    }
}