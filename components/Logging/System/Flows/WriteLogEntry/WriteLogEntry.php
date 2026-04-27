<?php

declare(strict_types=1);

namespace Avax\Components\Logging\System\Flows\WriteLogEntry;

final class WriteLogEntry
{
    public function write(\Avax\Components\Logging\System\PublicSurface\Logging $log, string $level, string $message): void
    {
        $log->log($level, $message);
    }
}