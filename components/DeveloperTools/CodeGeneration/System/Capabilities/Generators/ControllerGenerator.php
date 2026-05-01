<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\CodeGeneration\System\Capabilities\Generators;

use Avax\Components\Application\Text\System\Capabilities\CaseConversion\Str;
use Override;

/**
 * Generates controller class stubs.
 */
class ControllerGenerator extends CodeGenerator
{
    /**
     * Generate a controller class file.
     *
     * @param string $name Controller name (e.g. "UserController" or "User")
     * @param array  $data Additional data (e.g. ['methods' => ['index', 'show']])
     *
     * @return string The generated file path
     */
    #[Override]
    public function generate(string $name, array $data = []): string
    {
        // Normalize name - ensure it ends with "Controller"
        $className = Str::studly($name);

        if (! str_ends_with($className, 'Controller')) {
            $className .= 'Controller';
        }

        $subDir    = $data['subDir'] ?? 'Controllers';
        $namespace = $this->getNamespace($subDir);
        $methods   = $data['methods'] ?? [];

        $stub = $this->buildStub($className, $namespace, $methods);
        $path = $this->getFilePath($className, $subDir);

        $this->writeFile($path, $stub);

        return $path;
    }

    /**
     * Build the controller class stub.
     */
    protected function buildStub(string $className, string $namespace, array $methods): string
    {
        $methodsCode = '';

        if ($methods === []) {
            $methods = ['index'];
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
     * Generate a single controller method.
     */
    protected function generateMethod(string $method): string
    {
        $methodName = Str::camel($method);

        return "    public function {$methodName}() : void\n    {\n        // TODO: Implement {$methodName}\n    }\n";
    }
}
