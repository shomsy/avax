<?php

declare(strict_types=1);

namespace Avax\Container\Exceptions;

/**
 * Thrown when attempting to instantiate a class that is not instantiable.
 */
class ClassNotInstantiableException extends \RuntimeException
{
    public function __construct(string $className, int $code = 0, \Throwable|null $previous = null)
    {
        parent::__construct(
            message: sprintf("Class '%s' is not instantiable. Ensure it is not abstract or an interface.", $className),
            code: $code,
            previous: $previous
        );
    }
}