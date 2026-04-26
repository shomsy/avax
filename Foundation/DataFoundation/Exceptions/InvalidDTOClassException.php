<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Exceptions;

use InvalidArgumentException;

/**
 * Thrown when a specified DTO class does not exist or cannot be instantiated.
 */
class InvalidDTOClassException extends InvalidArgumentException
{
    /**
     * InvalidDTOClassException constructor.
     *
     * @param string $className The name of the invalid DTO class.
     */
    public function __construct(string $className)
    {
        $message = sprintf("The class '%s' is not a valid DTO class.", $className);
        parent::__construct(message: $message);
    }
}
