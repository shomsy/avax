<?php

declare(strict_types=1);

namespace Avax\Container\Exceptions;

use ReflectionProperty;
use RuntimeException;

/**
 * Exception thrown when an invalid property injection is detected.
 *
 * This exception is designed to communicate issues related to injecting dependencies into
 * a class property. It provides additional context about the problematic property for
 * debugging and resolution purposes.
 *
 * @package Avax\Container\Exception
 */
class InvalidInjectionException extends RuntimeException
{
    /**
     * The name of the property where injection failed.
     *
     * This property stores the exact name of the class property that caused the injection
     * error, aiding in debugging and resolution.
     *
     * @var string
     */
    private string $propertyName;

    /**
     * Constructs a new InvalidInjectionException instance.
     *
     * This constructor accepts a `ReflectionProperty` representing the property
     * where the injection failed and a custom error message.
     *
     * @param ReflectionProperty $property The reflection of the property where injection failed.
     * @param string             $message  A detailed descriptive error message explaining the cause.
     */
    public function __construct(ReflectionProperty $property, string $message)
    {
        // Assigns the problematic property's name to the private property for future reference.
        $this->propertyName = $property->getName();

        // Calls the parent RuntimeException constructor with a named argument for the error message.
        parent::__construct(message: $message);
    }

    /**
     * Retrieves the name of the property causing the injection error.
     *
     * This method provides clients with access to the name of the property that caused
     * the exception, allowing for targeted debugging or error reporting.
     *
     * @return string The name of the problematic property.
     */
    public function getPropertyName() : string
    {
        return $this->propertyName;
    }
}