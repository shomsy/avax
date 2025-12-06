<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Runner\Generators\Entity;

use Avax\Database\Migration\Runner\Generators\AbstractGenerator;
use RuntimeException;

/**
 * Final class for generating entity classes based on table schema.
 * Extends the AbstractGenerator for reusing the generator logic.
 */
final class EntityGenerator extends AbstractGenerator
{
    /**
     * Create an entity class file for the given table and fields.
     *
     * @param string $tableName The name of the table.
     * @param array  $fields    The fields' definitions of the table.
     *
     * @throws RuntimeException If paths or namespaces are not configured correctly.
     */
    public function create(string $tableName, array $fields) : void
    {
        // Load namespace and path from configuration.
        $namespace = config(key: 'app.namespaces.Entity');
        $path      = config(key: 'app.paths.Entity');

        // If a namespace or path is not configured, throw an exception.
        if (! $namespace || ! $path) {
            throw new RuntimeException(message: 'Entity paths or namespaces are not configured correctly.');
        }

        // Generate class name using AbstractGenerator's method.
        $className = $this->generateMigrationClassName(tableName: $tableName, type: 'entity');

        // Load and replace placeholders in the stub.
        $stub = $this->getStub(stubName: 'entity.stub');
        $stub = $this->replacePlaceholders(stub: $stub, placeholders: [
            'EntityName'  => $className,
            'Namespace'   => $namespace,
            'Properties'  => $this->generateProperties(fields: $fields),
            'Constructor' => $this->generateConstructor(fields: $fields),
            'Methods'     => $this->generateMethods(fields: $fields),
        ]);

        // Resolve the destination path and write the file.
        $destinationPath = $this->resolvePath(namespace: $namespace, name: $className);
        $this->writeToFile(path: $destinationPath, content: $stub);
    }

    /**
     * Generate class properties for the given fields.
     *
     * @param array $fields The fields' definitions of the table.
     *
     * @return string A string containing the generated properties.
     */
    private function generateProperties(array $fields) : string
    {
        return implode(
            PHP_EOL,
            array_map(
                fn($field) : string => sprintf(
                    '    protected %s $%s;',
                    $this->mapType(type: $field['type']),
                    $field['name']
                ),
                $fields
            )
        );
    }

    /**
     * Convert database field types to corresponding PHP types.
     *
     * @param string $type The database field type.
     *
     * @return string The corresponding PHP type.
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

    /**
     * Generate constructor method for the entity class.
     *
     * @param array $fields The fields' definitions of the table.
     *
     * @return string A string containing the generated constructor.
     */
    private function generateConstructor(array $fields) : string
    {
        // Arguments for the constructor.
        $args = implode(
            ', ',
            array_map(
                fn($field) : string => sprintf(
                    '%s|null $%s = null',
                    $this->mapType(type: $field['type']),
                    $field['name']
                ),
                $fields
            )
        );

        // Property assignments in the constructor.
        $assignments = implode(
            PHP_EOL,
            array_map(
                fn($field) : string => sprintf('        $this->%s = $%s;', $field['name'], $field['name']),
                $fields
            )
        );

        return <<<PHP
            public function __construct({$args})
            {
                {$assignments}
            }
            PHP;
    }

    /**
     * Generate getter and setter methods for the entity class.
     *
     * @param array $fields The fields' definitions of the table.
     *
     * @return string A string containing the generated methods.
     */
    private function generateMethods(array $fields) : string
    {
        $methods = array_map(function (array $field) : string {
            // Generating getter method.
            $getter = <<<PHP
                public function get{$this->camelCase(name: $field['name'])}(): ?{$this->mapType(type: $field['type'])}
                {
                    return \$this->{$field['name']};
                }
                PHP;

            // Generating setter method.
            $setter = <<<PHP
                                                            public function set{$this->camelCase(
                    name: $field['name']
                )}({$this->mapType(
                    type: $field['type']
                )} \${$field['name']}): self
                                                            {
                                                                \$this->{$field['name']} = \${$field['name']};
                                                                return \$this;
                                                            }
                PHP;

            return "{$getter}\n\n{$setter}";
        }, $fields);

        return implode(PHP_EOL, $methods);
    }

    /**
     * Convert snake_case to CamelCase.
     *
     * @param string $name The string in snake_case.
     *
     * @return string The string converted to CamelCase.
     */
    private function camelCase(string $name) : string
    {
        return ucfirst(
            str_replace(
                ' ',
                '',
                ucwords(
                    str_replace('_', ' ', $name)
                )
            )
        );
    }
}