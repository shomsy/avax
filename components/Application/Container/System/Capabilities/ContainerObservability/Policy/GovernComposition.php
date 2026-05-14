<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\ContainerObservability\Policy;

use Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings\DependencyRegistry;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Blueprints\CreateDependencyBlueprint;
use Avax\Components\Application\Container\System\Capabilities\ResolutionPolicy;
use ReflectionException;

/**
 * Runs the policy lane as a separate governance stage over the authored graph.
 */
final readonly class GovernComposition
{
    /**
     * @param  array<string, list<string>>  $graph
     * @param  array<string, list<string>>  $dependents
     * @return array{
     *     schemaVersion: int,
     *     stage: string,
     *     profile: string,
     *     failMode: string,
     *     blocked: bool,
     *     summary: array{error: int, warn: int},
     *     findings: array<string, list<array{code: string, severity: string, category: string, message: string}>>
     * }
     *
     * @throws ReflectionException
     */
    public function report(
        array $graph,
        array $dependents,
        DependencyRegistry $dependencyRegistry,
        CreateDependencyBlueprint $createDependencyBlueprint,
        ResolutionPolicy $resolutionPolicy,
        string $environment = '',
    ): array {
        $activePolicy = $resolutionPolicy->forEnvironment(environment: $environment);
        $findings = new CheckCompositionPolicies()->check(
            graph        : $graph,
            dependents   : $dependents,
            registrations: $dependencyRegistry,
            blueprints   : $createDependencyBlueprint,
            policy       : $activePolicy,
        );

        $summary = [
            'error' => 0,
            'warn' => 0,
        ];
        $blocked = false;

        foreach ($findings as $finding) {
            foreach ($finding as $serviceFinding) {
                $severity = strtolower(string: trim(string: (string) ($serviceFinding['severity'] ?? 'warn')));
                if (! isset($summary[$severity])) {
                    continue;
                }

                $summary[$severity]++;
                if ($activePolicy->shouldFailOn(severity: $severity)) {
                    $blocked = true;
                }
            }
        }

        return [
            'schemaVersion' => 1,
            'stage' => 'policy-governance',
            'profile' => $activePolicy->profile,
            'failMode' => $activePolicy->failMode,
            'blocked' => $blocked,
            'summary' => $summary,
            'findings' => $findings,
        ];
    }

    /**
     * @param  array<string, mixed>  $report
     * @return list<string>
     */
    public function messages(array $report): array
    {
        $messages = [];

        foreach ($report['findings'] ?? [] as $serviceId => $findings) {
            foreach ($findings as $finding) {
                $messages[] = strtoupper(string: (string) ($finding['severity'] ?? 'warn'))
                    .' '
                    .($finding['code'] ?? 'POLICY')
                    .' ['
                    .$serviceId
                    .']: '
                    .($finding['message'] ?? 'policy finding');
            }
        }

        sort(array: $messages);

        return $messages;
    }
}
