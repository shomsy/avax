<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\Capabilities\ResolveEventListeners;

use Avax\Components\Operations\Events\System\Foundation\CompiledListener;

/**
 * Resolves compiled listener entries into invokable callables.
 *
 * Handles both:
 * - Already-resolved callables (from DSL compilation)
 * - Class-string references (from attribute compilation)
 *
 * For class-string listeners, instantiates the listener with no-argument constructor.
 * If a container is available, it should be used — but for V5.7, simple instantiation
 * is used. Container integration is ROADMAP.
 */
final class ResolveEventListeners
{
    /**
     * Resolve a compiled listener into a callable.
     *
     * @throws \RuntimeException If a class-string listener cannot be instantiated.
     */
    public function resolve(CompiledListener $compiled): callable
    {
        $listener = $compiled->listener;

        // Class-string from attribute compilation.
        if (is_string($listener)) {
            return $this->instantiateListener($listener);
        }

        // Already a callable from DSL compilation.
        return $listener;
    }

    /**
     * Instantiate a listener class by its fully qualified class name.
     */
    private function instantiateListener(string $class): callable
    {
        if (! class_exists($class)) {
            throw new \RuntimeException(sprintf('Listener class "%s" does not exist.', $class));
        }

        try {
            $instance = new $class();
        } catch (\Throwable $e) {
            throw new \RuntimeException(
                sprintf('Cannot instantiate listener class "%s". It may require constructor dependencies. Container integration is planned for a future release.', $class),
                previous: $e,
            );
        }

        if (! is_callable($instance)) {
            throw new \RuntimeException(sprintf('Listener class "%s" is not invokable. It must implement __invoke().', $class));
        }

        return $instance;
    }
}
