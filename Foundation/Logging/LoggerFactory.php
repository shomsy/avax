<?php

declare(strict_types=1);

namespace Avax\Logging;

use Avax\Config\Architecture\DDD\AppPath;
use Avax\Logging\Writers\RotatingFileLogWriter;
use RuntimeException;

/**
 * LoggerFactory
 *
 * An environment-aware and PSR-3-compatible logger factory.
 *
 * Creates scoped, rotating file-based loggers based on application
 * environment and logical "channels" (e.g., error, auth, session).
 *
 * Design principles:
 * - Follows the Factory Pattern for logger creation
 * - Favors composition (delegates to `RotatingFileLogWriter`)
 * - Promotes separation of concerns (no writing logic here)
 * - Prepares loggers for safe use in production, dev, test
 *
 * @package Avax\Logging
 */
final class LoggerFactory
{
    /**
     * Creates the default global error logger.
     *
     * Uses the current application environment (`APP_ENV`) to determine the
     * base filename of the log, allowing per-environment log separation.
     *
     * - `production` → `prod_error-error.log.YYYY-MM-DD`
     * - `development` → `dev_error-error.log.YYYY-MM-DD`
     * - fallback → `error-error.log.YYYY-MM-DD`
     *
     * @return ErrorLogger The error logger instance, pre-configured with a rotating writer.
     */
    public function create() : ErrorLogger
    {
        // Get the environment from env()
        $env = env(key: 'APP_ENV');

        // Determine base name based on environment
        $baseName = match ($env) {
            'production'  => 'production-errors',
            'stage'       => 'stage-errors',
            'staging'     => 'staging-errors',
            'development' => 'dev-errors',
            default       => 'errors',
        };

        // Delegate to the specific channel-based logger builder
        return $this->createLoggerFor(channel: "{$baseName}-log");
    }

    /**
     * Creates a named logger channel (e.g. "session", "auth", etc.)
     * Each channel gets its own file, allowing for clean separation of logs.
     *
     * - Supports multiple log consumers (e.g., Auth, Session, Queue) with their own files.
     * - Uses a rotating file log writer (1 file per day).
     * - Sets timezone for all entries based on `APP_TZ` env or system fallback.
     *
     * @param string $channel Name of the channel (used as log file base name).
     *
     * @return ErrorLogger A PSR-3-compatible logger instance.
     */
    public function createLoggerFor(string $channel) : ErrorLogger
    {
        // Resolve a full path based on configured log directory + channel name
        $path = rtrim(AppPath::LOGS_PATH->get(), '/') . '/' . trim($channel, '/');

        // Ensure the directory is safe to use
        $this->ensureLogDirectoryIsWritable(logPath: $path);

        // Return the logger instance with a rotating file writer
        return new ErrorLogger(
            logWriter: new RotatingFileLogWriter(
                           baseLogPath: $path,
                           timezone   : env(
                                            key    : 'APP_TZ',
                                            default: date_default_timezone_get()
                                        )
                       )
        );
    }

    /**
     * Ensures that the directory for the log file exists and is writable.
     *
     * - If the directory does not exist, it is created recursively.
     * - If the directory exists but is not writable, a RuntimeException is thrown.
     * - This ensures the system does not silently fail when logging.
     *
     * @param string $logPath Full path to the intended log file.
     *
     * @throws RuntimeException If a directory is not writable or cannot be created.
     */
    private function ensureLogDirectoryIsWritable(string $logPath) : void
    {
        // Extract the parent directory from the log file path
        $dir = dirname(path: $logPath);

        // Attempt to create the directory if it does not exist
        if (! is_dir($dir) && ! mkdir($dir, 0750, true) && ! is_dir($dir)) {
            throw new RuntimeException(
                message: "Failed to create log directory: {$dir}"
            );
        }

        // Verify write permission (prevents silent failures or security issues)
        if (! is_writable($dir)) {
            throw new RuntimeException(
                message: "Log directory not writable: {$dir}"
            );
        }
    }
}
