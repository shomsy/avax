<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Console\System\Capabilities\Generators;

use Avax\Components\Application\Text\System\Capabilities\CaseConversion\Str;

/**
 * Generates service class stubs.
 */
class ServiceGenerator extends CodeGenerator
{
    /**
     * Generate a service class file.
     *
     * @param string $name Service name (e.g. "UserService" or "User")
     * @param array  $data Additional data (e.g. ['methods' => ['create', 'update']])
     *
     * @return string The generated file path
     */
    public function generate(string $name, array $data = []) : string
    {
        $className = Str::studly($name);

        // Ensure it ends with "Service"
        if (! str_ends_with($className, 'Service')) {
            $className .= 'Service';
        }

        $subDir    = $data['subDir'] ?? 'Services';
        $namespace = $this->getNamespace($subDir);
        $methods   = $data['methods'] ?? [];

        $stub = $this->buildStub($className, $namespace, $methods);
        $path = $this->getFilePath($className, $subDir);

        $this->writeFile($path, $stub);

        return $path;
    }

    /**
     * Build the service class stub.
     */
    protected function buildStub(string $className, string $namespace, array $methods) : string
    {
        $methodsCode = '';

        if (empty($methods)) {
            $methods = ['create', 'update', 'delete', 'find'];
        }

        foreach ($methods as $method) {
            $methodsCode .= $this->generateMethod($method);
        }

        return <<<PHP
            <?php
            
            declare(strict_types=1);
            
            namespace {$namespace};
            
            class {$className}
            {
            {$methodsCode}
            }
            PHP;
    }

    /**
     * Generate a single service method.
     */
    protected function generateMethod(string $method) : string
    {
        $methodName = Str::camel($method);

        return match ($methodName) {
            'create' => "\n    public function create(array \$data): mixed\n    {\n        // TODO: Implement create\n    }\n",
            'update' => "\n    public function update(int \$id, array \$data): mixed\n    {\n        // TODO: Implement update\n    }\n",
            'delete' => "\n    public function delete(int \$id): void\n    {\n        // TODO: Implement delete\n    }\n",
            'find'   => "\n    public function find(int \$id): mixed\n    {\n        // TODO: Implement find\n    }\n",
            default  => "\n    public function {$methodName}(): void\n    {\n        // TODO: Implement {$methodName}\n    }\n",
        };
    }
}
