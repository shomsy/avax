<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Runner\Generators\Repository;

use Avax\Database\Migration\Runner\Generators\AbstractGenerator;
use RuntimeException;

/**
 * RepositoryGenerator Class
 *
 * This class is responsible for generating repository classes with predefined methods for database operations.
 * It extends from AbstractGenerator and leverages its methods to handle stubs and file writing.
 */
final class RepositoryGenerator extends AbstractGenerator
{
    /**
     * Create a repository class for a given table and entity.
     *
     * @param string $tableName The name of the database table.
     * @param string $entity    The name of the entity class.
     * @param array  $fields    Additional fields used in repository methods. (Default: empty array).
     *
     * @throws RuntimeException If repository paths or namespaces are not configured correctly.
     */
    public function create(string $tableName, string $entity, array $fields = []) : void
    {
        $namespace = config(key: 'app.namespaces.Repositories');
        $path      = config(key: 'app.paths.Repositories');

        // Ensure namespace and path configuration exists
        if (! $namespace || ! $path) {
            throw new RuntimeException(message: 'Repository paths or namespaces are not configured correctly.');
        }

        // Generate class name using AbstractGenerator's method
        $className = $this->generateMigrationClassName(tableName: $tableName, type: 'repository');

        // Load and replace placeholders in the stub
        $stub = $this->getStub(stubName: 'repository.stub');
        $stub = $this->replacePlaceholders(stub: $stub, placeholders: [
            'RepositoryName' => $className,
            'Namespace'      => $namespace,
            'EntityName'     => $entity,
            'Methods'        => $this->generateMethods(entity: $entity),
        ]);

        // Determine the destination path and write the file
        $destinationPath = $this->resolvePath(namespace: $namespace, name: $className);
        $this->writeToFile(path: $destinationPath, content: $stub);
    }

    /**
     * Generate method stubs for the repository class.
     *
     * @param string $entity The name of the entity class.
     *
     * @return string The generated methods as a string.
     */
    private function generateMethods(string $entity) : string
    {
        return <<<PHP
            public function find(int \$id): ?{$entity}
            {
                \$result = \$this->queryBuilder()->where('id', '=', \$id)->first();
                return \$result ? new {$entity}(\$result) : null;
            }
            
            public function findAll(): array
            {
                \$results = \$this->queryBuilder()->get();
                return array_map(fn(\$data) => new {$entity}(\$data), \$results);
            }
            
            public function save({$entity} \$entity): bool
            {
                \$data = get_object_vars(\$entity);
            
                if (!empty(\$data['id'])) {
                    return \$this->queryBuilder()->where('id', '=', \$data['id'])->update(\$data);
                }
            
                \$id = \$this->queryBuilder()->insertGetId(\$data);
                if (\$id) {
                    \$entity->setId(\$id);
                    return true;
                }
            
                return false;
            }
            
            public function delete(int \$id): bool
            {
                return \$this->queryBuilder()->where('id', '=', \$id)->delete();
            }
            PHP;
    }
}