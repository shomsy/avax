<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Logging\System\Capabilities\Writers;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\Security\Redaction\System\PublicSurface\Redaction;
use DateTime;
use DateTimeZone;

/**
 * Capability to write logs to rotating files with retention management.
 * Uses Security/Redaction to redact sensitive data before writing.
 * Uses Application/Filesystem for all file I/O.
 * Rotation uses Filesystem::listDirectory() + filemtime() for age sorting.
 */
final class RotatingFileWriter
{
    /** @var list<string> */
    private static array $defaultSensitiveKeys
        = [
            'password', 'secret', 'token', 'key', 'authorization',
            'api_key', 'apikey', 'access_token', 'refresh_token',
            'session_id', 'PHPSESSID', 'cookie',
        ];

    private Filesystem $filesystem;

    public function __construct(
        private string $baseLogPath,
        private string $timezone = 'UTC',
        private int $maxLogFiles = 30,
        ?Filesystem $filesystem = null,
    ) {
        $this->filesystem = $filesystem ?? new Filesystem();
    }

    public function write(string $message, string $level = 'info', array $context = []): void
    {
        $date = new DateTime('now', new DateTimeZone($this->timezone))->format('Y-m-d');
        $directory = dirname($this->baseLogPath);
        $filename = basename($this->baseLogPath);

        $filePath = sprintf('%s/%s-%s.log', $directory, $date, $filename);

        if (! $this->filesystem->exists($directory)) {
            $this->filesystem->createDirectory($directory, 0o755);
        }

        // Redact sensitive data before writing to log file
        $redactedContext = Redaction::redactLog(
            logData      : $context,
            sensitiveKeys: self::$defaultSensitiveKeys,
        );

        $timestamp = new DateTime('now', new DateTimeZone($this->timezone))->format('Y-m-d H:i:s');
        $formatted = sprintf("[%s] %s: %s %s\n", $timestamp, strtoupper($level), $message, json_encode($redactedContext));

        $this->filesystem->append($filePath, $formatted);

        $this->rotate();
    }

    private function rotate(): void
    {
        $directory = dirname($this->baseLogPath);
        $filename = basename($this->baseLogPath);
        $suffix = "-{$filename}.log";

        // Use Filesystem to list directory, then filter and sort by age
        $entries = $this->filesystem->listDirectory($directory);

        $files = array_values(array_filter($entries, static function (string $entry) use ($directory, $suffix) : bool {
            $fullPath = $directory . '/' . $entry;

            return is_file($fullPath) && str_ends_with($entry, $suffix);
        }));

        if (count($files) <= $this->maxLogFiles) {
            return;
        }

        // Sort by modification time (oldest first)
        usort($files, static fn (string $a, string $b) : int => filemtime($directory . '/' . $a) - filemtime($directory . '/' . $b));

        while (count($files) > $this->maxLogFiles) {
            $oldest = array_shift($files);
            $this->filesystem->delete($directory . '/' . $oldest);
        }
    }
}
