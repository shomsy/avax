<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel\Contracts;

use LogicException;

/**
 * Kernel Context - Mutable Service Resolution State
 *
 * Holds the state of a service resolution operation as it progresses through
 * the resolution pipeline. Contains the service identifier, resolved instance
 * (when available), and metadata accumulated by pipeline steps, enabling coordinated state management across resolution steps.
 *
 * @see docs_md/Core/Kernel/Contracts/KernelContext.md#quick-summary
 */
final class KernelContext
{
    /**
     * Create a new resolution context.
     *
     * @param string             $serviceId     Unique identifier of the service being resolved
     * @param mixed|null         $instance      The resolved service instance
     * @param array              $metadata      Additional data accumulated during resolution
     * @param bool               $debug         Enable debug mode
     * @param bool               $allowAutowire Allow automatic dependency resolution
     * @param string|null        $consumer      Consumer identifier
     * @param string|null        $traceId       Trace identifier
     * @param int                $depth         Current recursion depth
     * @param KernelContext|null $parent        Parent context in the resolution chain
     * @param array              $overrides     Parameter overrides for this resolution
     */
    public function __construct(
        public readonly string             $serviceId,
        protected mixed                    $instance = null,
        public array                       $metadata = [],
        public readonly bool               $debug = false,
        public readonly bool               $allowAutowire = true,
        public readonly bool               $manualInjection = false,
        public readonly string|null        $consumer = null,
        public readonly string|null        $traceId = null,
        public readonly int                $depth = 0,
        public readonly KernelContext|null $parent = null,
        public readonly array              $overrides = []
    ) {}

    /**
     * Create a child context for recursive resolution.
     *
     * Creates a new context for resolving dependencies of the current service.
     * Maintains the resolution path and increments depth for circular dependency detection.
     * The child context inherits debug settings and trace ID while establishing the parent as consumer.
     *
     * @param string $serviceId Service identifier for the child resolution
     * @param array $overrides Parameter overrides for child resolution
     * @return self New child context
     * @see docs_md/Core/Kernel/Contracts/KernelContext.md#method-child
     */
    public function child(string $serviceId, array $overrides = []): self
    {
        return new self(
            serviceId: $serviceId,
            debug: $this->debug,
            allowAutowire: $this->allowAutowire,
            manualInjection: $this->manualInjection,
            consumer: $this->serviceId, // The parent service is the consumer
            traceId: $this->traceId,
            depth: $this->depth + 1,
            parent: $this,
            overrides: $overrides
        );
    }

    /**
     * Get the resolved instance.
     *
     * Returns the service instance that has been resolved by the pipeline, or null if resolution
     * has not yet completed. This method provides access to the final result of the resolution process.
     *
     * @return mixed The resolved service instance or null if not yet resolved
     * @see docs_md/Core/Kernel/Contracts/KernelContext.md#method-getInstance
     */
    public function getInstance(): mixed
    {
        return $this->instance;
    }

    /**
     * Check if a service exists in the current resolution path.
     * Used for circular dependency detection.
     *
     * Traverses the parent context chain to detect if the given service ID is already
     * being resolved higher up in the dependency tree, preventing infinite loops.
     *
     * @param string $serviceId Service identifier to check
     * @return bool True if service is in the resolution path (circular dependency)
     * @see docs_md/Core/Kernel/Contracts/KernelContext.md#method-contains
     */
    public function contains(string $serviceId): bool
    {
        $current = $this;
        while ($current !== null) {
            if ($current->serviceId === $serviceId) {
                return true;
            }
            $current = $current->parent; // ✓ Fixed: was $this->parent (infinite loop!)
        }

        return false;
    }

    /**
     * Get the resolution path as a string.
     *
     * Builds a human-readable string showing the complete chain of service dependencies
     * from the root resolution down to the current context, useful for debugging.
     *
     * @return string Resolution path showing the chain of service dependencies
     * @see docs_md/Core/Kernel/Contracts/KernelContext.md#method-getPath
     */
    public function getPath(): string
    {
        $path = $this->parent?->getPath() ?? '';

        return ($path !== '' ? $path . ' -> ' : '') . $this->serviceId;
    }

    /**
     * Set metadata only if not already set.
     *
     * Stores a metadata value in the specified namespace and key, but only if that key
     * doesn't already exist. This prevents accidental overwrites of important metadata.
     *
     * @param string $namespace Metadata namespace
     * @param string $key Metadata key
     * @param mixed $value Metadata value
     * @see docs_md/Core/Kernel/Contracts/KernelContext.md#method-setMetaOnce
     */
    public function setMetaOnce(string $namespace, string $key, mixed $value): void
    {
        if (! isset($this->metadata[$namespace][$key])) {
            $this->metadata[$namespace][$key] = $value;
        }
    }

