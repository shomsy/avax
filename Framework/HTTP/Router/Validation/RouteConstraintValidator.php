<?php

declare(strict_types=1);

namespace Gemini\HTTP\Router\Validation;

use Gemini\HTTP\Request\Request;
use Gemini\HTTP\Router\Routing\RouteDefinition;
use RuntimeException;

/**
 * Validates route parameter values against registered regex constraints.
 */
final class RouteConstraintValidator
{
    /**
     * Validates the route parameter constraints against the actual request attributes.
     *
     * @param RouteDefinition $route   The route being validated.
     * @param Request         $request The current HTTP request.
     *
     * @return void
     *
     * @throws RuntimeException If any constraint fails.
     */
    public function validate(RouteDefinition $route, Request $request) : void
    {
        foreach ($route->constraints as $param => $pattern) {
            $value = $request->getAttribute(name: $param);

            if (! is_string($value) && ! is_numeric($value)) {
                continue;
            }

            if (@preg_match(pattern: $pattern, subject: (string) $value) !== 1) {
                throw new RuntimeException(
                    message: sprintf('Route parameter "%s" failed constraint "%s"', $param, $pattern)
                );
            }
        }
    }
}
