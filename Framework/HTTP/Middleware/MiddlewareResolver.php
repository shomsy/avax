<?php

declare(strict_types=1);

namespace Gemini\HTTP\Middleware;

use Gemini\HTTP\Router\Exceptions\UnresolvableMiddlewareException;

/**
 * MiddlewareResolver
 *
 * Responsible for resolving middleware identifiers (either class names or group aliases)
 * into fully qualified class names. This class enables middleware pipelines to operate
 * with a resolved list of middleware class names, whether individual or grouped.
 */
final readonly class MiddlewareResolver
{
    /**
     * Constructor to initialize the resolver with its dependencies.
     *
     * @param MiddlewareGroupResolver $groupResolver The dependency capable of resolving middleware groups.
     */
    public function __construct(private MiddlewareGroupResolver $groupResolver) {}

    /**
     * Resolves a list of middleware definitions. Entries in the array can be either:
     * - Fully qualified class names (FQCNs) of middleware.
     * - Group aliases that represent a defined set of middleware.
     *
     * @param array<string|class-string> $middleware A list of middleware definitions (FQCNs or group aliases).
     *
     * @return array<class-string> Returns a list of resolved middleware FQCNs (fully-qualified class names).
     *
     * @throws UnresolvableMiddlewareException If any middleware entry is invalid or unresolvable.
     */
    public function resolve(array $middleware) : array
    {
        // Initialize an empty array to collect resolved middleware class names.
        $resolved = [];

        // Iterate through each middleware entry in the provided list.
        foreach ($middleware as $entry) {
            // Validate the middleware entry, ensuring it adheres to the expected data type.
            $this->validateEntry($entry);

            // If the entry matches a defined middleware group alias:
            if ($this->groupResolver->hasGroup(group: $entry)) {
                // Recursively resolve the middleware group and merge its entries into the result.
                $resolved = array_merge($resolved, $this->resolveGroup(entry: $entry));
            } elseif (class_exists($entry)) {
                // If the entry is a valid class name, add it to the result list.
                $resolved[] = $entry;
            } else {
                // If the entry cannot be resolved, throw an exception with details.
                throw new UnresolvableMiddlewareException(
                    message: "Middleware identifier [{$entry}] could not be resolved to a class or group."
                );
            }
        }

        // Return the fully resolved list of middleware class names.
        return $resolved;
    }

    /**
     * Validates the middleware entry to ensure it adheres to the expected type.
     *
     * Middleware entries must be strings to represent either:
     * - A middleware FQCN (class-string).
     * - A middleware group alias defined in the configuration.
     *
     * @param mixed $entry The middleware entry provided by the user.
     *
     * @throws UnresolvableMiddlewareException If the middleware entry is not a valid string.
     */
    private function validateEntry(mixed $entry) : void
    {
        // Ensure the entry is a string; otherwise, reject the entry.
        if (! is_string($entry)) {
            throw new UnresolvableMiddlewareException(
                message: "Middleware entry must be a string. Got: " . gettype($entry)
            );
        }
    }

    /**
     * Resolves a middleware group alias into its corresponding middleware class names.
     *
     * This process delegates the resolution task to the `MiddlewareGroupResolver` instance
     * and supports recursive resolution of nested middleware groups.
     *
     * @param string $entry The middleware group alias to resolve (e.g., 'web', 'api').
     *
     * @return array<class-string> Returns the fully resolved middleware classes for the group.
     *
     * @throws UnresolvableMiddlewareException If the group cannot be resolved.
     */
    private function resolveGroup(string $entry) : array
    {
        // Recursively resolve the group's middleware entries using the group resolver.
        return $this->resolve(middleware: $this->groupResolver->resolveGroup(entry: $entry));
    }
}