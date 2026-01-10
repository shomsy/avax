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
 * @see docs/Core/Kernel/Contracts/KernelContext.md#quick-summary
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
     * @param bool               $manualInjection If true, skips constructor injection
     * @param string|null        $consumer      Consumer identifier
     * @param string|null        $traceId       Trace identifier
     * @param int                $depth         Current recursion depth
     * @param KernelContext|null $parent        Parent context in the resolution chain
     * @param array              $overrides     Parameter overrides for this resolution
     * @see docs/Core/Kernel/Contracts/KernelContext.md#method-__construct
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
     * @param string $serviceId Service identifier for the child resolution.
     * @param array  $overrides Runtime parameter overrides.
     * @return self New child context.
     * @see docs/Core/Kernel/Contracts/KernelContext.md#method-child
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
     * @return mixed The service instance or null.
     * @see docs/Core/Kernel/Contracts/KernelContext.md#method-getinstance
     */
    public function getInstance(): mixed
    {
        return $this->instance;
    }

    /**
     * Check if a service exists in the current resolution path.
     * Used for circular dependency detection.
     *
     * @param string $serviceId Identifier to check.
     * @return bool True if found in path.
     * @see docs/Core/Kernel/Contracts/KernelContext.md#method-contains
     */
    public function contains(string $serviceId): bool
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
     * @see docs/Core/Kernel/Contracts/KernelContext.md#method-getpath
     */
    public function getPath(): string
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
     * @return void
     * @see docs/Core/Kernel/Contracts/KernelContext.md#method-setmetaonce
     */
    public function setMetaOnce(string $namespace, string $key, mixed $value): void
    {
        $this->metadata[$namespace][$key] ??= $value;
    }

    /**
     * Set metadata value.
     *
     * @param string $namespace Data namespace.
     * @param string $key       Data key.
     * @param mixed  $value     Data value.
     * @return void
     * @see docs/Core/Kernel/Contracts/KernelContext.md#method-setmeta
     */
    public function setMeta(string $namespace, string $key, mixed $value): void
    {
        $this->putMeta(namespace: $namespace, key: $key, value: $value);
    }

    /**
     * Store metadata value directly.
     *
     * @param string $namespace Data namespace.
     * @param string $key       Data key.
     * @param mixed  $value     Data value.
     * @return void
     * @see docs/Core/Kernel/Contracts/KernelContext.md#method-putmeta
     */
    public function putMeta(string $namespace, string $key, mixed $value): void
    {
        $this->metadata[$namespace][$key] = $value;
    }

    /**
     * Get metadata value with default.
     *
     * @param string $namespace Data namespace.
     * @param string $key       Data key.
     * @param mixed  $default    Fallback value.
     * @return mixed Data if found, default otherwise.
     * @see docs/Core/Kernel/Contracts/KernelContext.md#method-getmeta
     */
    public function getMeta(string $namespace, string $key, mixed $default = null): mixed
    {
        return $this->metadata[$namespace][$key] ?? $default;
    }

    /**
     * Check if metadata exists.
     *
     * @param string $namespace Data namespace.
     * @param string $key       Data key.
     * @return bool True if set.
     * @see docs/Core/Kernel/Contracts/KernelContext.md#method-hasmeta
     */
    public function hasMeta(string $namespace, string $key): bool
    {
        return isset($this->metadata[$namespace][$key]);
    }

    /**
     * Safely set the instance if not already resolved.
     *
     * @param mixed $instance Resolved service.
     * @return void
     * @see docs/Core/Kernel/Contracts/KernelContext.md#method-setinstancesafe
     */
    public function setInstanceSafe(mixed $instance): void
    {
        $this->instance ??= $instance;
    }

    /**
     * Explicitly overwrite the instance. Use for extenders or decorators.
     *
     * @param mixed $instance The new instance.
     * @return void
     * @see docs/Core/Kernel/Contracts/KernelContext.md#method-overwritewith
     */
    public function overwriteWith(mixed $instance): void
    {
        $this->instance = $instance;
    }

    /**
     * Set the resolved instance (alias for resolvedWith).
     *
     * @param object $instance Resolved service.
     * @return void
     * @see docs/Core/Kernel/Contracts/KernelContext.md#method-setinstance
     */
    public function setInstance(object $instance): void
    {
        $this->resolvedWith(instance: $instance);
    }

    /**
     * Safely set the resolved instance. Use for initial resolution.
     *
     * @param mixed $instance Resolved service.
     * @return void
     * @throws LogicException If already resolved.
     * @see docs/Core/Kernel/Contracts/KernelContext.md#method-resolvedwith
     */
    public function resolvedWith(mixed $instance): void
    {
        if ($this->instance !== null) {
            throw new LogicException(message: "Instance already resolved for [{$this->serviceId}]. Use overwriteWith() if modification is intended.");
        }
        $this->instance = $instance;
    }

    /**
     * String representation of the context.
     *
     * @return string
     * @see docs/Core/Kernel/Contracts/KernelContext.md#method-__tostring
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
     * @return bool
     * @see docs/Core/Kernel/Contracts/KernelContext.md#method-isresolved
     */
    public function isResolved(): bool
    {
        return $this->instance !== null;
    }
}
