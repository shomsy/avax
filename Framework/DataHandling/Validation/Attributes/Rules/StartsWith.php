<?php

declare(strict_types=1);

/**
 * Attribute to enforce that a property's value starts with one of the specified prefixes.
 * Using this attribute helps ensure consistent data formatting and validation across the application.
 *
 * The Attribute is restricted to be used on properties by the TARGET_PROPERTY flag.
 */

namespace Gemini\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Gemini\Exceptions\ValidationException;

/**
 * Attribute class for validating that a given value starts with one of the specified prefixes.
 *
 * The class uses PHP 8.0's Attributes feature to provide declarative validation rules on class properties.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
readonly class StartsWith
{
    public function __construct(private array $prefixes) {}

    /**
     * @throws \Gemini\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property) : void
    {
        foreach ($this->prefixes as $prefix) {
            if (str_starts_with((string) $value, (string) $prefix)) {
                return;
            }
        }

        throw new ValidationException(
            message: $property . ' must start with one of the following: ' . implode(', ', $this->prefixes),
        );
    }
}
