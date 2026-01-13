<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing;

/**
 * Instance-based stack for managing route group contexts.
 *
 * Replaces static global state with proper dependency injection.
 * Each RouterDsl instance gets its own RouteGroupStack for isolation.
 */
final class RouteGroupStack
{
    /**
     * Stack of route group contexts for managing nested routing configurations.
     *
     * @var RouteGroupContext[]
     */
    private array $stack = [];

    /**
     * Create a new RouteGroupStack instance.
     */
    public function __construct() {}

    /**
     * Pushes a new RouteGroupContext onto the stack.
     *
     * @param RouteGroupContext $group The context to be added to the stack.
     */
    public function push(RouteGroupContext $group) : void
    {
        $this->stack[] = $group;
    }

    /**
     * Pops the most recently added RouteGroupContext from the stack.
     */
    public function pop() : void
    {
        array_pop($this->stack);
    }

    /**
     * Applies current context configuration to a RouteBuilder.
     *
     * @param RouteBuilder $builder The builder to configure.
     *
     * @return RouteBuilder The configured builder.
     */
    public function applyTo(RouteBuilder $builder) : RouteBuilder
    {
        $context = $this->current();

        return $context?->applyTo(builder: $builder) ?? $builder;
    }

    /**
     * Retrieves the current (top-most) RouteGroupContext from the stack.
     *
     * @return RouteGroupContext|null The current context or null if stack is empty.
     */
    public function current() : RouteGroupContext|null
    {
        return end($this->stack) ?: null;
    }

    /**
     * Checks if the stack is empty.
     */
    public function isEmpty() : bool
    {
        return empty($this->stack);
    }

    /**
     * Gets the current stack depth.
     */
    public function depth() : int
    {
        return count($this->stack);
    }

    /**
     * Captures current stack state for snapshot/restore operations.
     *
     * @return RouteGroupContext[]
     */
    public function snapshot() : array
    {
        return $this->stack;
    }

    /**
     * Restores stack to a previously captured state.
     *
     * @param RouteGroupContext[] $stack
     */
    public function restore(array $stack) : void
    {
        $this->stack = $stack;
    }

    /**
     * Clears the entire stack (used for cleanup/testing).
     */
    public function clear() : void
    {
        $this->stack = [];
    }
}
