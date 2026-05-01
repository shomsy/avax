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
            $findings      = [...$findings, ...$classFindings];
        }

        foreach (get_declared_traits() as $traitName) {
            if (! str_starts_with($traitName, 'Avax\\')) {
                continue;
            }

            if (! trait_exists($traitName)) {
                continue;
            }

            $traitFindings = $this->scanTrait($traitName);
            $findings      = [...$findings, ...$traitFindings];
        }

        return $findings;
    }

    /**
     * @param class-string $className
     *
     * @return list<RuntimeSafetyFinding>
     */
    private function scanClass(string $className) : array
    {
        $findings = [];

        $reflection = new ReflectionClass($className);

        $staticProperties = $reflection->getProperties(ReflectionProperty::IS_STATIC);
        $hasResetHook     = $reflection->implementsInterface(ResettableState::class);

        foreach ($staticProperties as $property) {
            if ($property->isReadOnly()) {
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
                                 $property->getName(),
                             ),
                remediation: sprintf(
                                 'Implement ResettableState on %s or make $%s readonly',
                                 $className,
                                 $property->getName(),
                             ),
                location   : $this->propertyLocation(property: $property),
            );
        }

        return $findings;
    }

    /**
     * @param class-string $traitName
     *
     * @return list<RuntimeSafetyFinding>
     */
    private function scanTrait(string $traitName) : array
    {
        $findings = [];

        $reflection = new ReflectionClass($traitName);

        foreach ($reflection->getProperties(ReflectionProperty::IS_STATIC) as $property) {
            if ($property->isReadOnly()) {
                continue;
            }

            $findings[] = new RuntimeSafetyFinding(
                category   : 'trait_static_state',
                severity   : RuntimeSafetyFinding::SEVERITY_WARNING,
                component  : $traitName,
                message    : sprintf(
                                 'Trait %s introduces mutable static state via $%s',
                                 $traitName,
                                 $property->getName(),
                             ),
                remediation: 'Avoid static state in traits or ensure consumers implement ResettableState',
                location   : $this->propertyLocation(property: $property),
            );
        }

        return $findings;
    }

    private function propertyLocation(ReflectionProperty $property) : string
    {
        $class = $property->getDeclaringClass();
        $file  = $class->getFileName();
        $line  = $class->getStartLine();

        return ($file !== false ? $file : $class->getName()) . ':' . $line;
    }
}
