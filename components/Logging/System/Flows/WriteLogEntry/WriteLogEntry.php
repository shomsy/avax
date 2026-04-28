<?php

declare(strict_types=1);

namespace Avax\Components\Logging\System\Flows\WriteLogEntry;

use Avax\Components\Logging\System\PublicSurface\Logging;

final class WriteLogEntry
{
    public function write(Logging $log, string $level, string $message): void
    {
        $log->log($level, $message);
    }
}