<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\RuntimeSafety\ResetVerification;

use Avax\Framework\System\Capabilities\RuntimeSafety\RuntimeSafetyFinding;
use Avax\Framework\System\Capabilities\StateReset\ResettableState;
use Closure;
use ReflectionObject;
use Throwable;

/**
 * Verifies that all request-scoped components properly implement reset.
 */
final class ResetVerifier
{
    /**
     * @var array<string, ResettableState>
     */
    private array $registeredComponents = [];

    /**
     * Register a component that should reset between requests.
     */
    public function register(string $name, ResettableState $resettableState) : self
    {
        $this->registeredComponents[$name] = $resettableState;

        return $this;
    }

    /**
     * Verify all registered components can reset cleanly.
     *
     * @return list<RuntimeSafetyFinding>
     */
    public function verify() : array
    {
        $findings = [];

        foreach ($this->registeredComponents as $name => $component) {
            $finding = $this->verifyComponent($name, $component);

            if ($finding instanceof RuntimeSafetyFinding) {
                $findings[] = $finding;
            }
        }

        if (function_exists('app')) {
            $container = app();

            if (is_object($container) && is_callable([$container, 'getSharedInstances'])) {
                $containerFindings = $this->verifyContainerShared($container);
                $findings = [...$findings, ...$containerFindings];
            }
        }

        return $findings;
    }

    /**
     * Verify a single component resets cleanly.
     */
    private function verifyComponent(string $name, ResettableState $resettableState) : ?RuntimeSafetyFinding
    {
        try {
            $resettableState->resetState();

            return null;
        } catch (Throwable $throwable) {
            return new RuntimeSafetyFinding(
                category   : 'reset_failure',
                severity   : RuntimeSafetyFinding::SEVERITY_CRITICAL,
                component  : $name,
                message    : sprintf(
                                 'Component %s failed to reset: %s',
                                 $name,
                                 $throwable->getMessage(),
                             ),
                remediation: 'Fix resetState() implementation to handle all edge cases',
            );
        }
    }

    /**
     * @return list<RuntimeSafetyFinding>
     */
    private function verifyContainerShared(object $container) : array
    {
        $findings = [];

        try {
            $reader = [$container, 'getSharedInstances'];

            if (! is_callable($reader)) {
                return [];
            }

            /** @var array<string, object> $sharedInstances */
            $sharedInstances = Closure::fromCallable($reader)();
        } catch (Throwable) {
            return [];
        }

        foreach ($sharedInstances as $abstract => $instance) {
            if ($instance instanceof ResettableState) {
                continue;
            }

            $className = $instance::class;

            if (! $this->hasRequestLocalState($instance)) {
                continue;
            }

            $findings[] = new RuntimeSafetyFinding(
                category   : 'missing_reset',
                severity   : RuntimeSafetyFinding::SEVERITY_CRITICAL,
                component  : $abstract,
                message    : sprintf(
                                 'Shared service %s (%s) has request-local state but does not implement ResettableState',
                                 $abstract,
                                 $className,
                             ),
                remediation: sprintf(
                                 'Implement ResettableState on %s',
                                 $className,
                             ),
            );
        }

        return $findings;
    }

    /**
     * Check if an object appears to hold request-local state.
     */
    private function hasRequestLocalState(object $instance) : bool
    {
        $reflectionObject = new ReflectionObject($instance);

        foreach ($reflectionObject->getProperties() as $reflectionProperty) {
            $name = strtolower($reflectionProperty->getName());

            if (str_contains($name, 'request') || str_contains($name, 'session') || str_contains($name, 'input') || str_contains($name, 'response') || str_contains($name, 'context')
            ) {
                return true;
            }
        }

        return false;
    }
}
