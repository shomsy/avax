<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\CodeGeneration\System\Capabilities\Generators;

use Avax\Components\Application\Text\System\Capabilities\CaseConversion\Str;
use Override;

/**
 * Generates capability/action classes.
 */
class CapabilityGenerator extends CodeGenerator
{
    /**
     * Generate a capability class file.
     *
     * @param string $name Capability name (e.g. "RegisterUser" or "SendWelcomeMail")
     * @param array  $data Additional data (e.g. ['methods' => ['create', 'update']])
     *
     * @return string The generated file path
     */
    #[Override]
    public function generate(string $name, array $data = []) : string
    {
        $className = Str::studly($name);

        if (! str_ends_with($className, 'Capability')) {
            $className .= 'Capability';
        }

        $subDir    = $data['subDir'] ?? 'Capabilities';
        $namespace = $this->getNamespace($subDir);
        $methods   = $data['methods'] ?? [];

        $stub = $this->buildStub($className, $namespace, $methods);
        $path = $this->getFilePath($className, $subDir);

        $this->writeFile($path, $stub);

        return $path;
    }

    /**
     * Build the capability class stub.
     */
    protected function buildStub(string $className, string $namespace, array $methods) : string
    {
        $methodsCode = '';

        if ($methods === []) {
            $methods = ['execute'];
        }

        foreach ($methods as $method) {
            $methodsCode .= $this->generateMethod($method);
        }

        return <<<PHP
            <?php
            
            declare(strict_types=1);
            
            namespace {$namespace};
            
            use LogicException;
            
            class {$className}
            {
            {$methodsCode}
            }
            PHP;
    }

    /**
     * Generate a single capability method.
     */
    protected function generateMethod(string $method) : string
    {
        $methodName = Str::camel($method);

        return "\n    public function {$methodName}() : void\n    {\n        throw new LogicException('Define {$methodName} behavior before wiring {$methodName} into production.');\n    }\n";
    }
}
