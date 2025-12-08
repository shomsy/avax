<?php

declare(strict_types=1);

namespace Avax\Filesystem;

use Exception;

/**
 * Class to handle directory initialization, ensuring the directory exists,
 * has correct permissions, and is writable. This is critical for scenarios
 * where the application's smooth operation depends on specific directory setups.
 */
readonly class DirectoryInitializer
{
    // Number of attempts to make the directory writable before failing
    private const int RETRY_ATTEMPTS = 3;

    // Delay between retry attempts in microseconds
    private const int RETRY_DELAY = 100000;

    /**
     * DirectoryInitializer constructor.
     * Automatically initializes the directory by creating it if it doesn't exist,
     * setting permissions, and ensuring it is writable.
     *
     * @param string               $directoryPath The path to the directory to be initialized.
     * @param FileServiceInterface $fileService   The file service to handle directory operations.
     *
     * @throws \Exception
     * @throws \Exception
     */
    public function __construct(
        private string               $directoryPath,
        private FileServiceInterface $fileService
    ) {
        $this->initializeDirectory();
    }

    /**
     * Initializes the directory by creating it if necessary and setting permissions.
     * Ensures the directory is writable, which is crucial for subsequent operations.
     *
     * @throws Exception if the directory can't be created or made writable.
     */
    private function initializeDirectory() : void
    {
        // Only create the directory if it does not exist to avoid unnecessary operations
        if (! $this->fileService->isDirectory(path: $this->directoryPath) &&
            ! $this->fileService->createDirectory(path: $this->directoryPath, permissions: 0755)) {
            throw new Exception(message: 'Failed to create directory at ' . $this->directoryPath);
        }

        // Set permissions to 0755 and ensure the directory is writable
        $this->fileService->setPermissions(path: $this->directoryPath, permissions: 0755);
        $this->ensureWritable();
    }

    /**
     * Tries to ensure the directory is writable using multiple attempts.
     * This approach accounts for transient file system issues that may temporarily block write access.
     *
     * @throws Exception if the directory cannot be made writable after the given attempts.
     */
    private function ensureWritable() : void
    {
        for ($attempt = 1; $attempt <= self::RETRY_ATTEMPTS; ++$attempt) {
            // Attempt to write to the directory or retry after a delay
            if ($this->fileService->isWritable(path: $this->directoryPath) || $this->attemptFileCreation()) {
                return;
            }

            usleep(self::RETRY_DELAY);
        }

        throw new Exception(
            message: sprintf(
                         'Unable to make the directory writable at %s. Check file system permissions.',
                         $this->directoryPath
                     )
        );
    }

    /**
     * Attempts to create and write to a test file in the directory to check writability.
     * Removes the test file if successful, maintaining the directory's state.
     *
     * @return bool True if the directory is writable, false otherwise.
     */
    private function attemptFileCreation() : bool
    {
        $testFilePath = $this->directoryPath . '/.write_test';

        // Create the test file if it doesn't exist
        if (! $this->fileService->fileExists(path: $testFilePath)) {
            $this->fileService->createFile(path: $testFilePath);
        }

        // Set permissions for the test file to 0644
        $this->fileService->setPermissions(path: $testFilePath, permissions: 0644);

        // Check if the test file is writable
        $isWritable = $this->fileService->isWritable(path: $testFilePath);
        if ($isWritable) {
            unlink($testFilePath); // Clean up the test file if everything is functioning correctly
        }

        return $isWritable;
    }
}