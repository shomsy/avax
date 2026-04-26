<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Exceptions;

use Exception;

/**
 * Thrown when a DTO property is missing or structurally invalid.
 */
class InvalidPropertyException extends Exception
{
    /**
     * InvalidPropertyException constructor.
     *
     * @param string $propertyName The name of the missing or invalid property.
     */
    public function __construct(string $propertyName)
    {
        $message = sprintf("The property '%s' is missing or invalid in the DTO.", $propertyName);
        parent::__construct(message: $message);
    }
}
