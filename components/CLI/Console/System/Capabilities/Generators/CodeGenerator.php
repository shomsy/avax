<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Console\System\Capabilities\Generators;

use Avax\Components\Application\Text\System\Capabilities\CaseConversion\Str;
use RuntimeException;

/**
 * Abstract base class for code generators.
 *
 * Provides common functionality for generating PHP class stubs
 * including namespace handling, file path resolution, and file writing.
 */
abstract class CodeGenerator
{
    /** Base directory for generated files */
    protected string $baseDirectory;

    /** Default namespace for generated classes */
    protected string $defaultNamespace;

    public function __construct(
        string|null $baseDirectory = null,
        string|null $defaultNamespace = null,
    )
    {
        $this->baseDirectory = $baseDirectory ?? $this->detectBaseDirectory();
        $this->defaultNamespace = $defaultNamespace ?? 'App';
    }

    /**
     * Detect the project base directory.
     */
    protected function detectBaseDirectory() : string
    {
        // Try common project root indicators
        $candidates = [
            getcwd() . '/src',
            getcwd() . '/app',
            getcwd(),
        ];

        foreach ($candidates as $candidate) {
            if (is_dir($candidate)) {
                return $candidate;
            }
        }

        return getcwd() ?: '/tmp';
    }

    /**
     * Generate a class file.
     *
     * @param string $name Class name (StudlyCase)
     * @param array $data Additional data for the template
     */
    abstract public function generate(string $name, array $data = []) : string;

    /**
     * Get the namespace for a generated class.
     */
    protected function getNamespace(string $subDir) : string
    {
        $parts = array_filter(explode('/', $subDir));
        $ns    = $this->defaultNamespace;

        foreach ($parts as $part) {
            $ns .= '\\' . Str::studly($part);
        }

        return $ns;
    }

    /**
     * Write content to a file.
     *
     * @throws RuntimeException if file cannot be written
     */
    protected function writeFile(string $path, string $content) : void
    {
        $dir = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, 0o755, true);
        }

        $result = file_put_contents($path, $content);

        if ($result === false) {
            throw new RuntimeException('Failed to write file: ' . $path);
        }
    }

    /**
     * Check if a class file already exists.
     */
    protected function exists(string $name, string $subDir) : bool
    {
        $path = $this->getFilePath($name, $subDir);

        return file_exists($path);
    }

    /**
     * Get the file path for a generated class.
     *
     * @param string $name Class name
     * @param string $subDir Subdirectory within the base (e.g. "Controllers")
     */
    protected function getFilePath(string $name, string $subDir) : string
    {
        $dir = rtrim($this->baseDirectory, '/') . '/' . ltrim($subDir, '/');

        if (! is_dir($dir)) {
            mkdir($dir, 0o755, true);
        }

        return rtrim($dir, '/') . '/' . $name . '.php';
    }

    /**
     * Get the class name from a potentially qualified name.
     */
    protected function extractClassName(string $name) : string
    {
        $parts = explode('\\', $name);

        return end($parts);
    }
}
