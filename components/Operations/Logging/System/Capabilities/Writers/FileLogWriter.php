<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Logging\System\Capabilities\Writers;

use Exception;
use RuntimeException;

/**
 * Writes log entries to a local file.
 */
final class FileLogWriter implements LogWriterInterface
{
    private const string FALLBACK_PATH = '/tmp/avax-fallback.log';

    public function __construct(
        private readonly string $filePath
    )
    {
        $this->ensureDirectoryExists();
    }

    private function ensureDirectoryExists() : void
    {
        $directory = dirname($this->filePath);

        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            // Fallback to /tmp if we can't create the directory
            return;
        }
    }

    public function write(string $content) : void
    {
        try {
            $this->writeFile($this->filePath, $content);
        } catch (Exception) {
            $this->writeFile(self::FALLBACK_PATH, $content);
        }
    }

    private function writeFile(string $path, string $content) : void
    {
        if (file_put_contents($path, $content . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
            throw new RuntimeException("Failed to write to log file: [{$path}]");
        }
    }
}
