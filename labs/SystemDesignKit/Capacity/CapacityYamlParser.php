<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\Capacity;

/**
 * V3-00: Capacity.yaml parser spike.
 *
 * Status: @experimental
 * Purpose: prove that capacity.yaml files can be parsed
 * and validated against the capacity model schema.
 *
 * This is a spike in labs/, not production code.
 * It will be refined before promotion to components/SystemDesign.
 */
final readonly class CapacityYamlParser
{
    /**
     * Parse a capacity.yaml (or capacity.php config array) into a structured model.
     *
     * @param array<string, mixed> $config
     *
     * @return array{system: string, traffic: array<string, mixed>, storage: array<string, mixed>, cache: array<string, mixed>, queue: array<string, mixed>, latency: array<string, mixed>, availability: array<string, mixed>}
     */
    public function parse(array $config) : array
    {
        return [
            'system'       => $config['system'] ?? 'unknown',
            'traffic'      => $config['traffic'] ?? [],
            'storage'      => $config['storage'] ?? [],
            'cache'        => $config['cache'] ?? [],
            'queue'        => $config['queue'] ?? [],
            'latency'      => $config['latency'] ?? [],
            'availability' => $config['availability'] ?? [],
        ];
    }

    /**
     * Validate that required sections exist.
     *
     * @param array<string, mixed> $parsed
     *
     * @return list<string>
     */
    public function validateSections(array $parsed) : array
    {
        $missing  = [];
        $required = ['system', 'traffic', 'storage', 'cache', 'queue', 'latency', 'availability'];

        foreach ($required as $section) {
            if (! isset($parsed[$section]) || $parsed[$section] === []) {
                $missing[] = "Missing required section: {$section}";
            }
        }

        return $missing;
    }
}
