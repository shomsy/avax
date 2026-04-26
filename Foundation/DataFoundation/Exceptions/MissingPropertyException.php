<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Exceptions;

use Exception;

/**
 * Thrown when required data is absent from the input provided to a DTO constructor.
 */
class MissingPropertyException extends Exception
{
    /**
     * MissingPropertyException constructor.
     *
     * @param string $propertyName The name of the missing property.
     */
    public function __construct(string $propertyName)
    {
        $message = sprintf("The property '%s' is required but missing in the data.", $propertyName);
        parent::__construct(message: $message);
    }
}
