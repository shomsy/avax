<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Runner\Stubs;

use Avax\Avax;
use RuntimeException;

/**
 * Resolves and loads stub files for generators.
 */
final readonly class StubResolver
{
    /**
     * Path to stub files in the Foundation for migrations.
     */
    private string $defaultStubPath;

    public function __construct()
    {
        // Use the Avax enum to dynamically resolve the default stub path.
        $this->defaultStubPath = Avax::MIGRATIONS->resolve() . 'stubs/';
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
