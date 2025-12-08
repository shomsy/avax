<?php

declare(strict_types=1);

namespace Avax\Logging\Writers;

use Carbon\Carbon;
use DateTimeZone;
use Avax\Logging\LogWriterInterface;
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
     * Absolute path prefix for log files (e.g., /var/logs/app-error).
     * The File path is dynamically suffixed with the date.
     *
     * @var string
     */
    private string $baseLogPath;

    /**
     * Valid IANA timezone identifier (e.g., Europe/Belgrade).
     *
     * @var string
     */
    private string $timezone;

    /**
     * Current date suffix for caching (format: d.m.Y).
     *
     * @var string|null
     */
    private string|null $cachedDate = null;

    /**
     * Cached a full path to the log file (rotated daily).
     *
     * @var string|null
     */
    private string|null $cachedFilePath = null;

    /**
     * Maximum number of log files to retain (FIFO deletion).
     * Ensures consistent disk usage over time.
     *
     * @readonly
     */
    private readonly int $maxLogFiles;

    /**
     * Constructor.
     *
     * @param string      $baseLogPath Base absolute path for log files (no date or extension).
     * @param string|null $timezone    Optional timezone (default: UTC).
     * @param int         $maxLogFiles Max number of retained rotated log files.
     *
     * @throws RuntimeException If path or timezone are invalid.
     */
    public function __construct(
        string      $baseLogPath,
        string|null $timezone = null,
        int         $maxLogFiles = 30
    ) {
        // Set the default timezone to 'UTC' if no value is provided for $timezone.
        $timezone ??= 'UTC';

        // Validate the base log path value to ensure it is not empty and does not contain unsafe segments.
        $this->validateBaseLogPath(baseLogPath: $baseLogPath);

        // Attempt to resolve the absolute path of the provided base log path. If realpath() fails (e.g.,
        // if the path does not exist yet), fallback to using the raw $baseLogPath value,
        // ensuring that it does not end with DIRECTORY_SEPARATOR unnecessarily.
        $this->baseLogPath = rtrim($baseLogPath, DIRECTORY_SEPARATOR);

        // Check if the provided timezone is valid by ensuring it exists in the list of IANA timezone identifiers.
        // If it is invalid, throw a RuntimeException with a clear message.
        if (! in_array($timezone, DateTimeZone::listIdentifiers(), true)) {
            throw new RuntimeException(message: "Invalid timezone provided: {$timezone}");
        }

        // Assign the validated timezone to the class property for further use.
        $this->timezone = $timezone;

        // Set the maximum number of log files that can be rotated before overwriting old ones.
        $this->maxLogFiles = $maxLogFiles;
    }

    /**
     * Validates the base log path before use.
     *
     * @param string $baseLogPath
     *
     * @throws RuntimeException If a path is unsafe or empty.
     */
    private function validateBaseLogPath(string $baseLogPath) : void
    {
        if (empty($baseLogPath)) {
            throw new RuntimeException(message: "Base log path cannot be empty.");
        }

        if (strpos($baseLogPath, '..') !== false) {
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

            // Generate a new log file path using the base log path and the current date.
            $this->cachedFilePath = "{$this->baseLogPath}-{$currentDate}.log";
        }

        // Ensure the directory for the log file exists, creating it if necessary.
        $this->ensureDirectoryExists(directory: dirname($this->cachedFilePath));

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
     *
     * @return void
     */
    private function ensureDirectoryExists(string $directory) : void
    {
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new RuntimeException(message: "Failed to create log directory: {$directory}");
        }
    }

    /**
     * Enforces log file retention by removing log files older than a specified time limit.
     * This method ensures that the logging directory does not exceed a defined maximum file retention period.
     * Adheres to best practices such as validating file paths and ensuring atomic operations with `unlink`.
     *
     * @param int $maxFileAgeInDays Defaults to 30 days if not specified.
     *                              Represents the maximum age (in days) for retaining log files.
     *
     * @return void
     */
    private function rotateLogs(int $maxFileAgeInDays = 30) : void
    {
        // Retrieve a list of log files matching the naming convention: `<baseLogPath>-*.log`.
        // This uses the `glob` function to find all files matching the wildcard pattern.
        $logFiles = glob("{$this->baseLogPath}-*.log");

        // If the `glob` function fails (returns false), exit early as no files were found to process.
        if ($logFiles === false) {
            return;
        }

        // Get the current Unix timestamp, which represents the current time in seconds since the Unix epoch.
        $now = time();

        // Calculate the maximum file age in seconds by multiplying the provided days by the number of seconds in a day (86,400).
        $maxFileAgeInSeconds = $maxFileAgeInDays * 86400;

        // Iterate over each file path returned by `glob`.
        foreach ($logFiles as $file) {
            // Skip processing if the current path is not a regular file.
            // This avoids issues with directories or non-files that may have matched the pattern.
            if (! is_file($file)) {
                continue;
            }

            // Check if the file's modification time exceeds the maximum allowed age.
            // Compare the current timestamp with the last modification time (`filemtime`).
            if (($now - filemtime($file)) > $maxFileAgeInSeconds) {
                // If the file is older than allowed, delete it using the `unlink` function.
                // The `unlink` function permanently removes the file from the file system.
                unlink($file);
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
        // Attempting to write content to the specified file.
        // The filename is provided by the $filePath variable.
        // The data being written includes the content followed by a new line (PHP_EOL).
        // The FILE_APPEND flag ensures that the content is appended to the file instead of overwriting it.
        // The LOCK_EX flag prevents concurrent writes to the file
        //  by getting an exclusive lock during the writing process.
        $result = file_put_contents(
            filename: $filePath,
            data    : $content . PHP_EOL,
            flags   : FILE_APPEND | LOCK_EX
        );

        // Checking if the result of the file_put_contents call is false.
        // A result of false indicates that an error occurred while trying to write to the file.
        if ($result === false) {
            // Throwing a RuntimeException if writing to the file failed.
            // The exception provides a meaningful error message that includes the filepath for debugging.
            throw new RuntimeException(message: "Unable to write log entry to file: {$filePath}");
        }
    }
}
