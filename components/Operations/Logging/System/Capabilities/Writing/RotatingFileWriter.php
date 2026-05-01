<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Logging\System\Capabilities\Writing;

use DateTime;
use DateTimeZone;

/**
 * Capability to write logs to rotating files with retention management.
 *
 * Recovered from legacy RotatingFileLogWriter.
 * Adheres to Capability naming rules.
 */
final class RotatingFileWriter
{
    public function __construct(
        private readonly string $baseLogPath,
        private readonly string $timezone = 'UTC',
        private readonly int $maxLogFiles = 30,
    ) {}

    public function write(string $message, string $level = 'info', array $context = []) : void
    {
        $date     = (new DateTime('now', new DateTimeZone($this->timezone)))->format('Y-m-d');
        $directory = dirname($this->baseLogPath);
        $filename = basename($this->baseLogPath);

        $filePath = "{$directory}/{$date}-{$filename}.log";

        if (! is_dir($directory)) {
            mkdir($directory, 0o755, true);
        }

        $timestamp = (new DateTime('now', new DateTimeZone($this->timezone)))->format('Y-m-d H:i:s');
        $formatted = sprintf("[%s] %s: %s %s\n", $timestamp, strtoupper($level), $message, json_encode($context));

        file_put_contents($filePath, $formatted, FILE_APPEND | LOCK_EX);

        $this->rotate();
    }

    private function rotate() : void
    {
        $directory = dirname($this->baseLogPath);
        $filename = basename($this->baseLogPath);
        $files = glob("{$directory}/*-{$filename}.log");

        if (count($files) <= $this->maxLogFiles) {
            return;
        }

        usort($files, static fn ($a, $b) => filemtime($a) - filemtime($b));

        while ( count($files) > $this->maxLogFiles ) {
            unlink(array_shift($files));
        }
    }
}
