<?php

declare(strict_types=1);

namespace Avax\Logging;

/**
 * Interface LogWriterInterface
 *
 * Provides a contract for writing log entries. Implementations can be file-based, database-based, or any other
 * storage mechanism. This abstraction allows for flexible logging strategies in the application.
 */
interface LogWriterInterface
{
    /**
     * Writes a log entry to the defined storage mechanism.
     *
     * @param  string  $content  The log entry content to be written.
     */
    public function write(string $content): void;
}
