<?php

declare(strict_types=1);

namespace Avax\Logging;

use Avax\Facade\Facades\Storage;
use Exception;

/**
 * Final class FileLogWriter
 *
 * This class implements the LogWriterInterface to write log entries to a file.
 * It uses the Storage facade to handle filesystem operations and includes a fallback mechanism
 * if the primary log file path is not accessible.
 */
final class FileLogWriter implements LogWriterInterface
{
    /**
     * The fallback path for logging if the primary path is unavailable
     *
     * Using a constant for an alternative location ensures logging can still function
     * even when the specified path encounters issues.
     *
     * @var string
     */
    private const string FALLBACK_PATH = '/tmp/fallback-log.log';

    /**
     * Constructor to initialize an instance with a specified file path
     * and perform initial log file setup.
     *
     * @param string $filePath The path to the log file that needs to be initialized.
     *
     * @return void
     */
    public function __construct(private string $filePath)
    {
        $this->initializeLogFile();
    }

    /**
     * Initializes the log file by ensuring the directory exists and is writable.
     *
     * This method checks and creates the directory if it does not exist.
     * It switches to a fallback path if the specified path cannot be accessed.
     */
    private function initializeLogFile() : void
    {
        $directory = dirname(path: $this->filePath);
        // Attempt to ensure the directory exists or use fallback if creation fails
        if (! Storage::exists(path: $directory) && ! Storage::createDirectory(directory: $directory)) {
            $this->filePath = self::FALLBACK_PATH;
            $this->ensureWritable();

            return;
        }

        // Set directory permissions to ensure it is writable
        Storage::setPermissions(path: $directory, permissions: 0755);
        $this->ensureWritable();
    }

    /**
     * Ensures the file is writable, using fallback if necessary.
     *
     * This check ensures that writing logs does not fail silently by always
     * having a writable destination, either the target or fallback file.
     */
    private function ensureWritable() : void
    {
        if (! Storage::exists(path: $this->filePath) && ! $this->attemptFileCreation()) {
            $this->filePath = self::FALLBACK_PATH;
            Storage::write(path: $this->filePath, content: ''); // Create an empty file if it doesn't exist
        }
    }

    /**
     * Attempts to create the file and set appropriate permissions.
     *
     * @return bool True if file creation is successful; false otherwise.
     */
    private function attemptFileCreation() : bool
    {
        Storage::write(path: $this->filePath, content: ''); // Create an empty file
        Storage::setPermissions(path: $this->filePath, permissions: 0644);

        return Storage::exists(path: $this->filePath);
    }

    /**
     * Writes the content to the file. Uses fallback if a primary path fails.
     *
     * @param string $content The log entry content to write.
     */
    public function write(string $content) : void
    {
        try {
            Storage::write(path: $this->filePath, content: $content . PHP_EOL, append: true);
        } catch (Exception) {
            Storage::write(path: self::FALLBACK_PATH, content: $content . PHP_EOL, append: true);
        }
    }
}