<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Runner\Generators\Service;

use Avax\Database\Migration\Runner\Generators\AbstractGenerator;
use RuntimeException;

/**
 * Service Generator
 *
 * This generator creates service classes with basic scaffolding.
 * It extends AbstractGenerator to leverage shared utilities for file generation.
 */
final class ServiceGenerator extends AbstractGenerator
{
    /**
     * Creates a new service class.
     *
     * This method uses a stub file as a template, replaces placeholders
     * with actual values, and writes the generated content to a destination path.
     *
     * @param string $name The name of the service class to be generated.
     */
    public function create(string $name) : void
    {
        // Load the namespace and path from configuration
        // Rationale: Allow configuration to dictate the location and structure of generated files
        $namespace = config(key: 'app.namespaces.Services');
        $path      = config(key: 'app.paths.Services');

        if (! $namespace || ! $path) {
            throw new RuntimeException(message: 'Service namespace or path is not configured correctly.');
        }

        // Generate the class name using AbstractGenerator's method
        // Intent: Create standardized class names based on provided table name
        $className = $this->generateMigrationClassName(tableName: $name, type: 'service');

        // Load the service stub file
        // Rationale: Use a template to maintain consistent structure across generated service classes
        $stub = $this->getStub(stubName: 'service.stub');

        // Replace placeholders in the stub
        // Intent: Dynamically insert the class name and namespace into the template
        $stub = $this->replacePlaceholders(
            stub:         $stub,
            placeholders: [
                              'ServiceName' => $className,
                              'Namespace'   => $namespace,
                          ]
        );

        // Resolve the file path
        // Rationale: Ensure the new class is placed in the correct directory based on namespace
        $destinationPath = $this->resolvePath(namespace: $namespace, name: $className);

        // Write the generated content to the file
        // Rationale: Finalize the service class creation by writing the populated template to the file system
        $this->writeToFile(path: $destinationPath, content: $stub);
    }
}