<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Logging\System\Capabilities\Writing;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use DateTime;
use DateTimeZone;

/**
 * Capability to write logs to rotating files with retention management.
 *
 * Uses Application/Filesystem for all file I/O.
 */
final readonly class RotatingFileWriter
{
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

        $timestamp = new DateTime('now', new DateTimeZone($this->timezone))->format('Y-m-d H:i:s');
        $formatted = sprintf("[%s] %s: %s %s\n", $timestamp, strtoupper($level), $message, json_encode($context));

        $this->filesystem->append($filePath, $formatted);

        $this->rotate();
    }

    private function rotate(): void
    {
        $directory = dirname($this->baseLogPath);
        $filename = basename($this->baseLogPath);
        $pattern = sprintf('%s/*-%s.log', $directory, $filename);

        $files = glob($pattern) ?: [];

        if (count($files) <= $this->maxLogFiles) {
            return;
        }

        usort($files, static fn ($a, $b): int => filemtime($a) - filemtime($b));

        while (count($files) > $this->maxLogFiles) {
            $old = array_shift($files);
            if ($old === null) {
                break;
            }
            $this->filesystem->delete($old);
        }
    }
}
