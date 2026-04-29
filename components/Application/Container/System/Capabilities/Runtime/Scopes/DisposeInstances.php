<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes;

/**
 * Disposes owned runtime instances when scopes or shared state end.
 */
final class DisposeInstances
{
    /**
     * @param array<string, mixed> $instances
     * @param array<string, bool>  $disposable
     */
    public function disposeMany(array $instances, array $disposable = []) : void
    {
        foreach ($instances as $serviceId => $instance) {
            $this->dispose(
                instance  : $instance,
                disposable: ($disposable[$serviceId] ?? false)
                            || $instance instanceof DisposableInterface
            );
        }
    }

    public function dispose(mixed $instance, bool $disposable = false) : void
    {
        if (! is_object(value: $instance)) {
            return;
        }

        if (! $disposable && ! $instance instanceof DisposableInterface) {
            return;
        }

        if ($instance instanceof DisposableInterface) {
            $instance->dispose();

            return;
        }

        if (method_exists(object_or_class: $instance, method: 'dispose')) {
            $instance->dispose();
        }
    }
}
