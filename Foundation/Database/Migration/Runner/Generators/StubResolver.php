<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Runner\Generators;

use Exception;
use Psr\Log\LoggerInterface;

/**
 * StubResolver Class
 *
 * This class is responsible for handling the location, validation, and reading of stub files
 * used in code generation. It ensures that the directory containing stub files exists and is readable,
 * and it supports reading specific stub files for use in other applications.
 */
readonly class StubResolver
{
    /**
     * @param string          $stubDirectory The directory where stub files are stored.
     * @param LoggerInterface $logger        The logger instance for logging errors and information.
     *
     * @throws Exception If the stub directory is invalid upon instantiation.
     */
    public function __construct(
        private string          $stubDirectory,
        private LoggerInterface $logger
    ) {
        $this->validateStubDirectory();
    }

    /**
     * Validates that the stub directory exists and is readable.
     *
     * Throws an exception if the directory does not exist or is not readable, and logs a critical error.
     * This check is crucial for ensuring that later file operations do not fail due to
     * an invalid directory path.
     *
     * @throws Exception If the stub directory is invalid.
     */
    private function validateStubDirectory() : void
    {
        if (! is_dir($this->stubDirectory) || ! is_readable($this->stubDirectory)) {
            // Log the critical issue that the directory is invalid.
            $this->logger->critical(
                sprintf('Invalid stub directory: "%s".', $this->stubDirectory)
            );

            throw new Exception(
                sprintf(
                    'Stub directory "%s" does not exist or is not readable.',
                    $this->stubDirectory
                )
            );
        }
    }

    /**
     * Reads the contents of a stub file.
     *
     * This method resolves the full path of the stub file and attempts to read its contents.
     * If reading fails, it logs an error and throws an exception.
     * Successfully read content is logged for auditing purposes.
     *
     * @param string $stubName The name of the stub file.
     *
     * @return string The content of the stub file.
     * @throws Exception If the stub file cannot be read.
     */
    public function read(string $stubName) : string
    {
        // Resolve a full path for the specified stub file.
        $stubPath = $this->resolve($stubName);

        $content = file_get_contents($stubPath);
        if ($content === false) {
            // Log the error if reading the file fails.
            $this->logger->error(
                sprintf('Failed to read content of stub file: "%s" at "%s".', $stubName, $stubPath)
            );

            throw new Exception(sprintf('Failed to read stub file: "%s".', $stubName));
        }

        // Log successful read for future reference.
        $this->logger->info(
            sprintf('Successfully read stub file: "%s" from path: "%s".', $stubName, $stubPath)
        );

        return $content;
    }

    /**
     * Resolves the full path of a stub file.
     *
     * This method constructs the full path to the stub file within the stub directory.
     * It checks for the file's existence and readability, logging warnings and throwing exceptions as necessary.
     *
     * @param string $stubName The name of the stub file.
     *
     * @return string The resolved path of the stub file.
     * @throws Exception If the stub file does not exist or is unreadable.
     */
    public function resolve(string $stubName) : string
    {
        $stubPath = rtrim($this->stubDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $stubName;

        // Check that the file exists and is readable before progressing.
        if (! file_exists($stubPath) || ! is_readable($stubPath)) {
            // Log a warning if the file is missing or not accessible.
            $this->logger->warning(
                sprintf('Stub file "%s" not found or unreadable at path: "%s".', $stubName, $stubPath)
            );

            throw new Exception(sprintf('Stub file "%s" not found or unreadable.', $stubName));
        }

        return $stubPath;
    }
}