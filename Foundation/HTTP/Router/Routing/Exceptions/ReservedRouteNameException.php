<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing\Exceptions;

use Exception;

/**
 * Exception thrown when attempting to register a route with a reserved name.
 */
class ReservedRouteNameException extends Exception
{
    public function __construct(string $name)
    {
        parent::__construct(message: "Route name '{$name}' is reserved. Names starting with '__avax.' are not allowed.");
    }
}
