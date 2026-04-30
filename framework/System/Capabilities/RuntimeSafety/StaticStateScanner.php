<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\RuntimeSafety;

use Avax\Framework\System\Capabilities\StateReset\ResettableState;
use ReflectionClass;
use ReflectionProperty;
use Throwable;

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

            $classFindings = $this->scanClass($className);
            $findings      = [...$findings, ...$classFindings];
        }

        foreach (get_declared_traits() as $traitName) {
            if (! str_starts_with($traitName, 'Avax\\')) {
                continue;
            }

            $traitFindings = $this->scanTrait($traitName);
            $findings      = [...$findings, ...$traitFindings];
        }

        return $findings;
    }

    /**
     * @return list<RuntimeSafetyFinding>
     */
    private function scanClass(string $className) : array
    {
        $findings = [];

        try {
            $reflection = new ReflectionClass($className);
        } catch (Throwable) {
            return [];
        }

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
                location   : $property->getFileName() . ':' . $property->getStartLine(),
            );
        }

        return $findings;
    }

    /**
     * @return list<RuntimeSafetyFinding>
     */
    private function scanTrait(string $traitName) : array
    {
        $findings = [];

        try {
            $reflection = new ReflectionClass($traitName);
        } catch (Throwable) {
            return [];
        }

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
                location   : $property->getFileName() . ':' . $property->getStartLine(),
            );
        }

        return $findings;
    }
}
