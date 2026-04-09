<?php

declare(strict_types=1);

namespace Avax\Container\DI\Capabilities\Runtime\Scopes;

/**
 * Disposes owned runtime instances when scopes or shared state end.
 */
final class DisposeInstances
{
    /**
     * @param array<string, mixed> $instances
     * @param array<string, bool> $disposable
     */
    public function disposeMany(array $instances, array $disposable = []) : void
    {
        foreach ($instances as $serviceId => $instance) {
            $this->dispose(
                instance   : $instance,
                disposable : ($disposable[$serviceId] ?? false)
                    || $instance instanceof DisposableInterface
            );
        }
    }

    public function dispose(mixed $instance, bool $disposable = false) : void
    {
        if (! is_object($instance)) {
            return;
        }

        if (! $disposable && ! $instance instanceof DisposableInterface) {
            return;
        }

        if ($instance instanceof DisposableInterface) {
            $instance->dispose();
            return;
        }

        if (method_exists($instance, 'dispose')) {
            $instance->dispose();
        }
    }
}
