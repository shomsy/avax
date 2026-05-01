<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\RuntimeSafety;

use Avax\Framework\System\Capabilities\StateReset\ResettableState;
use ReflectionClass;
use ReflectionProperty;

/**
 * Scans for static state that may leak between requests.
 *
 * Identifies classes with:
 * - Non-readonly static properties
 * - Static singleton patterns without reset hooks
 * - Static caches or registries
 */
final class StaticStateScanner
{
    /**
     * Scan all loaded Avax classes for unsafe static state.
     *
     * @return list<RuntimeSafetyFinding>
     */
    public function scan() : array
    {
        $findings = [];

        foreach (get_declared_classes() as $className) {
            if (! str_starts_with($className, 'Avax\\')) {
                continue;
            }

            if (! class_exists($className)) {
                continue;
            }

            $classFindings = $this->scanClass($className);
            $findings = [...$findings, ...$classFindings];
        }

        foreach (get_declared_traits() as $traitName) {
            if (! str_starts_with($traitName, 'Avax\\')) {
                continue;
            }

            if (! trait_exists($traitName)) {
                continue;
            }

            $traitFindings = $this->scanTrait($traitName);
            $findings = [...$findings, ...$traitFindings];
        }

        return $findings;
    }

    /**
     * @param class-string $className
     * @return list<RuntimeSafetyFinding>
     */
    private function scanClass(string $className): array
    {
        $findings = [];

        $reflectionClass = new ReflectionClass($className);

        $staticProperties = $reflectionClass->getProperties(ReflectionProperty::IS_STATIC);
        $hasResetHook     = $reflectionClass->implementsInterface(ResettableState::class);

        foreach ($staticProperties as $staticProperty) {
            if ($staticProperty->isReadOnly()) {
                continue;
            }

            if ($hasResetHook) {
                continue;
            }

            $findings[] = new RuntimeSafetyFinding(
                category   : 'static_state',
                severity   : RuntimeSafetyFinding::SEVERITY_WARNING,
                component  : $className,
                message    : sprintf(
                                 'Class %s has mutable static property $%s without reset hook',
                                 $className,
                                 $staticProperty->getName(),
                             ),
                remediation: sprintf(
                                 'Implement ResettableState on %s or make $%s readonly',
                                 $className,
                                 $staticProperty->getName(),
                             ),
                location   : $this->propertyLocation(property: $staticProperty),
            );
        }

        return $findings;
    }

    /**
     * @param class-string $traitName
     * @return list<RuntimeSafetyFinding>
     */
    private function scanTrait(string $traitName): array
    {
        $findings = [];

        $reflectionClass = new ReflectionClass($traitName);

        foreach ($reflectionClass->getProperties(ReflectionProperty::IS_STATIC) as $reflectionProperty) {
            if ($reflectionProperty->isReadOnly()) {
                continue;
            }

            $findings[] = new RuntimeSafetyFinding(
                category   : 'trait_static_state',
                severity   : RuntimeSafetyFinding::SEVERITY_WARNING,
                component  : $traitName,
                message    : sprintf(
                                 'Trait %s introduces mutable static state via $%s',
                                 $traitName,
                                 $reflectionProperty->getName(),
                             ),
                remediation: 'Avoid static state in traits or ensure consumers implement ResettableState',
                location   : $this->propertyLocation(property: $reflectionProperty),
            );
        }

        return $findings;
    }

    private function propertyLocation(ReflectionProperty $reflectionProperty) : string
    {
        $reflectionClass = $reflectionProperty->getDeclaringClass();
        $file            = $reflectionClass->getFileName();
        $line            = $reflectionClass->getStartLine();

        return ($file !== false ? $file : $reflectionClass->getName()) . ':' . $line;
    }
}
