<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\RuntimeSafety\ResetVerification;

use Avax\Framework\System\Capabilities\RuntimeSafety\RuntimeSafetyFinding;
use Avax\Framework\System\Capabilities\StateReset\ResettableState;
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
    public function register(string $name, ResettableState $component) : self
    {
        $this->registeredComponents[$name] = $component;

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

            if ($finding !== null) {
                $findings[] = $finding;
            }
        }

        if (function_exists('app')) {
            $container = app();

            if ($container !== null && method_exists($container, 'getSharedInstances')) {
                $containerFindings = $this->verifyContainerShared($container);
                $findings          = [...$findings, ...$containerFindings];
            }
        }

        return $findings;
    }

    /**
     * Verify a single component resets cleanly.
     */
    private function verifyComponent(string $name, ResettableState $component) : RuntimeSafetyFinding|null
    {
        try {
            $component->reset();

            return null;
        } catch (Throwable $exception) {
            return new RuntimeSafetyFinding(
                category   : 'reset_failure',
                severity   : RuntimeSafetyFinding::SEVERITY_CRITICAL,
                component  : $name,
                message    : sprintf(
                                 'Component %s failed to reset: %s',
                                 $name,
                                 $exception->getMessage(),
                             ),
                remediation: 'Fix reset() implementation to handle all edge cases',
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
            $sharedInstances = $container->getSharedInstances();
        } catch (Throwable) {
            return [];
        }

        foreach ($sharedInstances as $abstract => $instance) {
            if ($instance instanceof ResettableState) {
                continue;
            }

            $className = get_class($instance);

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
        $reflection = new ReflectionObject($instance);

        foreach ($reflection->getProperties() as $property) {
            $name = strtolower($property->getName());

            if (str_contains($name, 'request') ||
                str_contains($name, 'session') ||
                str_contains($name, 'input') ||
                str_contains($name, 'response') ||
                str_contains($name, 'context')
            ) {
                return true;
            }
        }

        return false;
    }
}
