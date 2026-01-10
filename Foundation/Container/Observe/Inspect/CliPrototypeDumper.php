<?php

declare(strict_types=1);
namespace Avax\Container\Observe\Inspect;

use Avax\Container\Features\Think\Model\MethodPrototype;
use Avax\Container\Features\Think\Model\ParameterPrototype;
use Avax\Container\Features\Think\Model\PropertyPrototype;
use Avax\Container\Features\Think\Model\ServicePrototype;
use Avax\Container\Features\Think\Prototype\CompiledPrototypeDumper;

/**
 * @package Avax\Container\Observe\Inspect
 *
 * CLI-friendly formatter for ServicePrototype inspection and debugging.
 *
 * CliPrototypeDumper provides human-readable, terminal-optimized output for
 * ServicePrototype instances. It formats complex prototype structures into
 * clear, hierarchical text representations suitable for command-line interfaces,
 * debugging, and development workflows.
 *
 * WHY IT EXISTS:
 * - To enable developers to inspect ServicePrototype structures during development
 * - To provide debugging output for prototype generation and validation
 * - To support CLI tools that need to display prototype information
 * - To offer human-readable representations of complex dependency injection plans
 *
 * OUTPUT FORMAT:
 * The dumper produces structured, indented text output showing:
 * - Service class name and instantiability status
 * - Constructor signature with parameter types and defaults
 * - Injected properties with their types
 * - Injected methods with full signatures
 *
 * USAGE SCENARIOS:
 * - CLI inspection commands (`container:inspect`)
 * - Development debugging and troubleshooting
 * - Prototype validation and verification
 * - Documentation generation from live prototypes
 *
 * FORMATTING RULES:
 * - Class names displayed prominently with [ServicePrototype] prefix
 * - Instantiability clearly indicated (Yes/No)
 * - Constructor shown with full parameter signature
 * - Properties and methods listed hierarchically with indentation
 * - Type information included where available
 * - Default values shown for optional parameters
 *
 * PERFORMANCE CHARACTERISTICS:
 * - Lightweight string formatting operations
 * - Minimal memory overhead for output generation
 * - Fast execution for typical prototype sizes
 * - No external dependencies or I/O operations
 *
 * THREAD SAFETY:
 * - Stateless pure functions, safe for concurrent use
 * - No mutable state or shared resources
 * - Immutable input objects guarantee consistent output
 *
 * ERROR HANDLING:
 * - Assumes valid ServicePrototype input (validation should occur upstream)
 * - Gracefully handles missing or empty prototype sections
 * - No exceptions thrown during formatting operations
 *
 * INTEGRATION POINTS:
 * - Used by CLI commands for prototype display
 * - Called by diagnostic tools during development
 * - Supports debugging workflows in IDEs and terminals
 * - Can be extended for different output formats (JSON, HTML)
 *
 * EXTENSIBILITY:
 * - Additional formatParameter/formatProperty methods for custom formatting
 * - Support for different output styles (compact, verbose, colored)
 * - Integration with external formatters and renderers
 *
 * LIMITATIONS:
 * - Terminal/console output only (no rich formatting)
 * - English-only output (no internationalization)
 * - Text-based output (no graphical representations)
 *
 * @see     ServicePrototype The data structure being formatted
 * @see     InspectCommand CLI command that uses this dumper
 * @see     CompiledPrototypeDumper Alternative dumper for compiled formats
 * @see docs/Observe/Inspect/CliPrototypeDumper.md#quick-summary
 */
final readonly class CliPrototypeDumper
{
    /**
     * @param ServicePrototype $prototype Prototype to render for CLI output
     *
     * @return string Human-friendly, terminal-ready representation
     * @see docs/Observe/Inspect/CliPrototypeDumper.md#method-dump
     */
    public function dump(ServicePrototype $prototype) : string
    {
        $lines   = [];
        $lines[] = "\n[ServicePrototype] {$prototype->class}";
        $lines[] = "Instantiable: " . ($prototype->isInstantiable ? 'Yes' : 'No');

        if ($prototype->constructor instanceof MethodPrototype) {
            $lines[] = "Constructor: " . $this->formatMethod(method: $prototype->constructor);
        } else {
            $lines[] = "Constructor: (none)";
        }

        if ($prototype->injectedProperties !== []) {
            $lines[] = "Properties:";
            foreach ($prototype->injectedProperties as $property) {
                $lines[] = "  - {$this->formatProperty(property:$property)}";
            }
        } else {
            $lines[] = "Properties: (none)";
        }

        if ($prototype->injectedMethods !== []) {
            $lines[] = "Methods:";
            foreach ($prototype->injectedMethods as $method) {
                $lines[] = "  - {$this->formatMethod(method:$method)}";
            }
        } else {
            $lines[] = "Methods: (none)";
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
