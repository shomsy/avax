<?php

declare(strict_types=1);

namespace Avax\Components\Router\System\Flows\ResolveRequest\Constraints;

/**
 * Validates route parameters against defined regex constraints.
 */
final readonly class RouteConstraintValidator
{
    public function validate(array $parameters, array $constraints) : bool
    {
        foreach ($constraints as $param => $pattern) {
            if (isset($parameters[$param]) && ! preg_match('#^' . $pattern . '$#u', (string) $parameters[$param])) {
                return false;
            }
        }

        return true;
    }
}
