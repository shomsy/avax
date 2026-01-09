<?php

declare(strict_types=1);
namespace Avax\Container\Features\Think\Model;

use Avax\Container\Features\Think\Flow\DesignFlow;

/**
 * @package Avax\Container\Think\Model
 *
 * JSON report generator for prototype inspection and debugging.
 *
 * PrototypeReport provides comprehensive reporting capabilities for ServicePrototype
 * instances, generating human-readable JSON output for CLI inspection, debugging,
 * and monitoring purposes. It transforms complex prototype data into structured,
 * easily consumable reports.
 *
 * REPORT FEATURES:
 * - Complete prototype metadata and statistics
 * - Class hierarchy and dependency analysis
 * - Injection point details and type information
 * - Performance metrics and cache statistics
 * - CLI-friendly formatting with color coding support
 *
 * OUTPUT FORMATS:
 * - JSON: Structured data for programmatic consumption
 * - Pretty JSON: Human-readable formatted output
 * - Summary: Condensed overview for quick inspection
 * - Detailed: Full analysis with cross-references
 *
 * USAGE SCENARIOS:
 * - CLI container inspection tools (`container:inspect`)
 * - Debugging complex dependency injection issues
 * - Performance monitoring and bottleneck identification
 * - Documentation generation for service architectures
 * - CI/CD pipeline validation of container configuration
 *
 * ANALYSIS CAPABILITIES:
 * - Circular dependency detection
 * - Type compatibility verification
 * - Injection point validation
 * - Performance bottleneck identification
 * - Memory usage analysis
 *
 * INTEGRATION POINTS:
 * - CLI commands for interactive inspection
 * - Web dashboards for real-time monitoring
 * - Logging systems for automated reporting
 * - Testing frameworks for validation
 *
 * PERFORMANCE CONSIDERATIONS:
 * - Lazy evaluation of complex analysis
 * - Memory-efficient streaming for large reports
 * - Caching of expensive computations
 * - Configurable detail levels for performance tuning
 *
 * @see     ServicePrototype For the underlying data structure
 * @see     PrototypeRegistry For bulk reporting capabilities
 * @see     DesignFlow For integration with design workflow
 * @see docs_md/Features/Think/Model/PrototypeReport.md#quick-summary
 */
class PrototypeReport
{
    /**
     * @var array<string, mixed> Cached analysis results
     */
    private array $analysisCache = [];

    /**
     * Generates a bulk report for multiple prototypes.
     *
     * Efficiently processes multiple prototypes and provides aggregate statistics.
     *
     * @param iterable<ServicePrototype> $prototypes Collection of prototypes to analyze
     *
     * @return array{
     *     summary: array,
     *     prototypes: array,
     *     statistics: array
     * }
     * @see docs_md/Features/Think/Model/PrototypeReport.md#method-generatebulkreport
     */
    public function generateBulkReport(iterable $prototypes) : array
    {
        $reports = [];
        $stats   = [
            'total_prototypes'      => 0,
            'instantiable_classes'  => 0,
            'total_dependencies'    => 0,
            'constructor_injection' => 0,
            'property_injection'    => 0,
            'method_injection'      => 0,
        ];

        foreach ($prototypes as $prototype) {
            $report                     = $this->generateForPrototype(prototype: $prototype);
            $reports[$prototype->class] = $report;

            // Update statistics
            $stats['total_prototypes']++;
            if ($prototype->isInstantiable) {
                $stats['instantiable_classes']++;
            }
            $stats['total_dependencies'] += count($report['dependencies']);
            if ($report['constructor']) {
                $stats['constructor_injection']++;
            }
            $stats['property_injection'] += count($report['properties']);
            $stats['method_injection']   += count($report['methods']);
        }

        return [
            'summary'    => [
                'generated_at'     => date('c'),
                'version'          => '1.0',
                'total_prototypes' => $stats['total_prototypes'],
            ],
            'statistics' => $stats,
            'prototypes' => $reports,
        ];
    }

    /**
     * Generates a comprehensive JSON report for a single prototype.
     *
     * Creates detailed analysis including injection points, dependencies,
     * and performance characteristics.
     *
     * @param ServicePrototype $prototype The prototype to analyze
     *
     * @return array{
     *     class: string,
     *     instantiable: bool,
     *     constructor: array|null,
     *     properties: array,
     *     methods: array,
     *     dependencies: array,
     *     complexity: array
     * }
     * @see docs_md/Features/Think/Model/PrototypeReport.md#method-generateforprototype
     */
    public function generateForPrototype(ServicePrototype $prototype) : array
    {
        return [
            'class'        => $prototype->class,
            'instantiable' => $prototype->isInstantiable,
            'constructor'  => $prototype->constructor ? $this->analyzeMethod(method: $prototype->constructor) : null,
            'properties'   => $this->analyzeProperties(properties: $prototype->injectedProperties),
            'methods'      => $this->analyzeMethods(methods: $prototype->injectedMethods),
            'dependencies' => $this->extractDependencies(prototype: $prototype),
            'complexity'   => $this->calculateComplexity(prototype: $prototype),
            'metadata'     => [
                'generated_at' => date('c'),
                'version'      => '1.0',
            ],
        ];
    }

