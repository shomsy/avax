<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capability\Resolution\Kernel;

use LogicException;

/**
 * Kernel Context - Mutable Service Resolution State
 *
 * Holds the state of a service resolution operation as it progresses through
 * the resolution pipeline. Contains the service identifier, resolved instance
 * (when available), and metadata accumulated by pipeline steps, enabling coordinated state management across
 * resolution steps.
 *
 */
final class KernelContext
{
    /**
     * Create a new resolution context.
     *
     * @param string             $serviceId       Unique identifier of the service being resolved
     * @param mixed|null         $instance        The resolved service instance
     * @param array              $metadata        Additional data accumulated during resolution
     * @param bool               $debug           Enable debug mode
     * @param bool               $allowAutowire   Allow automatic dependency resolution
     * @param bool               $manualInjection If true, skips constructor injection
     * @param string|null        $consumer        Consumer identifier
     * @param string|null        $traceId         Trace identifier
     * @param int                $depth           Current recursion depth
     * @param KernelContext|null $parent          Parent context in the resolution chain
     * @param array              $overrides       Parameter overrides for this resolution
     *
     */
    public function __construct(
        public readonly string             $serviceId,
        protected mixed                    $instance = null,
        public array|null                  $metadata = null,
        public readonly bool               $debug = false,
        public readonly bool               $allowAutowire = true,
        public readonly bool               $manualInjection = false,
        public readonly string|null        $consumer = null,
        public readonly string|null        $traceId = null,
        public readonly int                $depth = 0,
        public readonly KernelContext|null $parent = null,
        public readonly array              $overrides = []
    )
    {
        $this->metadata ??= [];
    }

    /**
     * Create a child context for recursive resolution.
     *
     * @param string $serviceId Service identifier for the child resolution.
     * @param array  $overrides Runtime parameter overrides.
     *
     * @return self New child context.
     *
     */
    public function child(string $serviceId, array $overrides = []) : self
    {
        return new self(
            serviceId      : $serviceId,
            debug          : $this->debug,
            allowAutowire  : $this->allowAutowire,
            manualInjection: $this->manualInjection,
            consumer       : $this->serviceId, // The parent service is the consumer
            traceId        : $this->traceId,
            depth          : $this->depth + 1,
            parent         : $this,
            overrides      : $overrides
        );
    }

    /**
     * Get the resolved instance.
     *
     * @return mixed The service instance or null.
     *
     */
    public function getInstance() : mixed
    {
        return $this->instance;
    }

    /**
     * Check if a service exists in the current resolution path.
     * Used for circular dependency detection.
     *
     * @param string $serviceId Identifier to check.
     *
     * @return bool True if found in path.
     *
     */
    public function contains(string $serviceId) : bool
    {
        $current = $this;
        while ($current !== null) {
            if ($current->serviceId === $serviceId) {
                return true;
            }
            $current = $current->parent;
        }

        return false;
    }

    /**
     * Get the resolution path as a string.
     *
     * @return string Human-readable path.
     *
     */
    public function getPath() : string
    {
        $path = $this->parent?->getPath() ?? '';

        return ($path !== '' ? $path . ' -> ' : '') . $this->serviceId;
    }

    /**
     * Set metadata only if not already set.
     *
     * @param string $namespace Data namespace.
     * @param string $key       Data key.
     * @param mixed  $value     Data value.
     *
     * @throws LogicException When attempting to overwrite with a different value.
     *
     */
    public function setMetaOnce(string $namespace, string $key, mixed $value) : void
    {
        if (isset($this->metadata[$namespace][$key])) {
            if ($this->metadata[$namespace][$key] !== $value) {
                throw new LogicException(message: "Metadata [{$namespace}.{$key}] is already set and cannot be overwritten. Use putMeta() to replace intentionally.");
            }

            return;
        }

        $this->metadata[$namespace][$key] = $value;
    }

    /**
     * Set metadata value.
     *
     * @param string $namespace Data namespace.
     * @param string $key       Data key.
     * @param mixed  $value     Data value.
     *
     */
    public function setMeta(string $namespace, string $key, mixed $value) : void
    {
        $this->putMeta(namespace: $namespace, key: $key, value: $value);
    }

    /**
     * Store metadata value directly.
     *
     * @param string $namespace Data namespace.
     * @param string $key       Data key.
     * @param mixed  $value     Data value.
     *
     */
    public function putMeta(string $namespace, string $key, mixed $value) : void
    {
        $this->metadata[$namespace][$key] = $value;
    }

    /**
     * Get metadata value with default.
     *
     * @param string $namespace Data namespace.
     * @param string $key       Data key.
     * @param mixed  $default   Fallback value.
     *
     * @return mixed Data if found, default otherwise.
     *
     */
    public function getMeta(string $namespace, string $key, mixed $default = null) : mixed
    {
        return $this->metadata[$namespace][$key] ?? $default;
    }

    /**
     * Check if metadata exists.
     *
     * @param string $namespace Data namespace.
     * @param string $key       Data key.
     *
     * @return bool True if set.
     *
     */
    public function hasMeta(string $namespace, string $key) : bool
    {
        return isset($this->metadata[$namespace][$key]);
    }

    /**
     * Safely set the instance if not already resolved.
     *
     * @param mixed $instance Resolved service.
     *
     */
    public function setInstanceSafe(mixed $instance) : void
    {
        $this->instance ??= $instance;
    }

    /**
     * Explicitly overwrite the instance. Use for extenders or decorators.
     *
     * @param mixed $instance The new instance.
     *
     */
    public function overwriteWith(mixed $instance) : void
    {
        $this->instance = $instance;
    }

    /**
     * Set the resolved instance (alias for resolvedWith).
     *
     * @param object $instance Resolved service.
     *
     */
    public function setInstance(object $instance) : void
    {
        $this->resolvedWith(instance: $instance);
    }

    /**
     * Safely set the resolved instance. Use for initial resolution.
     *
     * @param mixed $instance Resolved service.
     *
     * @throws LogicException If already resolved.
     *
     */
    public function resolvedWith(mixed $instance) : void
    {
        if ($this->instance !== null) {
            throw new LogicException(message: "Instance already resolved for [{$this->serviceId}]. Use overwriteWith() if modification is intended.");
        }
        $this->instance = $instance;
    }

    /**
     * String representation of the context.
     *
     */
    public function __toString() : string
    {
        return sprintf(
            'KernelContext{serviceId=%s, depth=%d, resolved=%s}',
            $this->serviceId,
            $this->depth,
            $this->isResolved() ? 'yes' : 'no'
        );
    }

    /**
     * Check if the context has a resolved instance.
     *
     */
    public function isResolved() : bool
    {
        return $this->instance !== null;
    }
}
