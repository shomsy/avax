<?php

declare(strict_types=1);

namespace Avax\Container\Observe\Inspect;

use Avax\Container\Features\Think\Model\MethodPrototype;
use Avax\Container\Features\Think\Model\ParameterPrototype;
use Avax\Container\Features\Think\Model\PropertyPrototype;
use Avax\Container\Features\Think\Model\ServicePrototype;
use Avax\Container\Features\Think\Prototype\CompiledPrototypeDumper;

/**
 * @see     ServicePrototype The data structure being formatted
 * @see     InspectCommand CLI command that uses this dumper
 * @see     CompiledPrototypeDumper Alternative dumper for compiled formats
 * @see     docs/Observe/Inspect/CliPrototypeDumper.md#quick-summary
 */
final readonly class CliPrototypeDumper
{
    /**
     * @param ServicePrototype $prototype Prototype to render for CLI output
     *
     * @return string Human-friendly, terminal-ready representation
     *
     * @see docs/Observe/Inspect/CliPrototypeDumper.md#method-dump
     */
    public function dump(ServicePrototype $prototype) : string
    {
        $lines   = [];
        $lines[] = "\n[ServicePrototype] {$prototype->class}";
        $lines[] = 'Instantiable: ' . ($prototype->isInstantiable ? 'Yes' : 'No');

        if ($prototype->constructor instanceof MethodPrototype) {
            $lines[] = 'Constructor: ' . $this->formatMethod(method: $prototype->constructor);
        } else {
            $lines[] = 'Constructor: (none)';
        }

        if ($prototype->injectedProperties !== []) {
            $lines[] = 'Properties:';
            foreach ($prototype->injectedProperties as $property) {
                $lines[] = "  - {$this->formatProperty(property: $property)}";
            }
        } else {
            $lines[] = 'Properties: (none)';
        }

        if ($prototype->injectedMethods !== []) {
            $lines[] = 'Methods:';
            foreach ($prototype->injectedMethods as $method) {
                $lines[] = "  - {$this->formatMethod(method: $method)}";
            }
        } else {
            $lines[] = 'Methods: (none)';
        }

        return implode("\n", $lines) . "\n";
    }

    private function formatMethod(MethodPrototype $method) : string
    {
        $params = array_map(
            fn(ParameterPrototype $param) : string => $this->formatParameter(parameter: $param),
            $method->parameters
        );

        return $method->name . '(' . implode(', ', $params) . ')';
    }

    private function formatParameter(ParameterPrototype $parameter) : string
    {
        $type   = $parameter->type ? $parameter->type . ' ' : '';
        $name   = '$' . $parameter->name;
        $suffix = $parameter->hasDefault ? ' = ' . var_export($parameter->default, true) : '';

        return $type . $name . $suffix;
    }

    private function formatProperty(PropertyPrototype $property) : string
    {
        $type = $property->type ? $property->type . ' ' : '';

        return $type . '$' . $property->name;
    }
}
