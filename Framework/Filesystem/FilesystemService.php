<?php

declare(strict_types=1);

namespace Gemini\Filesystem;

/**
 * Logging to handle file system operations.
 *
 * This service abstracts common file system operations such as writing files
 * and ensuring that directories are writable.
 */
class FilesystemService
{
    /**
     * Write content to a file.
     *
     * @param string $fileName The file path.
     * @param string $content  The content to write into the file.
     */
    public function writeFile(string $fileName, string $content) : void
    {
        file_put_contents(filename: $fileName, data: $content);
    }

    /**
     * Ensure the given directory is writable. If it's not writable, attempt to change the permissions.
     *
     * This method checks if the specified directory is writable and, if not, it attempts to set the necessary
     * permissions (e.g., 0777) to allow writing.
     *
     * @param string $directory The directory path to check.
     */
    public function ensureDirectoryIsWritable(string $directory) : void
    {
        if (! file_exists(filename: $directory)) {
            mkdir(
                directory  : $directory,
                permissions: 0755,
                recursive  : true,
            ); // Recursively create directories if they don't exist
        }

        if (! is_writable(filename: $directory)) {
            chmod(filename: $directory, permissions: 0755); // Ensure it is writable
        }
    }
}
