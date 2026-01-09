<?php

declare(strict_types=1);

namespace Avax\HTTP\Middleware;

use Avax\Container\Config\Settings;
use RuntimeException;

/**
 * Resolves middleware group aliases into concrete middleware class lists.
 *
 * Responsible for handling middleware group definitions and providing
 * functionality to resolve those groups into their respective middleware chains.
 */
final readonly class MiddlewareGroupResolver
{
    private array $config;

    public function __construct(Settings $configRepository)
    {
        $this->config = $configRepository->get(
            key    : 'middleware',
            default: ['groups' => []]
        );

        if (! isset($this->config['groups']) || ! is_array(value: $this->config['groups'])) {
            $this->config['groups'] = [];
        }

        $this->validateConfig(config: $this->config);
    }

    /**
     * Validates the initial configuration to detect early misconfigurations.
     *
     * @param array $config The middleware configuration data to validate.
     *
     * @throws RuntimeException If the configuration is invalid.
     */
    private function validateConfig(array $config) : void
    {
        if (! isset($config['groups']) || ! is_array(value: $config['groups'])) {
            throw new RuntimeException(message: 'Middleware configuration must contain a "groups" array.');
        }

        foreach ($config['groups'] as $groupName => $middlewares) {
            if (! is_string(value: $groupName)) {
                throw new RuntimeException(message: 'Middleware group names must be strings.');
            }

            if (! is_array(value: $middlewares)) {
                throw new RuntimeException(message: "Middleware group [{$groupName}] must be an array.");
            }
        }
    }

    /**
     * Resolves a middleware group name to its list of middleware classes.
     *
     * @param string $entry Middleware group alias (e.g. 'web', 'api').
     *
     * @return array<class-string> List of fully qualified middleware class names.
     *
     * @throws RuntimeException If the middleware group does not exist or is invalid.
     */
    public function resolveGroup(string $entry) : array
    {
        if (! $this->hasGroup(group: $entry)) {
            throw new RuntimeException(message: "Middleware group [{$entry}] does not exist.");
        }

        $group = $this->config['groups'][$entry];

        if (! is_array(value: $group)) {
            throw new RuntimeException(message: "Middleware group [{$entry}] must be an array.");
        }

        // Ensure all entries strictly adhere to the class-string type.
        foreach ($group as $middleware) {
            if (! is_string(value: $middleware) || ! class_exists(class: $middleware)) {
                throw new RuntimeException(
                    message: "Invalid middleware [{$middleware}] in group [{$entry}]. Must be a valid class name."
                );
            }
        }

        return $group;
    }

    /**
     * Checks if a middleware group alias is defined in the configuration.
     *
     * @param string $group Middleware group alias to check.
     *
     * @return bool True if the group exists, false otherwise.
     */
    public function hasGroup(string $group) : bool
    {
        return isset($this->config['groups'][$group]);
    }
}
