<?php

declare(strict_types=1);

/**
 * Attribute class to define a validation rule for the exact size of a property's value.
 *
 * This class is marked readonly to enforce immutability after instantiation, ensuring
 * the consistency of the size constraint.
 */

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;

/**
 * Attribute class for enforcing fixed-size length constraints on properties.
 * Applied at the property level to validate that a property's length matches the specified size.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
readonly class Size
{
    public function __construct(private int $size) {}

    /**
     * @throws \Avax\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property): void
    {
        if (strlen(string: (string) $value) !== $this->size) {
            throw new ValidationException(
                message: sprintf('%s must be exactly %d characters.', $property, $this->size),
            );
        }
    }
}
