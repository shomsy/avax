<?php

declare(strict_types=1);

namespace Avax\Filesystem\Storage;

use Exception;

/**
 * Class Filesystem
 *
 * This class acts as an abstraction layer over various file storage mechanisms.
 * It allows switching between different types of storage (e.g., local, cloud)
 * based on the configuration provided.
 */
readonly class Filesystem
{
    /**
     * Filesystem constructor. Initializes the class with a specific file storage implementation.
     *
     * @param FileStorageInterface $fileStorage The file storage implementation to use.
     */
    public function __construct(private FileStorageInterface $fileStorage) {}

    /**
     * Retrieves the disk storage instance based on the given disk name.
     *
     * This method checks the configuration for the given disk name and returns the appropriate
     * file storage implementation. If the disk driver is not supported, an exception is thrown.
     *
     * @param string|null $name The name of the disk configuration to retrieve. If null, the default configuration will
     *                          be used.
     *
     * @return FileStorageInterface The instance of the file storage based on the disk configuration.
     * @throws \Avax\Container\Exceptions\FoundationContainerException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Exception
     */
    public function disk(string|null $name = null) : FileStorageInterface
    {
        $diskConfig = config(key: "filesystems.disks." . $name, default: config(key: "filesystems.default"));

        if ($diskConfig['driver'] === 'local') {
            return app(abstract: LocalFileStorage::class);
        }

        throw new Exception(message: 'Unsupported disk driver: ' . $diskConfig['driver']);
    }

    /**
     * Reads the content of the given file path.
     *
     * @param string $path The path of the file to read.
     *
     * @return string The content of the file.
     */
    public function read(string $path) : string
    {
        return $this->fileStorage->read(path: $path);
    }

    /**
     * Writes content to the given file path.
     *
     * @param string $path    The path where the content should be written.
     * @param string $content The content to write.
     *
     * @return bool True if the writing was successful, false otherwise.
     */
    public function write(string $path, string $content, bool $append = false) : bool
    {
        return $this->fileStorage->write(path: $path, content: $content, append: $append);
    }

    /**
     * Deletes the file at the specified path.
     *
     * @param string $path The path of the file to delete.
     *
     * @return bool True if the file was successfully deleted, false otherwise.
     */
    public function delete(string $path) : bool
    {
        return $this->fileStorage->delete(path: $path);
    }

    /**
     * Checks if a file exists at the specified path.
     *
     * @param string $path The path to check for existence.
     *
     * @return bool True if the file exists, false otherwise.
     */
    public function exists(string $path) : bool
    {
        return $this->fileStorage->exists(path: $path);
    }

    /**
     * Creates a directory at the specified path.
     *
     * @param string $directory The path of the directory to create.
     *
     * @return bool True if the directory was successfully created, false otherwise.
     */
    public function createDirectory(string $directory) : bool
    {
        return $this->fileStorage->createDirectory(directory: $directory);
    }

    /**
     * Deletes the directory at the specified path.
     *
     * @param string $directory The path of the directory to delete.
     *
     * @return bool True if the directory was successfully deleted, false otherwise.
     */
    public function deleteDirectory(string $directory) : bool
    {
        return $this->fileStorage->deleteDirectory(directory: $directory);
    }

    /**
     * Sets permissions for the file at the specified path.
     *
     * @param string $path        The path of the file.
     * @param int    $permissions The permissions to set.
     *
     * @return bool True if the permissions were successfully set, false otherwise.
     */
    public function setPermissions(string $path, int $permissions) : bool
    {
        return $this->fileStorage->setPermissions(path: $path, permissions: $permissions);
    }

    /**
     * Checks if the specified file or directory has the given permissions.
     *
     * This method allows checking for specific permissions (e.g., readability, writability).
     *
     * @param string $path        The path of the file or directory to check.
     * @param int    $permissions The permissions to check for (e.g., 0755).
     *
     * @return bool True if the path has the specified permissions, false otherwise.
     */
    public function hasPermission(string $path, int $permissions) : bool
    {
        return $this->fileStorage->hasPermission(path: $path, permissions: $permissions);
    }

    /**
     * Clears the contents of the specified directory.
     *
     * This method leverages the storage component to remove all files and subdirectories within the given directory.
     *
     * @param string $directory The path to the directory to be cleared.
     *
     * @return bool Returns true if the directory was successfully cleared, otherwise false.
     */
    public function clear(string $directory) : bool
    {
        return $this->fileStorage->clear(directory: $directory);
    }

    /**
     * Checks if the specified path is writable in the file storage.
     *
     * @param string $path The path to be checked for write permissions.
     *
     * @return bool Returns true if the path is writable, otherwise false.
     */
    public function isWritable(string $path) : bool
    {
        return $this->fileStorage->isWritable(path: $path);
    }


}
