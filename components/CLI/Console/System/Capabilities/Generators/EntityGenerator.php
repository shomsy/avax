<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Console\System\Capabilities\Generators;

use Avax\Components\Application\Text\System\Capabilities\CaseConversion\Str;
use Override;

/**
 * Generates entity class stubs.
 */
class EntityGenerator extends CodeGenerator
{
    /**
     * Generate an entity class file.
     *
     * @param string $name Entity name or table name (e.g. "User" or "users")
     * @param array $data Additional data (e.g. ['fields' => [['name' => 'id', 'type' => 'int'], ...]])
     *
     * @return string The generated file path
     */
    #[Override]
    public function generate(string $name, array $data = []) : string
    {
        $className = Str::studly($name);

        // Remove "Entity" suffix if present, then add it back
        $className = ! in_array(preg_replace('/Entity$/', '', $className), ['', '0'], true) && preg_replace('/Entity$/', '', $className) !== [] ? preg_replace('/Entity$/', '', $className) : $className;
        $className .= 'Entity';

        $subDir    = $data['subDir'] ?? 'Entities';
        $namespace = $this->getNamespace($subDir);
        $tableName = $data['table'] ?? Str::snake($name) . 's';
        $fields    = $data['fields'] ?? [];

        $stub = $this->buildStub($className, $namespace, $tableName, $fields);
        $path = $this->getFilePath($className, $subDir);

        $this->writeFile($path, $stub);

        return $path;
    }

    /**
     * Build the entity class stub.
     */
    protected function buildStub(string $className, string $namespace, string $tableName, array $fields) : string
    {
        if ($fields === []) {
            $fields = [
                ['name' => 'id', 'type' => 'int'],
                ['name' => 'createdAt', 'type' => 'DateTimeImmutable'],
                ['name' => 'updatedAt', 'type' => 'DateTimeImmutable'],
            ];
        }

        $properties = '';
        $getters    = '';
        $setters    = '';

        foreach ($fields as $field) {
            $fieldName = Str::camel($field['name']);
            $fieldType = $this->mapType($field['type'] ?? 'string');

            $properties .= "    private {$fieldType} \${$fieldName};\n";
            $getters    .= $this->generateGetter($fieldName, $fieldType);
            $setters    .= $this->generateSetter($fieldName, $fieldType);
        }

        return <<<PHP
            <?php
            
            declare(strict_types=1);
            
            namespace {$namespace};
            
            /**
             * Entity class for table '{$tableName}'.
             */
            class {$className}
            {
            {$properties}
            {$getters}
            {$setters}
            }
            PHP;
    }

    /**
     * Map database types to PHP types.
     */
    protected function mapType(string $type) : string
    {
        return match (strtolower($type)) {
            'int', 'integer', 'bigint', 'smallint', 'tinyint' => 'int',
            'float', 'decimal', 'double', 'real'              => 'float',
            'bool', 'boolean'                                 => 'bool',
            'datetime', 'datetimeimmutable', 'timestamp'      => '\\DateTimeImmutable',
            'json'                                            => 'array',
            default                                           => 'string',
        };
    }

    /**
     * Generate a getter method.
     */
    protected function generateGetter(string $fieldName, string $fieldType) : string
    {
        $methodName = 'get' . Str::studly($fieldName);

        return "\n    public function {$methodName}(): {$fieldType}\n    {\n        return \$this->{$fieldName};\n    }\n";
    }

    /**
     * Generate a setter method.
     */
    protected function generateSetter(string $fieldName, string $fieldType) : string
    {
        $methodName = 'set' . Str::studly($fieldName);

        return "\n    public function {$methodName}({$fieldType} \${$fieldName}) : self\n    {\n        \$this->{$fieldName} = \${$fieldName};\n\n        return \$this;\n    }\n";
    }
}
