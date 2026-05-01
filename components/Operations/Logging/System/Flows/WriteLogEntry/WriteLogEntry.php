<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Logging\System\Flows\WriteLogEntry;

use Avax\Components\Operations\Logging\System\PublicSurface\Logging;

final class WriteLogEntry
{
    public function write(Logging $logging, string $level, string $message) : void
    {
        $logging->log($level, $message);
    }
}
