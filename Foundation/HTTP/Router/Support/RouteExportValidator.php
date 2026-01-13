<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Support;

use Avax\HTTP\Router\Routing\RouteDefinition;
use Psr\Log\LoggerInterface;

/**
 * Validates route exportability for caching.
 *
 * Ensures routes contain only serializable data before caching.
 * Logs warnings for non-exportable routes and provides skip logic.
 */
final class RouteExportValidator
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    /**
     * Filters an array of routes, keeping only exportable ones.
     *
     * @param RouteDefinition[] $routes
     *
     * @return RouteDefinition[]
     */
    public function filterExportable(array $routes) : array
    {
        $exportable = [];

        foreach ($routes as $route) {
            if ($this->validate(route: $route)) {
                $exportable[] = $route;
            }
        }

        $skipped = count($routes) - count($exportable);
        if ($skipped > 0) {
            $this->logger->info(message: 'Some routes skipped during cache export', context: [
                'total_routes'      => count($routes),
                'exportable_routes' => count($exportable),
                'skipped_routes'    => $skipped,
            ]);
        }

        return $exportable;
    }

    /**
     * Validates if a route can be safely exported for caching.
     *
     * @param RouteDefinition $route The route to validate
     *
     * @return bool True if exportable, false otherwise
     */
    public function validate(RouteDefinition $route) : bool
    {
        $issues = [];

        // Check action exportability
        if (! $this->isActionExportable(action: $route->action)) {
            $issues[] = 'action contains non-serializable data (closures or objects)';
        }

        // Check middleware exportability
        if (! $this->isMiddlewareExportable(middleware: $route->middleware)) {
            $issues[] = 'middleware contains non-string values';
        }

        // Check defaults exportability
        if (! $this->isDefaultsExportable(defaults: $route->defaults)) {
            $issues[] = 'defaults contain non-scalar values';
        }

        // Check attributes exportability
        if (! $this->isAttributesExportable(attributes: $route->attributes)) {
            $issues[] = 'attributes contain non-scalar values';
        }

        // Check domain exportability
        if ($route->domain !== null && ! is_string($route->domain)) {
            $issues[] = 'domain is not a string';
        }

        // Check authorization exportability
        if ($route->authorization !== null && ! is_string($route->authorization)) {
            $issues[] = 'authorization is not a string';
        }

        if (! empty($issues)) {
            $this->logger->warning(message: 'Route cannot be cached', context: [
                'route'  => $route->method . ' ' . $route->path,
                'name'   => $route->name,
                'issues' => $issues,
            ]);

            return false;
        }

        return true;
    }

    /**
     * Validates if action can be exported.
     */
    private function isActionExportable(mixed $action) : bool
    {
        // String actions are always exportable
        if (is_string($action)) {
            return true;
        }

        // Array actions must be [class-string, method-string]
        if (is_array($action) && count($action) === 2) {
            return is_string($action[0]) && is_string($action[1]);
        }

        // Closures and other objects are not exportable
        return false;
    }

    /**
     * Validates if middleware array can be exported.
     */
    private function isMiddlewareExportable(array $middleware) : bool
    {
        foreach ($middleware as $mw) {
            if (! is_string($mw)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validates if defaults can be exported.
     */
    private function isDefaultsExportable(array $defaults) : bool
    {
        return $this->isScalarArray(array: $defaults);
    }

    /**
     * Checks if array contains only scalar values or arrays of scalars.
     */
    private function isScalarArray(array $array) : bool
    {
        foreach ($array as $value) {
            if (is_array($value)) {
                if (! $this->isScalarArray(array: $value)) {
                    return false;
                }
            } elseif (! is_scalar($value) && $value !== null) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validates if attributes can be exported.
     */
    private function isAttributesExportable(array $attributes) : bool
    {
        return $this->isScalarArray(array: $attributes);
    }
}
