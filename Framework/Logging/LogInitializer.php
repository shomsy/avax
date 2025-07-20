<?php

declare(strict_types=1);

namespace Gemini\Logging;

use Exception;
use Gemini\Config\Architecture\DDD\AppPath;
use Gemini\Facade\Facades\Storage;

/**
 * Class LogInitializer
 *
 * Handles the initialization of the logs' directory.
 * This includes ensuring that the directory exists, is writable, and has appropriate permissions.
 * This class is intended to be used at the startup of the application to guarantee
 * that logging mechanisms have a valid directory to write to.
 */
class LogInitializer
{
    /** @var string The fallback directory path for logs if the primary path fails */
    private const string FALLBACK_LOG_PATH = '/tmp';

    /** @var int The default directory permissions for log directories */
    private const int DEFAULT_PERMISSIONS = 0755;

    /**
     * Ensures the logs directory exists and is writable.
     *
     * This method checks if the primary log path is valid and writable, attempting to create it if it does not exist.
     * If the primary path is invalid, it falls back to a secondary path. If both fail, an exception is thrown.
     *
     * @throws Exception if the directory cannot be created or is not writable.
     */
    public static function ensureLogsDirectoryExists() : void
    {
        $logPath = AppPath::LOGS_PATH->get();
        // Try to create or validate the primary log path
        if (! self::verifyDirectory(path: $logPath)) {
            $logPath = self::FALLBACK_LOG_PATH;

            // Try the fallback path if primary fails
            if (! self::verifyDirectory(path: $logPath)) {
                throw new Exception(
                    sprintf('Unable to create logs directory at either %s or fallback path.', $logPath)
                );
            }
        }
    }

    /**
     * Verifies or creates a writable directory.
     *
     * This method attempts to verify if the provided directory path is valid and writable.
     * If the directory does not exist, it will attempt to create it with default permissions.
     * It also ensures the directory is writable.
     * If any step fails, it logs an emergency message.
     *
     * @param string $path Path to the directory to verify.
     *
     * @return bool True if the directory is writable, false otherwise.
     */
    private static function verifyDirectory(string $path) : bool
    {
        try {
            // Check if the path is a directory or attempt to create it
            if (! Storage::exists(path: $path) && ! Storage::createDirectory(directory: $path)) {
                return false;
            }

            // Ensure the directory has the correct permissions
            Storage::setPermissions(path: $path, permissions: self::DEFAULT_PERMISSIONS);

            return true;
        } catch (Exception $exception) {
            // Log an emergency message on failure
            error_log(
                sprintf('Failed to initialize log directory at %s: %s', $path, $exception->getMessage()),
                3,
                self::FALLBACK_LOG_PATH . '/emergency_log.log'
            );

            return false;
        }
    }
}
