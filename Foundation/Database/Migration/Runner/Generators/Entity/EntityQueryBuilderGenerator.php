<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Runner\Generators\Entity;

use Avax\Database\Migration\Runner\Generators\AbstractGenerator;
use RuntimeException;

/**
 * EntityQueryBuilderGenerator Class
 *
 * Generates PHP entity classes with integrated query builder functionality.
 * Designed to automate generation of entity classes with standard CRUD operations.
 */
final class EntityQueryBuilderGenerator extends AbstractGenerator
{
    /**
     * Create a new entity class with integrated query builder methods.
     *
     * @param string $name   The name of the entity class to create.
     * @param string $table  The name of the database table associated with the entity.
     * @param array  $fields The fields to include in the entity class.
     *
     * @throws RuntimeException if necessary configuration is missing.
     */
    public function create(string $name, string $table, array $fields) : void
    {
        $namespace = config(key: 'app.namespaces.Entity');
        $path      = config(key: 'app.paths.Entity');
        if (! $namespace || ! $path) {
            throw new RuntimeException(message: 'Entity paths or namespaces are not configured correctly.');
        }

        // Generate class name based on the table and entity type.
        $className = $this->generateMigrationClassName(tableName: $table, type: 'entity');

        // Load and replace placeholders in the stub.
        $stub = $this->getStub(stubName: 'entity-querybuilder.stub');
        $stub = $this->replacePlaceholders(stub: $stub, placeholders: [
            'EntityName'   => $className,
            'TableName'    => $table,
            'Namespace'    => $namespace,
            'QueryMethods' => $this->generateQueryMethods(),
            'Properties'   => $this->generateProperties(fields: $fields),
        ]);

        // Resolve the destination path and write the file.
        $destinationPath = $this->resolvePath(namespace: $namespace, name: $className);
        $this->writeToFile(path: $destinationPath, content: $stub);
    }

    /**
     * Generate standard query methods for the entity.
     *
     * @return string The PHP code for query methods.
     *
     * Methods include common CRUD operations to make entity management straightforward.
     */
    private function generateQueryMethods() : string
    {
        return <<<PHP
            public function find(int \$id): ?self
            {
                \$result = \$this->where('id', '=', \$id)->first();
                return \$result ? (new static())->fillFromArray(\$result) : null;
            }
            
            public function findAll(): array
            {
                \$results = \$this->get();
                return array_map(fn(array \$data) => (new static())->fillFromArray(\$data), \$results);
            }
            
            public function save(): bool
            {
                \$data = get_object_vars(\$this);
                if (!empty(\$data['id'])) {
                    return \$this->where('id', '=', \$data['id'])->update(\$data);
                }
            
                \$id = \$this->insertGetId(\$data);
                if (\$id) {
                    \$this->id = \$id;
                    return true;
                }
            
                return false;
            }
            
            public function delete(): bool
            {
                if (empty(\$this->id)) {
                    throw new \RuntimeException('Cannot delete an unsaved entity.');
                }
                return \$this->where('id', '=', \$this->id)->delete();
            }
            
            public function fillFromArray(array \$data): self
            {
                foreach (\$data as \$key => \$value) {
                    if (property_exists(\$this, \$key)) {
                        \$this->{\$key} = \$value;
                    }
                }
                return \$this;
            }
            PHP;
    }

    /**
     * Generate properties for the entity class based on given fields.
     *
     * @param array $fields The fields to include in the entity class.
     *
     * @return string The PHP code for entity properties.
     */
    private function generateProperties(array $fields) : string
    {
        return implode(
            PHP_EOL,
            array_map(
                fn(array $field) : string => sprintf(
                    '    protected %s $%s;',
                    $this->mapType(type: $field['type']),
                    $field['name']
                ),
                $fields
            )
        );
    }

    /**
     * Map database types to PHP types for entity properties.
     *
     * @param string $type The database type.
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
}