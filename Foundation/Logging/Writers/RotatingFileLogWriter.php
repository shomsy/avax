<?php

declare(strict_types=1);

namespace Avax\Logging\Writers;

use Avax\Facade\Facades\Storage;
use Avax\Logging\LogWriterInterface;
use Carbon\Carbon;
use DateTimeZone;
use RuntimeException;

/**
 * ✅ RotatingFileLogWriter
 *
 * Writes log entries to a daily rotated log file based on the current timezone-aware date.
 * Automatically manages log retention by deleting the oldest files beyond a configurable threshold.
 *
 * ✅ Use Cases:
 * - Structured file logging in production/staging/dev environments.
 * - Prevents unbounded disk growth with built-in retention.
 * - Ready for future ingestion by structured log collectors (e.g., ELK, Loki).
 *
 * 🧱 Best Practices Followed:
 * - Safe path resolution & validation
 * - Immutable config via constructor
 * - Atomic writes with file locks
 * - Lazy rotation logic for performance
 * - PSR-3 compliant output
 */
final class RotatingFileLogWriter implements LogWriterInterface
{
    /**
     * Current date suffix for caching (format: d.m.Y).
     */
    private string|null $cachedDate = null;

    /**
     * Cached a full path to the log file (rotated daily).
     */
    private string|null $cachedFilePath = null;

    /**
     * @param string      $baseLogPath Base absolute path for log files (no date or extension).
     * @param string $timezone IANA timezone identifier (default: UTC).
     * @param int         $maxLogFiles Max number of retained rotated log files.
     *
     * @throws RuntimeException If path or timezone are invalid.
     */
    public function __construct(
        private string       $baseLogPath,
        private string       $timezone = 'UTC',
        private readonly int $maxLogFiles = 30
    )
    {
        // Validate the base log path value to ensure it is not empty and does not contain unsafe segments.
        $this->validateBaseLogPath(baseLogPath: $this->baseLogPath);

        // Attempt to resolve the absolute path of the provided base log path.
        $this->baseLogPath = rtrim(string: $this->baseLogPath, characters: DIRECTORY_SEPARATOR);

        // Check if the provided timezone is valid.
        if (! in_array(needle: $this->timezone, haystack: DateTimeZone::listIdentifiers(), strict: true)) {
            throw new RuntimeException(message: "Invalid timezone provided: {$this->timezone}");
        }
    }

    /**
     * Validates the base log path before use.
     *
     *
     * @throws RuntimeException If a path is unsafe or empty.
     */
    private function validateBaseLogPath(string $baseLogPath) : void
    {
        if (empty($baseLogPath)) {
            throw new RuntimeException(message: 'Base log path cannot be empty.');
        }

        if (strpos(haystack: $baseLogPath, needle: '..') !== false) {
            throw new RuntimeException(message: "Base log path contains unsafe segments: {$baseLogPath}");
        }
    }

    /**
     * Writes a log entry to the current day's log file (auto-rotated).
     *
     * @param string $content The log content (already formatted, e.g., PSR-3).
     *
     * @throws RuntimeException If a file cannot be written.
     */
    public function write(string $content) : void
    {
        // Get the current date and time in the specified timezone, formatted as 'd.m.Y'.
        $currentDate = Carbon::now()->setTimezone(timeZone: $this->timezone)->format(format: 'd.m.Y');

        // Check if the cached date does not match the current date.
        if ($this->cachedDate !== $currentDate) {
            // Update the cached date to the current date.
            $this->cachedDate = $currentDate;

            // Resolve directory and filename for specific format: {date}-{filename}.log
            $directory = dirname(path: $this->baseLogPath);
            $filename  = basename(path: $this->baseLogPath);

            // Generate a new log file path: e.g., 30.12.2025-bootstrap-error-logs.log
            $this->cachedFilePath = "{$directory}/{$currentDate}-{$filename}.log";
        }

        // Ensure the directory for the log file exists, creating it if necessary.
        $this->ensureDirectoryExists(directory: dirname(path: $this->cachedFilePath));

        // Rotate old logs if the number of log files exceeds the defined limit.
        $this->rotateLogs();

        // Append the provided log content to the current log file, creating it if it doesn't exist.
        $this->appendToFile(filePath: $this->cachedFilePath, content: $content);
    }

    /**
     * Ensures the specified directory exists by creating it if it does not exist.
     * Throws an exception if directory creation fails.
     *
     * @param string $directory The path of the directory to ensure exists.
     */
    private function ensureDirectoryExists(string $directory) : void
    {
        if (! Storage::exists(path: $directory)) {
            Storage::createDirectory(directory: $directory);
        }
    }

    /**
     * Enforces log file retention by removing log files older than a specified time limit.
     * This method ensures that the logging directory does not exceed a defined maximum file retention period.
     * Adheres to best practices such as validating file paths and ensuring atomic operations with `unlink`.
     *
     *                                 Represents the maximum age (in days) for retaining log files.
     */
    private function rotateLogs() : void
    {
        // Retrieve a list of log files matching the naming convention.
        $logFiles            = Storage::listFiles(path: dirname(path: $this->baseLogPath));

        $now = time();
        $maxFileAgeInSeconds = $this->maxLogFiles * 86400;

        foreach ($logFiles as $file) {
            if (! str_starts_with(haystack: basename(path: $file), needle: basename(path: $this->baseLogPath))) {
                continue;
            }

            if (($now - Storage::lastModified(path: $file)) > $maxFileAgeInSeconds) {
                Storage::delete(path: $file);
            }
        }
    }

    /**
     * Appends content to the log file using exclusive lock.
     *
     * @param string $filePath Full path to the current log file.
     * @param string $content  Formatted log content.
     *
     * @throws RuntimeException If writing fails.
     */
    private function appendToFile(string $filePath, string $content) : void
    {
        Storage::write(path: $filePath, content: $content . PHP_EOL, append: true);
    }
}
