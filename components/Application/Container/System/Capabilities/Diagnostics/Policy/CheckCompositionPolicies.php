<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Diagnostics\Policy;

use Avax\Components\Application\Container\System\Capabilities\Composition\ContainerSettings;
use Avax\Components\Application\Container\System\Capabilities\Composition\CreateContainerConfig;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings\ServiceRegistry;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings\ServiceRegistryInterface;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Blueprints\CreateServiceBlueprint;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Ownership\RegistrationCategory;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Ownership\RegistrationMetadata;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Ownership\RegistrationVisibility;
use Avax\Components\Application\Container\System\Capabilities\Resolution\LifetimePlan;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolutionPolicy;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ServiceResolver;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\ResettableInterface;
use Avax\Components\Application\Container\System\Container;
use Avax\Components\Application\Container\System\ContainerInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use ReflectionException;
use SensitiveParameter;

/**
 * Evaluates structural composition policies and returns machine-readable findings.
 */
final readonly class CheckCompositionPolicies
{
    /**
     * @param array<string, list<string>> $graph
     * @param array<string, list<string>> $dependents
     *
     * @return array<string, list<array{code: string, severity: string, category: string, message: string}>>
     * @throws ReflectionException
     */
    public function check(
        array            $graph,
        array            $dependents,
        ServiceRegistry  $registrations,
        CreateServiceBlueprint $blueprints,
        ResolutionPolicy $policy,
    ) : array
    {
        $findings = [];

        foreach ($graph as $serviceId => $dependencies) {
            $findings[$serviceId] = [];
            $registration         = $registrations->get(abstract: $serviceId);
            $metadata             = $registration?->metadata ?? RegistrationMetadata::for(unitId: $serviceId);
            $candidate            = $registration?->concrete;

            if ($candidate === null && class_exists(class: $serviceId)) {
                $candidate = $serviceId;
            }

            if (is_string(value: $candidate) && class_exists(class: $candidate)) {
                $blueprint        = $blueprints->createFor(class: $candidate);
                $constructorArity = count(value: $blueprint->constructor?->parameters ?? []);
                if ($constructorArity >= 6) {
                    $findings[$serviceId][] = $this->finding(
                        policy  : $policy,
                        code    : 'POL-001',
                        severity: 'warn',
                        category: 'composition',
                        message : "constructor arity is {$constructorArity}; this unit may be over-injected",
                    );
                }
            }

            if (
                $metadata->visibility === RegistrationVisibility::SHARED
                && count(value: $dependents[$serviceId] ?? []) <= 1
            ) {
                $findings[$serviceId][] = $this->finding(
                    policy  : $policy,
                    code    : 'POL-002',
                    severity: 'warn',
                    category: 'ownership',
                    message : 'shared visibility has one or zero known consumers; this may be premature extraction',
                );
            }

            if ($metadata->category === RegistrationCategory::FOUNDATION && count(value: $dependencies) >= 5) {
                $findings[$serviceId][] = $this->finding(
                    policy  : $policy,
                    code    : 'POL-003',
                    severity: 'warn',
                    category: 'architecture',
                    message : 'foundation unit depends on many services; check for overgrown foundation',
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
                        policy  : $policy,
                        code    : 'POL-004',
                        severity: 'error',
                        category: 'architecture',
                        message : "flow slice [{$metadata->ownerSlice}] depends directly on flow slice [{$dependencyMetadata->ownerSlice}]",
                    );
                }

                if (in_array(needle: $dependency, haystack: [
                    PsrContainerInterface::class,
                    ContainerInterface::class,
                    Container::class,
                    ServiceResolver::class,
                    ServiceRegistryInterface::class,
                ],           strict: true)) {
                    $findings[$serviceId][] = $this->finding(
                        policy  : $policy,
                        code    : 'POL-008',
                        severity: 'error',
                        category: 'runtime',
                        message : 'service depends on container runtime internals; this is service locator drift',
                    );
                }

                if (in_array(needle: $dependency, haystack: [
                        ContainerSettings::class,
                        CreateContainerConfig::class,
                    ],       strict: true) && $metadata->category !== RegistrationCategory::CONFIGURATION) {
                    $findings[$serviceId][] = $this->finding(
                        policy  : $policy,
                        code    : 'POL-009',
                        severity: 'warn',
                        category: 'runtime',
                        message : 'service depends on raw settings/config globals outside configuration ownership',
                    );
                }
            }

            if (in_array(needle: $metadata->concept, haystack: ['service', 'manager', 'helper', 'util', 'common', 'misc', 'core', 'base', 'shared'], strict: true)) {
                $findings[$serviceId][] = $this->finding(
                    policy  : $policy,
                    code    : 'POL-005',
                    severity: 'warn',
                    category: 'naming',
                    message : "concept name [{$metadata->concept}] is too generic for ownership-aware diagnostics",
                );
            }

            $sliceTail = strtolower(string: (string) basename(path: str_replace(search: '.', replace: '/', subject: $metadata->ownerSlice)));
            if (
                $metadata->category === RegistrationCategory::CAPABILITY
                && in_array(needle: $sliceTail, haystack: ['misc', 'common', 'shared', 'core', 'helpers', 'utils'], strict: true)
            ) {
                $findings[$serviceId][] = $this->finding(
                    policy  : $policy,
                    code    : 'POL-010',
                    severity: 'warn',
                    category: 'architecture',
                    message : "capability slice [{$metadata->ownerSlice}] reads like a generic bucket",
                );
            }

            if (
                $metadata->category === RegistrationCategory::FLOW
                && in_array(needle: $metadata->visibility, haystack: [RegistrationVisibility::PUBLIC, RegistrationVisibility::SHARED], strict: true)
                && $metadata->intent !== 'entry'
            ) {
                $findings[$serviceId][] = $this->finding(
                    policy  : $policy,
                    code    : 'POL-006',
                    severity: 'warn',
                    category: 'ownership',
                    message : 'flow unit uses shared or public visibility without entry intent; this may be an everything-shared-by-default smell',
                );
            }

            if (
                $metadata->ownerSlice === 'default'
                && ($metadata->category !== RegistrationCategory::CONFIGURATION || $metadata->visibility !== RegistrationVisibility::PUBLIC)
            ) {
                $findings[$serviceId][] = $this->finding(
                    policy  : $policy,
                    code    : 'POL-011',
                    severity: 'warn',
                    category: 'ownership',
                    message : 'ownership posture still depends on the default slice; clarify the owning slice explicitly',
                );
            }

            if (
                $metadata->hasConditions()
                && $metadata->visibility === RegistrationVisibility::INTERNAL
                && ! $metadata->exported
            ) {
                $findings[$serviceId][] = $this->finding(
                    policy  : $policy,
                    code    : 'POL-013',
                    severity: 'warn',
                    category: 'runtime',
                    message : 'conditional registration is hidden behind internal runtime-only posture',
                );
            }

            $lifetime = LifetimePlan::fromRegistration(serviceId: $serviceId, registration: $registration);
            if ($lifetime->isPooled()) {
                if (! is_string(value: $candidate) || ! class_exists(class: $candidate)) {
                    $findings[$serviceId][] = $this->finding(
                        policy  : $policy,
                        code    : 'POL-007',
                        severity: 'error',
                        category: 'runtime',
                        message : 'pooled lifetime requires a class-backed container-owned object',
                    );
                } elseif ($lifetime->poolResetBeforeReuse && ! is_subclass_of(object_or_class: $candidate, class: ResettableInterface::class)) {
                    $findings[$serviceId][] = $this->finding(
                        policy  : $policy,
                        code    : 'POL-007',
                        severity: 'error',
                        category: 'runtime',
                        message : 'pooled lifetime enables reset-before-reuse but the class does not implement ResettableInterface',
                    );
                }
            }

            if (count(value: $registrations->decorationChain(abstract: $serviceId)) >= 4) {
                $findings[$serviceId][] = $this->finding(
                    policy  : $policy,
                    code    : 'POL-012',
                    severity: 'warn',
                    category: 'composition',
                    message : 'service has a long decorator chain; check for decorator sprawl',
                );
            }

            usort(
                array   : $findings[$serviceId],
                callback: static fn (array $left, array $right) : int => [$left['severity'], $left['code']]
                    <=> [$right['severity'], $right['code']],
            );
        }

        ksort(array: $findings);

        return $findings;
    }

    /**
     * @return array{code: string, severity: string, category: string, message: string}
     */
    private function finding(
        ResolutionPolicy $policy,
        #[SensitiveParameter]
        string           $code,
        string           $severity,
        string           $category,
        string           $message,
    ) : array
    {
        return [
            'code'     => $code,
            'severity' => $policy->severityFor(code: $code, defaultSeverity: $severity),
            'category' => $category,
            'message'  => $message,
        ];
    }
}
