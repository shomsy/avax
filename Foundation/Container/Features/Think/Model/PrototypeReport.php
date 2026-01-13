<?php

declare(strict_types=1);

namespace Avax\Container\Features\Think\Model;

/**
 * The "Audit Tool" for inspecting and visualizing class blueprints.
 *
 * PrototypeReport is a diagnostic utility that converts complex, multi-level
 * {@see ServicePrototype} objects into human-readable data maps. It is
 * used primarily by the CLI (e.g. `avax container:inspect`) to help
 * developers understand the "Dependency Graph" of their application and
 * identify potential issues like high complexity or excessive injection
 * points.
 *
 * @see     docs/Features/Think/Model/PrototypeReport.md
 * @see     ServicePrototype For the source data being analyzed.
 */
class PrototypeReport
{
    /** @var array<string, mixed> Internal cache for calculated analysis results. */
    private array $analysisCache = [];

    /**
     * Generate an aggregate report for a collection of blueprints.
     *
     * @param iterable<ServicePrototype> $prototypes The blueprints to audit.
     *
     * @return array<string, mixed> A structured map containing global statistics and individual reports.
     *
     * @see docs/Features/Think/Model/PrototypeReport.md#method-generatebulkreport
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
                'generated_at'     => date(format: 'c'),
                'version'          => '1.0',
                'total_prototypes' => $stats['total_prototypes'],
            ],
            'statistics' => $stats,
            'prototypes' => $reports,
        ];
    }

    /**
     * Generate a deep-dive report for one specific blueprint.
     *
     * @param ServicePrototype $prototype The blueprint to audit.
     *
     * @return array<string, mixed> Detailed metadata, dependency lists, and complexity metrics.
     *
     * @see docs/Features/Think/Model/PrototypeReport.md#method-generateforprototype
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
                'generated_at' => date(format: 'c'),
                'version'      => '1.0',
            ],
        ];
    }

    /**
     * Decomposes a method blueprint into reporting-friendly primitives.
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
     * Decomposes property blueprints into reporting-friendly primitives.
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
     * Decomposes multiple method blueprints.
     */
    private function analyzeMethods(array $methods) : array
    {
        return array_map(callback: [$this, 'analyzeMethod'], array: $methods);
    }

    /**
     * Scans the entire prototype tree to extract unique dependency class names.
     */
    private function extractDependencies(ServicePrototype $prototype) : array
    {
        $dependencies = [];

        if ($prototype->constructor) {
            foreach ($prototype->constructor->parameters as $param) {
                if ($param->type) {
                    $dependencies[] = $param->type;
                }
            }
        }

        foreach ($prototype->injectedProperties as $property) {
            if ($property->type) {
                $dependencies[] = $property->type;
            }
        }

        foreach ($prototype->injectedMethods as $method) {
            foreach ($method->parameters as $param) {
                if ($param->type) {
                    $dependencies[] = $param->type;
                }
            }
        }

        return array_values(array_unique($dependencies));
    }

    /**
     * Heuristic calculator for "Dependency Complexity".
     */
    private function calculateComplexity(ServicePrototype $prototype) : array
    {
        $constructorParams = $prototype->constructor ? count($prototype->constructor->parameters) : 0;
        $propertyCount     = count($prototype->injectedProperties);
        $methodCount       = count($prototype->injectedMethods);

        $totalInjectionPoints = $constructorParams + $propertyCount + $methodCount;

        $complexity = 'simple';
        if ($totalInjectionPoints > 10) {
            $complexity = 'complex';
        } elseif ($totalInjectionPoints > 5) {
            $complexity = 'moderate';
        }

        return [
            'total_injection_points' => $totalInjectionPoints,
            'complexity_level'       => $complexity,
            'breakdown'              => [
                'constructor' => $constructorParams,
                'properties'  => $propertyCount,
                'methods'     => $methodCount,
            ],
        ];
    }

    /**
     * Export a report as a pretty JSON string.
     *
     * @param array<string, mixed> $report The report data.
     *
     * @see docs/Features/Think/Model/PrototypeReport.md#method-tojson
     */
    public function toJson(array $report) : string
    {
        return json_encode(value: $report, flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Export a human-readable text summary of a report.
     *
     * @param array<string, mixed> $report The report data.
     *
     * @see docs/Features/Think/Model/PrototypeReport.md#method-tosummary
     */
    public function toSummary(array $report) : string
    {
        if (isset($report['summary'])) {
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
        }

        return sprintf(
            "Prototype: %s\n" .
            "Instantiable: %s\n" .
            "Unique Dependencies: %d\n" .
            "Complexity: %s (%d points)\n",
            $report['class'],
            $report['instantiable'] ? 'Yes' : 'No',
            count($report['dependencies']),
            $report['complexity']['complexity_level'],
            $report['complexity']['total_injection_points']
        );
    }
}
