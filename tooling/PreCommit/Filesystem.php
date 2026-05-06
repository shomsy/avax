<?php

declare(strict_types=1);

namespace Avax\Tooling\PreCommit;

final class Filesystem
{
    public function ensureDirectoryExists(string $directory, int $permissions = 0775): void
    {
        if (is_dir($directory)) {
            return;
        }

        if (! mkdir($directory, $permissions, true) && ! is_dir($directory)) {
            throw new HookInstallerException('Failed to create directory: '.$directory);
        }
    }

    public function assertReadableFile(string $file): void
    {
        if (! is_file($file)) {
            throw new HookInstallerException('File does not exist: '.$file);
        }

        if (! is_readable($file)) {
            throw new HookInstallerException('File is not readable: '.$file);
        }
    }

    public function assertExecutable(string $file): void
    {
        if (! is_file($file)) {
            throw new HookInstallerException('Executable does not exist: '.$file);
        }

        if (! is_executable($file)) {
            throw new HookInstallerException('File is not executable: '.$file);
        }
    }

    public function atomicWriteExecutable(string $targetFile, string $content): void
    {
        $directory = dirname($targetFile);
        $temporaryFile = tempnam($directory, basename($targetFile).'.tmp.');

        if ($temporaryFile === false) {
            throw new HookInstallerException('Failed to create temporary file in: '.$directory);
        }

        try {
            $bytesWritten = file_put_contents($temporaryFile, $content, LOCK_EX);

            if ($bytesWritten === false || $bytesWritten !== strlen($content)) {
                throw new HookInstallerException('Failed to write full hook content to: '.$temporaryFile);
            }

            if (! chmod($temporaryFile, 0755)) {
                throw new HookInstallerException('Failed to make hook executable: '.$temporaryFile);
            }

            if (! rename($temporaryFile, $targetFile)) {
                throw new HookInstallerException('Failed to move hook into place: '.$targetFile);
            }
        } finally {
            if (is_file($temporaryFile)) {
                @unlink($temporaryFile);
            }
        }
    }

    public function backupFile(string $file): string
    {
        $backupFile = sprintf(
            '%s.backup.%s',
            $file,
            date('Ymd-His')
        );

        if (! copy($file, $backupFile)) {
            throw new HookInstallerException('Failed to create backup: '.$backupFile);
        }

        return $backupFile;
    }

    public function readFile(string $file): string
    {
        $content = file_get_contents($file);

        if ($content === false) {
            throw new HookInstallerException('Failed to read file: '.$file);
        }

        return $content;
    }

    public function deleteFile(string $file): void
    {
        if (! is_file($file)) {
            return;
        }

        if (! unlink($file)) {
            throw new HookInstallerException('Failed to delete file: '.$file);
        }
    }
}
