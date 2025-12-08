<?php

declare(strict_types=1);

namespace Avax\Container\Containers\Proxy;

use Closure;
use Avax\DataHandling\ArrayHandling\Arrhae;

/**
 * Implements a lazy loading proxy pattern for deferred object initialization.
 * This proxy delays the creation of expensive objects until they are actually needed,
 * optimizing resource utilization and improving application performance.
 *
 * @template T of object
 * @final
 */
final class LazyProxy
{
    /**
     * Metadata container for storing the proxied instance and related information.
     * Using Arrhae for type-safe key-value storage operations.
     *
     * @var Arrhae<string, T>
     */
    private Arrhae $meta;

    /**
     * Initializes a new lazy proxy instance with a resolver closure.
     * Implements constructor promotion for cleaner dependency injection.
     *
     * @param Closure(): T $resolver Factory closure that creates the actual instance when needed
     */
    public function __construct(private readonly Closure $resolver)
    {
        $this->meta = new Arrhae();
    }

    /**
     * Dynamically forwards method calls to the proxied instance.
     * Ensures instance initialization before method invocation.
     *
     * @param string            $method The method name being called
     * @param array<int, mixed> $args   Arguments passed to the method
     *
     * @return mixed The result of the method call on the proxied instance
     */
    public function __call(string $method, array $args) : mixed
    {
        $this->init();

        return $this->meta->get(key: 'instance')->$method(...$args);
    }

    /**
     * Initializes the proxied instance if it hasn't been created yet.
     * Uses a lazy initialization pattern to defer object creation.
     */
    private function init() : void
    {
        if (! $this->meta->has(key: 'instance')) {
            $this->meta->set(key: 'instance', value: ($this->resolver)());
        }
    }

    /**
     * Dynamically forwards property access to the proxied instance.
     * Ensures instance initialization before property access.
     *
     * @param string $prop The property name being accessed
     *
     * @return mixed The value of the property from the proxied instance
     */
    public function __get(string $prop) : mixed
    {
        $this->init();

        return $this->meta->get(key: 'instance')->$prop;
    }

    /**
     * Resets the proxy by removing the cached instance.
     * Allows for re-initialization of the proxied object if needed.
     */
    public function reset() : void
    {
        $this->meta->forget(key: 'instance');
    }

    /**
     * Checks if the proxied instance has been initialized.
     *
     * @return bool True if the instance exists, false otherwise
     */
    public function hasInstance() : bool
    {
        return $this->meta->has(key: 'instance');
    }

    /**
     * Retrieves the proxied instance directly.
     * Note: This method doesn't ensure initialization.
     *
     * @return object|null The proxied instance if initialized, null otherwise
     */
    public function getInstance() : object|null
    {
        return $this->meta->get(key: 'instance');
    }
}