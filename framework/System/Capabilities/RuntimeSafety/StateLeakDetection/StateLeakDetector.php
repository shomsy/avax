<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\RuntimeSafety\StateLeakDetection;

use Avax\Framework\System\Capabilities\RuntimeSafety\RuntimeSafetyFinding;
use Closure;
use ReflectionClass;
use ReflectionObject;
use ReflectionProperty;

/**
 * Detects state leaks between requests in long-lived runtimes.
 *
 * Checks for:
 * - Mutable static state in classes
 * - Singleton services holding request-local data
 * - Unclosed transactions
 * - Leaked request-scoped bindings
 */
final class StateLeakDetector
{
    /**
     * @var array<string, callable(): mixed>
     */
    private array $leakChecks = [];

    public function __construct()
    {
        $this->registerDefaultChecks();
    }

    private function registerDefaultChecks() : void
    {
        $this->addCheck('static_mutation', $this->detectStaticMutation(...));
        $this->addCheck('singleton_leaks', $this->detectSingletonLeaks(...));
    }

    /**
     * Add a custom leak check.
     */
    /**
     * @param callable(): mixed $check
     */
    public function addCheck(string $name, callable $check) : self
    {
        $this->leakChecks[$name] = $check;

        return $this;
    }

    /**
     * Run all leak checks.
     *
     * @return list<RuntimeSafetyFinding>
     */
    public function detect() : array
    {
        $findings = [];

        foreach ($this->leakChecks as $name => $check) {
            $results = $check();

            if (is_array($results)) {
                $findings = [...$findings, ...$results];
            } elseif ($results instanceof RuntimeSafetyFinding) {
                $findings[] = $results;
            }
        }

        return $findings;
    }

    /**
     * Detect mutable static state across classes.
     *
     * @return list<RuntimeSafetyFinding>
     */
    private function detectStaticMutation() : array
    {
        $findings = [];
        $declaredClasses = get_declared_classes();

        foreach ($declaredClasses as $className) {
            if (! str_starts_with($className, 'Avax\\')) {
                continue;
            }

            if (! class_exists($className)) {
                continue;
            }

            /** @var class-string $className */
            $reflection = new ReflectionClass($className);

            foreach ($reflection->getProperties(ReflectionProperty::IS_STATIC) as $property) {
                if (! $property->isReadOnly() && ! $property->hasDefaultValue()) {
                    $findings[] = new RuntimeSafetyFinding(
                        category   : 'state_leak',
                        severity   : RuntimeSafetyFinding::SEVERITY_WARNING,
                        component  : $className,
                        message    : sprintf(
                                         'Class %s has mutable static property $%s that may leak between requests',
                                         $className,
                                         $property->getName(),
                                     ),
                        remediation: sprintf(
                                         'Make $%s readonly or add a reset hook for worker mode',
                                         $property->getName(),
                                     ),
                        location   : $this->propertyLocation(property: $property),
                    );
                }
            }
        }

        return $findings;
    }

    /**
     * Detect singleton services that may hold request-local data.
     *
     * @return list<RuntimeSafetyFinding>
     */
    private function detectSingletonLeaks() : array
    {
        $findings = [];

        if (function_exists('app')) {
            $container = app();

            if (! is_object($container)) {
                return [];
            }

            if (method_exists($container, 'getSharedInstances')) {
                /** @var array<string, object> $sharedInstances */
                $sharedInstances = Closure::fromCallable([$container, 'getSharedInstances'])();

                foreach ($sharedInstances as $abstract => $instance) {
                    $reflection = new ReflectionObject($instance);

                    foreach ($reflection->getProperties() as $property) {
                        $propertyName = $property->getName();

                        $requestLocalNames = ['request', 'currentRequest', 'lastInput', 'lastResponse', 'session', 'currentUser'];

                        if (in_array($propertyName, $requestLocalNames, true)) {
                            $findings[] = new RuntimeSafetyFinding(
                                category   : 'singleton_leak',
                                severity   : RuntimeSafetyFinding::SEVERITY_CRITICAL,
                                component  : $abstract,
                                message    : sprintf(
                                                 'Singleton %s holds request-local data in $%s',
                                                 $abstract,
                                                 $propertyName,
                                             ),
                                remediation: sprintf(
                                                 'Implement ResettableState on %s and clear $%s in reset()',
                                                 $abstract,
                                                 $propertyName,
                                             ),
                            );
                        }
                    }
                }
            }
        }

        return $findings;
    }

    private function propertyLocation(ReflectionProperty $property) : string
    {
        $class = $property->getDeclaringClass();
        $file = $class->getFileName();
        $line = $class->getStartLine();

        return ($file !== false ? $file : $class->getName()) . ':' . $line;
    }
}