    /**
     * Set metadata value.
     *
     * Stores a metadata value in the specified namespace and key, allowing pipeline steps
     * to share information and state during the resolution process.
     *
     * @param string $namespace Metadata namespace
     * @param string $key Metadata key
     * @param mixed $value Metadata value
     * @see docs_md/Core/Kernel/Contracts/KernelContext.md#method-setMeta
     */
    public function setMeta(string $namespace, string $key, mixed $value): void
    {
        $this->putMeta($namespace, $key, $value);
    }

    /**
     * Store metadata value.
     *
     * Directly stores a metadata value without additional processing, providing
     * a low-level interface for metadata management.
     *
     * @param string $namespace Metadata namespace
     * @param string $key Metadata key
     * @param mixed $value Metadata value to store
     * @see docs_md/Core/Kernel/Contracts/KernelContext.md#method-putMeta
     */
    public function putMeta(string $namespace, string $key, mixed $value): void
    {
        $this->metadata[$namespace][$key] = $value;
    }

    /**
     * Get metadata value with default.
     *
     * Retrieves a metadata value from the specified namespace and key, returning
     * the provided default value if the key doesn't exist.
     *
     * @param string $namespace Metadata namespace
     * @param string $key Metadata key
     * @param mixed $default Default value if key not found
     * @return mixed Metadata value or default
     * @see docs_md/Core/Kernel/Contracts/KernelContext.md#method-getMeta
     */
    public function getMeta(string $namespace, string $key, mixed $default = null): mixed
    {
        return $this->metadata[$namespace][$key] ?? $default;
    }

    /**
     * Check if metadata exists.
     *
     * Determines whether a metadata value has been set for the specified namespace and key.
     *
     * @param string $namespace Metadata namespace
     * @param string $key Metadata key
     * @return bool True if metadata exists
     * @see docs_md/Core/Kernel/Contracts/KernelContext.md#method-hasMeta
     */
    public function hasMeta(string $namespace, string $key): bool
    {
        return isset($this->metadata[$namespace][$key]);
    }

    /**
     * Safely set the instance if not already resolved.
     * Prevents LogicException if the instance was already set by another step.
     *
     * Sets the resolved instance only if no instance has been set yet, providing
     * a safe way for pipeline steps to contribute to resolution without conflicts.
     *
     * @param mixed $instance Service instance to set
     * @see docs_md/Core/Kernel/Contracts/KernelContext.md#method-setInstanceSafe
     */
    public function setInstanceSafe(mixed $instance): void
    {
        if ($this->instance === null) {
            $this->instance = $instance;
        }
    }

    /**
     * Explicitly overwrite the instance. Use for extenders or decorators.
     *
     * Replaces the current resolved instance with a new one, intended for use by
     * extenders, decorators, or other transformations that modify the final result.
     *
     * @param mixed $instance New instance to set (overwrites existing)
     * @see docs_md/Core/Kernel/Contracts/KernelContext.md#method-overwriteWith
     */
    public function overwriteWith(mixed $instance): void
    {
        $this->instance = $instance;
    }

    /**
     * Set the resolved instance.
     *
     * Provides a convenient alias for resolvedWith(), setting the service instance
     * as the final result of the resolution process.
     *
     * @param object $instance Service instance
     * @see docs_md/Core/Kernel/Contracts/KernelContext.md#method-setInstance
     */
    public function setInstance(object $instance): void
    {
        $this->resolvedWith($instance);
    }

    /**
     * Safely set the resolved instance. Use for initial resolution.
     *
     * Sets the service instance as resolved, but only if no instance has been set yet.
     * This prevents accidental overwrites and ensures clean resolution state.
     *
     * @param mixed $instance Service instance to set
     * @throws LogicException If instance already resolved
     * @see docs_md/Core/Kernel/Contracts/KernelContext.md#method-resolvedWith
     */
    public function resolvedWith(mixed $instance): void
    {
        if ($this->instance !== null) {
            throw new LogicException("Instance already resolved for [{$this->serviceId}]. Use overwriteWith() if modification is intended.");
        }
        $this->instance = $instance;
    }

    /**
     * String representation of the context.
     *
     * Returns a human-readable string summarizing the context's current state,
     * including service ID, resolution depth, and resolution status.
     *
     * @return string Context information
     * @see docs_md/Core/Kernel/Contracts/KernelContext.md#method-__toString
     */
    public function __toString(): string
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
     * Determines whether the resolution process has completed and an instance is available.
     *
     * @return bool True if instance is resolved
     * @see docs_md/Core/Kernel/Contracts/KernelContext.md#method-isResolved
     */
    public function isResolved(): bool
    {
        return $this->instance !== null;
    }
}
