<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Runner\Generators;

use Avax\Config\Architecture\DDD\AppPath;
use Avax\Facade\Facades\Storage;
use RuntimeException;

/**
 * AbstractGenerator
 *
 * Provides reusable foundational logic for migration file generation.
 * Adheres to Clean Architecture and modern DSL philosophy.
 */
abstract class AbstractGenerator
{
    /**
     * Retrieves the contents of a stub file used as a template.
     *
     * @param string $stubName The name of the stub file to retrieve (e.g., 'create.stub').
     *
     * @return string The contents of the stub.
     * @throws RuntimeException If the stub file is missing.
     */
    protected function getStub(string $stubName) : string
    {
        $stubPath = $this->resolveStubPath(stubName: $stubName);

        if (! Storage::exists($stubPath)) {
            throw new RuntimeException(message: sprintf('Stub "%s" not found at path: %s', $stubName, $stubPath));
        }

        return Storage::read($stubPath);
    }

    /**
     * Resolves the absolute path to the specified stub file.
     *
     * @param string $stubName The filename of the stub.
     *
     * @return string The resolved absolute path.
     */
    private function resolveStubPath(string $stubName) : string
    {
        return AppPath::STUBS_PATH->get() . $stubName;
    }

    /**
     * Replaces all placeholders in a stub string with provided values.
     *
     * @param string                $stub         The original stub content.
     * @param array<string, string> $placeholders Array of placeholders and replacement values.
     *
     * @return string The updated stub content.
     */
    protected function replacePlaceholders(string $stub, array $placeholders) : string
    {
        foreach ($placeholders as $placeholder => $value) {
            $stub = str_replace(sprintf('{{%s}}', $placeholder), $value, $stub);
        }

        return $stub;
    }

    /**
     * Writes content to a file and applies secure permissions.
     *
     * @param string $path    The absolute file path.
     * @param string $content The content to write to disk.
     *
     * @throws RuntimeException On write or permission failure.
     */
    protected function writeToFile(string $path, string $content) : void
    {
        $directory = dirname($path);

        // Create a directory if it doesn't exist
        if (! Storage::exists($directory)) {
            Storage::createDirectory($directory);
        }

        // Throws if writing to a file fails
        if (! Storage::write($path, $content)) {
            throw new RuntimeException(message: 'Failed to write file at path: ' . $path);
        }

        $permissions = config(key: 'app.filePermissions', default: 0666);

        if (! Storage::setPermissions($path, $permissions)) {
            throw new RuntimeException(message: 'Failed to set permissions for file: ' . $path);
        }

        $this->setFileOwnership($path);
    }

    /**
     * Ensures the file has appropriate ownership metadata for local development.
     *
     * @param string $path Absolute path of the file.
     */
    private function setFileOwnership(string $path) : void
    {
        if (PHP_OS_FAMILY === 'Linux' || PHP_OS_FAMILY === 'Darwin') {
            $uid = getmyuid() ?: getenv('UID') ?: 1000;
            $gid = getmygid() ?: getenv('GID') ?: 1000;

            shell_exec(sprintf('chown %d:%d %s', $uid, $gid, escapeshellarg($path)));
        }
    }

    /**
     * Resolves the appropriate filesystem path for a given namespace.
     *
     * @param string $namespace The target namespace.
     * @param string $name      The base class name (without extension).
     *
     * @return string Fully qualified file path.
     * @throws RuntimeException If no config path is found for the namespace.
     */
    protected function resolvePath(string $namespace, string $name) : string
    {
        $type = array_keys(config(key: 'app.namespaces'), $namespace, true)[0] ?? null;
        $path = config(key: 'app.paths.' . $type);

        if (! $path) {
            throw new RuntimeException(
                message: sprintf('Path for %s is not defined in app.php configuration.', $namespace)
            );
        }

        return rtrim(base_path(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
               . rtrim((string) $path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
               . ($name . '.php');
    }
}