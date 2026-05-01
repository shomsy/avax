<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\CodeGeneration\System\Capabilities\Generators;

use Avax\Components\Application\Text\System\Capabilities\CaseConversion\Str;
use Override;

/**
 * Generates repository class stubs.
 */
class RepositoryGenerator extends CodeGenerator
{
    /**
     * Generate a repository class file.
     *
     * @param string $name Repository name (e.g. "UserRepository" or "User")
     * @param array  $data Additional data (e.g. ['entity' => 'UserEntity'])
     *
     * @return string The generated file path
     */
    #[Override]
    public function generate(string $name, array $data = []): string
    {
        $className = Str::studly($name);

        // Ensure it ends with "Repository"
        if (! str_ends_with($className, 'Repository')) {
            $className .= 'Repository';
        }

        $subDir    = $data['subDir'] ?? 'Repositories';
        $namespace = $this->getNamespace($subDir);
        $entity    = $data['entity'] ?? $this->inferEntity($className);
        $entityNamespace = $data['entityNamespace'] ?? $this->getNamespace('Entities');

        $stub = $this->buildStub($className, $namespace, $entity, $entityNamespace);
        $path = $this->getFilePath($className, $subDir);

        $this->writeFile($path, $stub);

        return $path;
    }

    /**
     * Infer the entity name from the repository class name.
     */
    protected function inferEntity(string $repositoryName): string
    {
        $base = preg_replace('/Repository$/', '', $repositoryName);

        return in_array($base, ['', '0', []], true) ? 'Entity' : $base;
    }

    /**
     * Build the repository class stub.
     */
    protected function buildStub(
        string $className,
        string $namespace,
        string $entity,
        string $entityNamespace,
    ): string {
        $entityClass = Str::studly($entity);

        if (! str_ends_with($entityClass, 'Entity')) {
            $entityClass .= 'Entity';
        }

        $entityBaseName = ! in_array(preg_replace('/Entity$/', '', $entityClass), ['', '0'], true) && preg_replace('/Entity$/', '', $entityClass) !== [] ? preg_replace('/Entity$/', '', $entityClass) : $entityClass;

        return <<<PHP
            <?php
            
            declare(strict_types=1);
            
            namespace {$namespace};
            
            use {$entityNamespace}\\{$entityClass};
            
            /**
             * Repository for {$entityBaseName} entities.
             */
            class {$className}
            {
                /**
                 * Find an entity by its ID.
                 */
                public function findById(int \$id): ?{$entityClass}
                {
                    // TODO: Implement
                    return null;
                }
            
                /**
                 * Find all entities.
                 *
                 * @return {$entityClass}[]
                 */
                public function findAll() : array
                {
                    // TODO: Implement
                    return [];
                }
            
                /**
                 * Save an entity.
                 */
                public function save({$entityClass} \$entity) : void
                {
                    // TODO: Implement
                }
            
                /**
                 * Delete an entity.
                 */
                public function delete({$entityClass} \$entity) : void
                {
                    // TODO: Implement
                }
            
                /**
                 * Find entities by criteria.
                 *
                 * @return {$entityClass}[]
                 */
                public function findBy(array \$criteria) : array
                {
                    // TODO: Implement
                    return [];
                }
            
                /**
                 * Find one entity by criteria.
                 */
                public function findOneBy(array \$criteria): ?{$entityClass}
                {
                    // TODO: Implement
                    return null;
                }
            }
            PHP;
    }
}
