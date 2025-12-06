<?php

declare(strict_types=1);

namespace Avax\Container\Exceptions;

/**
 * Thrown when a dependency cannot be resolved due to missing type hints or invalid configuration.
 */
class UnresolvableDependencyException extends \RuntimeException
{
    public function __construct(\ReflectionParameter $reflectionParameter, int $code = 0, \Throwable|null $previous = null)
    {
        $message = sprintf(
            "Cannot resolve dependency '%s' in parameter '%s' of function/method '%s'.",
            $reflectionParameter->getType()?->getName() ?? 'unknown type',
            $reflectionParameter->getName(),
            $reflectionParameter->getDeclaringFunction()->getName() ?? 'unknown function'
        );

        parent::__construct(message: $message, code: $code, previous: $previous);
    }
}