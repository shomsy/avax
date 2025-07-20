<?php

declare(strict_types=1);

namespace Gemini\Database\Migration\Runner\Stubs;

use Gemini\Gemini;
use RuntimeException;

/**
 * Resolves and loads stub files for generators.
 */
final readonly class StubResolver
{
    /**
     * Path to stub files in the framework for migrations.
     */
    private string $defaultStubPath;

    public function __construct()
    {
        // Use the Gemini enum to dynamically resolve the default stub path.
        $this->defaultStubPath = Gemini::MIGRATIONS->resolve() . 'stubs/';
    }

    /**
     * Resolves a stub file by its name.
     *
     * @param string $stubName Name of the stub file.
     *
     * @return string Content of the stub file.
     */
    public function resolve(string $stubName) : string
    {
        $filePath = $this->defaultStubPath . $stubName;

        if (! file_exists($filePath)) {
            throw new RuntimeException(message: 'Stub file not found: ' . $filePath);
        }

        return file_get_contents($filePath);
    }
}
