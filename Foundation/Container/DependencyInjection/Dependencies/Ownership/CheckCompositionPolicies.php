<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Dependencies\Ownership;

use Avax\Container\DependencyInjection\Dependencies\Bindings\ServiceRegistry;
use Avax\Container\DependencyInjection\Dependencies\Blueprints\CreateServiceBlueprint;

/**
 * Evaluates structural composition policies and returns machine-readable findings.
 */
final readonly class CheckCompositionPolicies
{
    /**
     * @param array<string, list<string>> $graph
     * @param array<string, list<string>> $dependents
     * @return array<string, list<array{code: string, severity: string, category: string, message: string}>>
     */
    public function check(
        array $graph,
        array $dependents,
        ServiceRegistry $registrations,
        CreateServiceBlueprint $blueprints
    ) : array {
        $findings = [];

        foreach ($graph as $serviceId => $dependencies) {
            $findings[$serviceId] = [];
            $registration = $registrations->get(abstract: $serviceId);
            $metadata = $registration?->metadata ?? RegistrationMetadata::for(unitId: $serviceId);
            $candidate = $registration?->concrete;

            if ($candidate === null && class_exists($serviceId)) {
                $candidate = $serviceId;
            }

            if (is_string($candidate) && class_exists($candidate)) {
                $blueprint = $blueprints->createFor(class: $candidate);
                $constructorArity = count($blueprint->constructor?->parameters ?? []);
                if ($constructorArity >= 6) {
                    $findings[$serviceId][] = $this->finding(
                        code     : 'POL-001',
                        severity : 'warn',
                        category : 'composition',
                        message  : "constructor arity is {$constructorArity}; this unit may be over-injected"
                    );
                }
            }

            if (
                $metadata->visibility === RegistrationVisibility::SHARED
                && count($dependents[$serviceId] ?? []) <= 1
            ) {
                $findings[$serviceId][] = $this->finding(
                    code     : 'POL-002',
                    severity : 'warn',
                    category : 'ownership',
                    message  : 'shared visibility has one or zero known consumers; this may be premature extraction'
                );
            }

            if ($metadata->category === RegistrationCategory::FOUNDATION && count($dependencies) >= 5) {
                $findings[$serviceId][] = $this->finding(
                    code     : 'POL-003',
                    severity : 'warn',
                    category : 'architecture',
                    message  : 'foundation unit depends on many services; check for overgrown foundation'
                );
            }

            foreach ($dependencies as $dependency) {
                $dependencyMetadata = $registrations->ownership(abstract: $dependency)
                    ?? RegistrationMetadata::for(unitId: $dependency);

                if (
                    $metadata->category === RegistrationCategory::FLOW
                    && $dependencyMetadata->category === RegistrationCategory::FLOW
                    && $metadata->ownerSlice !== $dependencyMetadata->ownerSlice
                ) {
                    $findings[$serviceId][] = $this->finding(
                        code     : 'POL-004',
                        severity : 'error',
                        category : 'architecture',
                        message  : "flow slice [{$metadata->ownerSlice}] depends directly on flow slice [{$dependencyMetadata->ownerSlice}]"
                    );
                }
            }

            if (in_array($metadata->concept, ['service', 'manager', 'helper', 'util', 'common', 'misc', 'core', 'base', 'shared'], true)) {
                $findings[$serviceId][] = $this->finding(
                    code     : 'POL-005',
                    severity : 'warn',
                    category : 'naming',
                    message  : "concept name [{$metadata->concept}] is too generic for ownership-aware diagnostics"
                );
            }

            usort(
                $findings[$serviceId],
                static fn(array $left, array $right) : int => [$left['severity'], $left['code']]
                    <=> [$right['severity'], $right['code']]
            );
        }

        ksort($findings);

        return $findings;
    }

    /**
     * @return array{code: string, severity: string, category: string, message: string}
     */
    private function finding(string $code, string $severity, string $category, string $message) : array
    {
        return [
            'code' => $code,
            'severity' => $severity,
            'category' => $category,
            'message' => $message,
        ];
    }
}
