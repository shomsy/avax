<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Flows\GenerateUrl;

use Avax\Components\HTTP\Router\System\Foundation\Failure\RouterFailure;

/**
 * Generates URLs from named routes with parameter substitution.
 *
 * Usage:
 *   $generator = new GenerateUrl();
 *   $generator->addRoute('user.profile', '/users/{id}/profile');
 *   $url = $generator->execute('user.profile', ['id' => 42]);
 *   // Returns: /users/42/profile
 */
final class GenerateUrl
{
    /** @var array<string, string> Map of route name => pattern */
    private array $routes = [];

    /** @var array<string, mixed> Default parameters */
    private array $defaults = [];

    /**
     * Register a named route pattern.
     */
    public function addRoute(string $name, string $pattern) : void
    {
        $this->routes[$name] = $pattern;
    }

    /**
     * Set default parameters for URL generation.
     *
     * @param array<string, mixed> $defaults
     */
    public function setDefaults(array $defaults) : void
    {
        $this->defaults = $defaults;
    }

    /**
     * Generate a URL from a named route.
     *
     * @param string               $name  Route name
     * @param array<string, mixed> $params Route parameters to substitute
     * @param array<string, mixed> $extra Query string parameters
     *
     * @throws RouterFailure If the route is not found or parameters are missing
     */
    public function execute(string $name, array $params = [], array $extra = []) : string
    {
        if (! isset($this->routes[$name])) {
            throw new RouterFailure("Route '{$name}' is not registered for URL generation");
        }

        $pattern = $this->routes[$name];
        $params  = array_merge($this->defaults, $params);

        // Substitute path parameters
        $url = (string) preg_replace_callback(
            '/\{(\w+)\}/',
            static function (array $matches) use ($params, $name) : string {
                $param = $matches[1];
                if (! array_key_exists($param, $params)) {
                    throw new RouterFailure(
                        "Missing required parameter '{$param}' for route '{$name}'",
                    );
                }

                return (string) $params[$param];
            },
            $pattern,
        );

        // Append query string from extra parameters
        if ($extra !== []) {
            $query = http_build_query($extra);
            if ($query !== '') {
                $url .= '?' . $query;
            }
        }

        return $url;
    }

    /**
     * Check if a route name exists.
     */
    public function hasRoute(string $name) : bool
    {
        return isset($this->routes[$name]);
    }

    /**
     * Get all registered routes.
     *
     * @return array<string, string>
     */
    public function getRoutes() : array
    {
        return $this->routes;
    }
}
