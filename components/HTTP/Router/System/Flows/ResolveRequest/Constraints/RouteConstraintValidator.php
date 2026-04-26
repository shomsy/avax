<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\System\Flows\ResolveRequest\Constraints;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;
use Avax\HTTP\Router\System\Foundation\Exceptions\InvalidConstraintException;
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
     * @throws InvalidConstraintException If constraint pattern is invalid.
     * @throws RuntimeException If constraint validation fails.
     */
    public function validate(RouteDefinition $route, ServerRequest $request) : void
    {
        foreach ($route->constraints as $param => $pattern) {
            // Validate constraint pattern syntax first
            $this->validateConstraintPattern(pattern: $pattern);

            $value = $request->getAttribute(name: $param);

            if (! is_string(value: $value) && ! is_numeric(value: $value)) {
                continue;
            }

            // Escape delimiters in pattern to prevent regex injection
            $escapedPattern = preg_quote(str: $pattern, delimiter: '/');
            $fullPattern    = "/^{$escapedPattern}$/";

            $matchResult = preg_match(pattern: $fullPattern, subject: (string) $value);

            if ($matchResult === false) {
                throw new InvalidConstraintException(pattern: $pattern, reason: 'regex compilation failed');
            }

            if ($matchResult !== 1) {
                throw new RuntimeException(
                    message: sprintf('Route parameter "%s" failed constraint "%s"', $param, $pattern)
                );
            }
        }
    }

    /**
     * Validates that a constraint pattern is syntactically correct.
     *
     * @param string $pattern The regex pattern to validate.
     *
     * @throws InvalidConstraintException If pattern is invalid.
     */
    private function validateConstraintPattern(string $pattern) : void
    {
        // Test pattern compilation
        $testPattern = "/{$pattern}/";
        $error       = null;

        set_error_handler(callback: static function ($errno, $errstr) use (&$error) {
            $error = $errstr;
        });

        $result = preg_match(pattern: $testPattern, subject: '');

        restore_error_handler();

        if ($result === false || $error !== null) {
            throw new InvalidConstraintException(pattern: $pattern, reason: $error ?: 'invalid regex syntax');
        }
    }
}
