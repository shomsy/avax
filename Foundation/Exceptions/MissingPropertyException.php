<?php

declare(strict_types=1);

namespace Avax\Exceptions;

use Exception;
use Override;

/**
 * Class MissingPropertyException
 *
 * Thrown when a required property is missing from the data provided to a DTO.
 *
 * @deprecated Move to Foundation/DataHandling/DataTransfer/Exceptions/
 */
class MissingPropertyException extends Exception
{
    /**
     * MissingPropertyException constructor.
     *
     * @param string $propertyName The name of the missing property.
     */
    #[Override]
    public function __construct(string $propertyName)
    {
        $message = sprintf("The property '%s' is required but missing in the data.", $propertyName);
        parent::__construct(message: $message);
    }
}
