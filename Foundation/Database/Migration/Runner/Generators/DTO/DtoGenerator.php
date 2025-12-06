<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Runner\Generators\DTO;

use Avax\Database\Migration\Runner\Generators\AbstractGenerator;
use RuntimeException;

/**
 * Class DtoGenerator
 *
 * A final class responsible for generating DTO (Data Transfer Object)
 * classes based on a given table name and its fields.
 *
 * @package Avax\Database\Migration\Generators
 */
final class DtoGenerator extends AbstractGenerator
{
    /**
     * Generate and create a DTO class file based on provided table name and fields.
     *
     * @param string $tableName The name of the table to generate the DTO for.
     * @param array  $fields    An associative array of fields where 'type' and 'name' are defined.
     *
     * @throws RuntimeException If DTO paths or namespaces are not configured correctly.
     */
    public function create(string $tableName, array $fields) : void
    {
        // Retrieve DTO namespace and path from configuration
        $namespace = config(key: 'app.namespaces.DTO');
        $path      = config(key: 'app.paths.DTO');

        // Ensure namespace and path are configured
        if (! $namespace || ! $path) {
            throw new RuntimeException(message: 'DTO paths or namespaces are not configured correctly.');
        }

        // Generate the class name using the AbstractGenerator's method
        $className = $this->generateMigrationClassName(tableName: $tableName, type: 'dto');

        // Load and replace placeholders in the stub
        $stub = $this->getStub(stubName: 'dto.stub');
        $stub = $this->replacePlaceholders(stub: $stub, placeholders: [
            'DTOName'    => $className,
            'Namespace'  => $namespace,
            'Properties' => $this->generateProperties(fields: $fields),
        ]);

        // Resolve the destination path and write the file
        $destinationPath = $this->resolvePath(namespace: $namespace, name: $className);
        $this->writeToFile(path: $destinationPath, content: $stub);
    }

    /**
     * Generate formatted properties for the DTO class.
     *
     * @param array $fields An array of fields with 'type' and 'name'.
     *
     * @return string Formatted properties as strings.
     */
    private function generateProperties(array $fields) : string
    {
        return implode(
            PHP_EOL,
            array_map(
                fn($field) : string => sprintf(
                    '    public %s $%s;',
                    $this->mapType(type: $field['type']),
                    $field['name']
                ),
                $fields
            )
        );
    }

    /**
     * Map database types to PHP types.
     *
     * @param string $type The database type (e.g., 'string', 'int').
     *
     * @return string The corresponding PHP type (e.g., 'string', 'int') or 'mixed' if not mapped.
     */
    private function mapType(string $type) : string
    {
        return match ($type) {
            'string', 'text'             => 'string',
            'int', 'integer', 'bigint'   => 'int',
            'float', 'double', 'decimal' => 'float',
            'bool', 'boolean'            => 'bool',
            default                      => 'mixed',
        };
    }
}