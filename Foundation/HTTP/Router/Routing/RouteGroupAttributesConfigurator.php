<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing;

use InvalidArgumentException;

/**
 * The `RouteGroupAttributesConfigurator` class is responsible for configuring
 * a route group context by applying a set of attributes like prefix, middleware,
 * domain, and more.
 *
 * This class uses the Strategy design pattern to map each attribute to its
 * respective handler, ensuring extensibility and separation of responsibilities.
 */
final class RouteGroupAttributesConfigurator
{
    /**
     * Map of supported route group attributes and their respective handlers,
     * defined as callable strategies. Each attributes key maps to a closure/function
     * that modifies the `RouteGroupContext`.
     *
     * @var array<string, callable(RouteGroupContext, mixed): void> A map of attribute keys and their handlers.
     */
    private array $strategies;

    /**
     * Constructor of the class.
     *
     * Initializes the mapping of available attribute handlers (`$strategies`) with
     * their processing logic defined as closures. Using constructor promotion for
     * a lean and expressive instantiation process.
     */
    public function __construct()
    {
        // Initializes the strategy map with closures for each supported attribute:
        $this->strategies = [
            /**
             * Strategy for handling 'prefix' - converts the value to a string
             * and applies it as a prefix to the route group context.
             */
            'prefix'     => fn(RouteGroupContext $context, mixed $value) => $context->setPrefix((string) $value),

            /**
             * Strategy for handling 'middleware' - converts the value to an array
             * and appends the middleware to the route group context.
             */
            'middleware' => fn(RouteGroupContext $context, mixed $value) => $context->addMiddleware(
                (array) $value
            ),

            /**
             * Strategy for handling 'domain' - converts the value to a string
             * and sets it as the domain for the route group context.
             */
            'domain'     => fn(RouteGroupContext $context, mixed $value) => $context->setDomain((string) $value),

            /**
             * Strategy for handling 'name' - converts the value to a string
             * and applies it as a prefix to the names of route group context
             * names.
             */
            'name'       => fn(RouteGroupContext $context, mixed $value) => $context->setNamePrefix(
                (string) $value
            ),

            /**
             * Strategy for handling 'authorize' - converts the value to a string
             * and sets it as authorization for the route group context.
             */
            'authorize'  => fn(RouteGroupContext $context, mixed $value) => $context->setAuthorization(
                (string) $value
            ),

            // Additional attribute types can be added here following the same pattern, maintaining extensibility.
        ];
    }

    /**
     * Applies the provided attributes to the given route group context.
     *
     * Iterates through each key-value pair of attributes, validates the key
     * against the supported strategies, and applies the corresponding
     * handler to modify the `RouteGroupContext`.
     *
     * @param array<string, mixed> $attributes A map of attributes to be configured for the route group context (e.g.,
     *                                         'prefix' => '/api').
     * @param RouteGroupContext    $context    The route group context where the attributes will be applied.
     *
     * @throws InvalidArgumentException If an attribute key is not recognized or unsupported.
     */
    public function apply(array $attributes, RouteGroupContext $context) : void
    {
        // Iterate over each key-value pair of attributes.
        foreach ($attributes as $attribute => $value) {
            // Check if the attribute is supported by existing strategies.
            if (! isset($this->strategies[$attribute])) {
                // If not supported, throw an exception to enforce proper usage.
                throw new InvalidArgumentException(
                    sprintf('Unsupported route group attribute: %s', $attribute)
                );
            }

            // Execute the corresponding strategy using a callable, passing in
            // the target context and the attribute value.
            ($this->strategies[$attribute])($context, $value);
        }
    }
}