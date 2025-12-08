<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features;

use Exception;

/**
 * AuditRotator - Log Rotation and Size Management
 *
 * Manages audit log rotation based on size and age.
 * Prevents unbounded log growth in production environments.
 *
 * Features:
 * - Size-based rotation (max file size)
 * - Time-based rotation (daily, weekly, monthly)
 * - Automatic compression of old logs
 * - Retention policy (delete old logs)
 * - Atomic rotation (no data loss)
 *
 * @example
 *   $rotator = new AuditRotator('/var/log/session.log');
 *   $rotator->setMaxSize(10 * 1024 * 1024);  // 10 MB
 *   $rotator->setMaxFiles(7);                 // Keep 7 days
 *   $rotator->rotate();
 *
 * @package Avax\HTTP\Session\Features
 */
final class AuditRotator
{
    private const DEFAULT_MAX_SIZE  = 10485760; // 10 MB
    private const DEFAULT_MAX_FILES = 7;
    private const DEFAULT_COMPRESS  = true;

    /**
     * AuditRotator Constructor.
     *
     * @param string $logPath  Path to log file.
     * @param int    $maxSize  Maximum file size in bytes (default: 10 MB).
     * @param int    $maxFiles Maximum number of rotated files to keep (default: 7).
     * @param bool   $compress Compress rotated logs (default: true).
     */
    public function __construct(
        private string $logPath,
        private int    $maxSize = self::DEFAULT_MAX_SIZE,
        private int    $maxFiles = self::DEFAULT_MAX_FILES,
        private bool   $compress = self::DEFAULT_COMPRESS
    ) {}

    /**
     * Force rotation regardless of size.
     *
     * Useful for time-based rotation (daily, weekly, etc).
     *
     * @return bool True on success.
     */
    public function forceRotate() : bool
    {
        if (! file_exists($this->logPath)) {
            return false;
        }

        // Temporarily set max size to 0 to force rotation
        $originalMaxSize = $this->maxSize;
        $this->maxSize   = 0;

        $result = $this->rotate();

        $this->maxSize = $originalMaxSize;

        return $result;
    }

    /**
     * Rotate the log file.
     *
     * Renames current log to .1, shifts existing rotated logs,
     * and optionally compresses old logs.
     *
     * @return bool True on success.
     */
    public function rotate() : bool
    {
        if (! $this->shouldRotate()) {
            return false;
        }

        try {
            // Shift existing rotated logs
            $this->shiftRotatedLogs();

            // Rename current log to .1
            $rotatedPath = $this->logPath . '.1';
            if (! rename($this->logPath, $rotatedPath)) {
                return false;
            }

            // Compress if enabled
            if ($this->compress) {
                $this->compressLog($rotatedPath);
            }

            // Clean up old logs
            $this->cleanupOldLogs();

            return true;
        } catch (Exception $e) {
            error_log("Log rotation failed: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Check if rotation is needed.
     *
     * @return bool True if log should be rotated.
     */
    public function shouldRotate() : bool
    {
        if (! file_exists($this->logPath)) {
            return false;
        }

        $size = filesize($this->logPath);

        return $size >= $this->maxSize;
    }

    /**
     * Shift existing rotated logs.
     *
     * .1 → .2, .2 → .3, etc.
     *
     * @return void
     */
    private function shiftRotatedLogs() : void
    {
        // Start from the highest number and work backwards
        for ($i = $this->maxFiles - 1; $i >= 1; $i--) {
            $oldPath = $this->logPath . '.' . $i;
            $newPath = $this->logPath . '.' . ($i + 1);

            // Check both compressed and uncompressed versions
            foreach ([$oldPath, $oldPath . '.gz'] as $path) {
                if (file_exists($path)) {
                    $targetPath = ($path === $oldPath) ? $newPath : $newPath . '.gz';
                    rename($path, $targetPath);
                }
            }
        }
    }

    /**
     * Compress a log file using gzip.
     *
     * @param string $path Path to log file.
     *
     * @return bool True on success.
     */
    private function compressLog(string $path) : bool
    {
        if (! file_exists($path)) {
            return false;
        }

        $compressedPath = $path . '.gz';

        // Read original file
        $content = file_get_contents($path);
        if ($content === false) {
            return false;
        }

        // Compress and write
        $compressed = gzencode($content, 9);
        if ($compressed === false) {
            return false;
        }

        if (file_put_contents($compressedPath, $compressed) === false) {
            return false;
        }

        // Delete original
        unlink($path);

        return true;
    }

    /**
     * Clean up old rotated logs beyond retention limit.
     *
     * @return void
     */
    private function cleanupOldLogs() : void
    {
        for ($i = $this->maxFiles + 1; $i <= $this->maxFiles + 10; $i++) {
            foreach ([$this->logPath . '.' . $i, $this->logPath . '.' . $i . '.gz'] as $path) {
                if (file_exists($path)) {
                    unlink($path);
                }
            }
        }
    }

    /**
     * Set maximum file size.
     *
     * @param int $bytes Size in bytes.
     *
     * @return self Fluent interface.
     */
    public function setMaxSize(int $bytes) : self
    {
        $this->maxSize = $bytes;

        return $this;
    }

    /**
     * Set maximum number of rotated files to keep.
     *
     * @param int $count Number of files.
     *
     * @return self Fluent interface.
     */
    public function setMaxFiles(int $count) : self
    {
        $this->maxFiles = $count;

        return $this;
    }

    /**
     * Enable or disable compression.
     *
     * @param bool $compress Enable compression.
     *
     * @return self Fluent interface.
     */
    public function setCompress(bool $compress) : self
    {
        $this->compress = $compress;

        return $this;
    }

    /**
     * Get configuration summary.
     *
     * @return array<string, mixed> Configuration.
     */
    public function getConfig() : array
    {
        return [
            'log_path'      => $this->logPath,
            'max_size'      => $this->formatBytes($this->maxSize),
            'max_files'     => $this->maxFiles,
            'compress'      => $this->compress,
            'current_size'  => $this->getCurrentSizeFormatted(),
            'total_size'    => $this->formatBytes($this->getTotalSize()),
            'rotated_count' => count($this->getRotatedLogs()),
        ];
    }

    /**
     * Format bytes to human-readable string.
     *
     * @param int $bytes Bytes.
     *
     * @return string Formatted string.
     */
    private function formatBytes(int $bytes) : string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i     = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get human-readable current size.
     *
     * @return string Size with unit (e.g., "5.2 MB").
     */
    public function getCurrentSizeFormatted() : string
    {
        return $this->formatBytes($this->getCurrentSize());
    }

    /**
     * Get current log file size.
     *
     * @return int Size in bytes, or 0 if file doesn't exist.
     */
    public function getCurrentSize() : int
    {
        if (! file_exists($this->logPath)) {
            return 0;
        }

        return filesize($this->logPath);
    }

    /**
     * Get total size of all log files (current + rotated).
     *
     * @return int Total size in bytes.
     */
    public function getTotalSize() : int
    {
        $total = $this->getCurrentSize();

        foreach ($this->getRotatedLogs() as $path) {
            $total += filesize($path);
        }

        return $total;
    }

    /**
     * Get list of all rotated log files.
     *
     * @return array<string> List of file paths.
     */
    public function getRotatedLogs() : array
    {
        $logs = [];

        for ($i = 1; $i <= $this->maxFiles + 10; $i++) {
            foreach ([$this->logPath . '.' . $i, $this->logPath . '.' . $i . '.gz'] as $path) {
                if (file_exists($path)) {
                    $logs[] = $path;
                }
            }
        }

        return $logs;
    }
}
