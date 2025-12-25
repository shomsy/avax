<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing;

/**
 * A class representing a stack-based storage for managing route group contexts.
 *
 * This stack allows you to maintain the state of nested route group configurations
 * while routing requests. It provides the ability to push a new context to the stack,
 * pop the latest context, or retrieve the current (top-most) context.
 *
 * The class is implemented as a static utility to maintain a global stateful behavior
 * for managing `RouteGroupContext`.
 */
final class RouteGroupStack
{
    /**
     * Static stack containing the list of RouteGroupContext instances.
     *
     * @var RouteGroupContext[] Stack of route group contexts for managing
     *                          nested routing configurations.
     */
    private static array $stack = [];

    /**
     * Contains an internal list of mapped constraints for the current route group.
     *
     * @var array<string, mixed> Associative array where keys represent constraint
     *                           identifiers (e.g., parameter names or attributes),
     *                           and values represent the corresponding constraint
     *                           values or callable validators applied during route
     *                           matching logic.
     */
    private array $constraints = [];

    /**
     * Pushes a new RouteGroupContext onto the stack.
     *
     * This method represents entering a new route group context in the routing lifecycle.
     *
     * @param RouteGroupContext $group The context to be added to the stack.
     *
     * @return void
     */
    public static function push(RouteGroupContext $group) : void
    {
        // Append the provided RouteGroupContext onto the stack.
        self::$stack[] = $group;
    }

    /**
     * Pops the most recently added RouteGroupContext from the stack.
     *
     * This method represents exiting the current route group context in the routing lifecycle.
     *
     * @return void
     */
    public static function pop() : void
    {
        // Remove the most recent context from the stack.
        array_pop(array: self::$stack);
    }

    /**
     * This is a stateless utility method that applies context-specific configuration
     * to a given RouteBuilder instance. This method uses the current application
     * context to dynamically alter the behavior of the routing builder.
     *
     * Example usage:
     *
     * ```
     * $builder = AppRouter::apply($builder);
     * ```
     *
     * @param RouteBuilder $builder Instance of the RouteBuilder object to be configured.
     *
     * @return RouteBuilder Returns the original RouteBuilder instance, potentially
     *                      altered by the context, or returns it unmodified if no context exists.
     */
    public static function apply(RouteBuilder $builder) : RouteBuilder
    {
        // Get the current application context, which encapsulates dynamic state or configuration.
        $context = self::current();

        // If the $context instance exists, apply the context-specific modifications
        // to the provided RouteBuilder ($builder). If no context is available, return
        // the unmodified $builder instance.
        return $context?->applyTo(builder: $builder) ?? $builder;
    }

    /**
     * Retrieves the current (top-most) RouteGroupContext from the stack.
     *
     * The top-most context refers to the one most recently added via `push`.
     * If the stack is empty, this method will return `null`.
     *
     * @return RouteGroupContext|null The current context or `null` if the stack is empty.
     */
    public static function current() : RouteGroupContext|null
    {
        // Get the last context from the stack without removing it.
        return end(array: self::$stack) ?: null;
    }

    /**
     * Adds a set of parameter constraints to the current route group configuration.
     *
     * This method is used to define validation constraints or patterns for parameters
     * within the current routing scope. These constraints are later applied during
     * route matching to ensure the parameters satisfy the defined rules.
     *
     * Example usage:
     * ```
     * $routeGroup->addConstraints([
     *     'id' => '\d+',
     *     'slug' => '[a-z0-9-]+',
     * ]);
     * ```
     *
     * @param array<string, mixed> $constraints An associative array of constraints where the keys
     *                                          represent parameter names (e.g., 'id', 'slug') and
     *                                          the values represent the constraint patterns or
     *                                          validation rules (e.g., regex or callbacks).
     *
     * @return void This method does not return a value.
     */
    public function addConstraints(array $constraints) : void
    {
        // Iterate over the provided associative array of constraints.
        foreach ($constraints as $param => $pattern) {
            // Add or update the constraint for the specified parameter name ($param).
            // Each constraint pattern is stored in the $constraints property for later use.
            $this->constraints[$param] = $pattern;
        }
    }

    /**
     * Retrieves the constraints associated with this route group.
     *
     * Constraints are applied to routes contained within the group and serve
     * as a configuration mechanism for managing shared logic or rules that
     * affect grouped routes.
     *
     * The constraints are returned as-is (no deep or defensive copy is made), so external
     * modifications to the returned array may inadvertently affect the state of the object.
     * Use caution when manipulating the returned array directly.
     *
     * @return array The array of constraints associated with this route group.
     */
    public function getConstraints() : array
    {
        // Return the array of constraints currently associated with this route group.
        return $this->constraints;
    }
}