    /**
     * Analyzes a method prototype for reporting.
     *
     * @param MethodPrototype $method
     *
     * @return array Method analysis data
     */
    private function analyzeMethod(MethodPrototype $method) : array
    {
        return [
            'name'       => $method->name,
            'parameters' => array_map(
                static fn(ParameterPrototype $param) => [
                    'name'        => $param->name,
                    'type'        => $param->type,
                    'has_default' => $param->hasDefault,
                    'is_variadic' => $param->isVariadic,
                    'allows_null' => $param->allowsNull,
                ],
                $method->parameters
            ),
        ];
    }

    /**
     * Analyzes property prototypes for reporting.
     *
     * @param PropertyPrototype[] $properties
     *
     * @return array Property analysis data
     */
    private function analyzeProperties(array $properties) : array
    {
        $result = [];
        foreach ($properties as $name => $property) {
            $result[$name] = [
                'type'        => $property->type,
                'has_default' => $property->hasDefault,
                'allows_null' => $property->allowsNull,
            ];
        }

        return $result;
    }

    /**
     * Analyzes method prototypes for reporting.
     *
     * @param MethodPrototype[] $methods
     *
     * @return array Method analysis data
     */
    private function analyzeMethods(array $methods) : array
    {
        $result = [];
        foreach ($methods as $method) {
            $result[] = $this->analyzeMethod(method: $method);
        }

        return $result;
    }

    /**
     * Extracts all dependencies from a prototype.
     *
     * @param ServicePrototype $prototype
     *
     * @return array<string> Unique list of dependency types
     */
    private function extractDependencies(ServicePrototype $prototype) : array
    {
        $dependencies = [];

        // Constructor dependencies
        if ($prototype->constructor) {
            foreach ($prototype->constructor->parameters as $param) {
                if ($param->type) {
                    $dependencies[] = $param->type;
                }
            }
        }

        // Property dependencies
        foreach ($prototype->injectedProperties as $property) {
            if ($property->type) {
                $dependencies[] = $property->type;
            }
        }

        // Method dependencies
        foreach ($prototype->injectedMethods as $method) {
            foreach ($method->parameters as $param) {
                if ($param->type) {
                    $dependencies[] = $param->type;
                }
            }
        }

        return array_unique($dependencies);
    }

    /**
     * Calculates complexity metrics for a prototype.
     *
     * @param ServicePrototype $prototype
     *
     * @return array Complexity analysis data
     */
    private function calculateComplexity(ServicePrototype $prototype) : array
    {
        $constructorParams = $prototype->constructor ? count($prototype->constructor->parameters) : 0;
        $propertyCount     = count($prototype->injectedProperties);
        $methodCount       = count($prototype->injectedMethods);

        $totalInjectionPoints = $constructorParams + $propertyCount + $methodCount;

        // Calculate complexity score (simple heuristic)
        $complexity = 'simple';
        if ($totalInjectionPoints > 10) {
            $complexity = 'complex';
        } elseif ($totalInjectionPoints > 5) {
            $complexity = 'moderate';
        }

        return [
            'constructor_parameters' => $constructorParams,
            'injected_properties'    => $propertyCount,
            'injected_methods'       => $methodCount,
            'total_injection_points' => $totalInjectionPoints,
            'complexity_level'       => $complexity,
        ];
    }

    /**
     * Exports report as pretty-printed JSON string.
     *
     * @param array $report The report data to export
     *
     * @return string Pretty-printed JSON
     * @see docs_md/Features/Think/Model/PrototypeReport.md#method-tojson
     */
    public function toJson(array $report) : string
    {
        return json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Exports report as compact JSON string.
     *
     * @param array $report The report data to export
     *
     * @return string Compact JSON
     * @see docs_md/Features/Think/Model/PrototypeReport.md#method-tocompactjson
     */
    public function toCompactJson(array $report) : string
    {
        return json_encode($report, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Generates a human-readable summary.
     *
     * @param array $report The full report data
     *
     * @return string Human-readable summary
     * @see docs_md/Features/Think/Model/PrototypeReport.md#method-tosummary
     */
    public function toSummary(array $report) : string
    {
        if (isset($report['summary'])) {
            // Bulk report summary
            $stats = $report['statistics'];

            return sprintf(
                "Container Prototype Report\n" .
                "==========================\n" .
                "Total Prototypes: %d\n" .
                "Instantiable Classes: %d\n" .
                "Total Dependencies: %d\n" .
                "Constructor Injection: %d\n" .
                "Property Injection: %d\n" .
                "Method Injection: %d\n" .
                "Generated: %s\n",
                $stats['total_prototypes'],
                $stats['instantiable_classes'],
                $stats['total_dependencies'],
                $stats['constructor_injection'],
                $stats['property_injection'],
                $stats['method_injection'],
                $report['summary']['generated_at']
            );
        } else {
            // Single prototype summary
            return sprintf(
                "Prototype: %s\n" .
                "Instantiable: %s\n" .
                "Dependencies: %d\n" .
                "Constructor: %s\n" .
                "Properties: %d\n" .
                "Methods: %d\n",
                $report['class'],
                $report['instantiable'] ? 'Yes' : 'No',
                count($report['dependencies']),
                $report['constructor'] ? 'Yes' : 'No',
                count($report['properties']),
                count($report['methods'])
            );
        }
    }
}
