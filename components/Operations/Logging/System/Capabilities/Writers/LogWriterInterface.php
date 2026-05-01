<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Logging\System\Capabilities\Writers;

/**
 * Contract for writing log entries to a specific storage.
 */
interface LogWriterInterface
{
    /**
     * Writes log content to the storage.
     */
    public function write(string $content): void;
}
