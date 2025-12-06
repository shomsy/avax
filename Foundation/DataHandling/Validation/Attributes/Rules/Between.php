<?php

declare(strict_types=1);

/**
 * Attribute class to impose a "between" validation rule on a property.
 *
 * This attribute is intended to be used on properties that need to ensure
 * their values lie between a specified minimum and maximum range.
 *
 * The class is marked as read-only to prevent changes to the min and max
 * values after instantiation, ensuring the integrity of the validation rule.
 */

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;

/**
 * Attribute class for enforcing that a property's value is within a specified range.
 *
 * Modifiers:
 * - This class is read-only to ensure immutability once it is constructed.
 * - It is intended to be used as a property attribute.
 *
 * Use this class to validate that a given property falls within a specific minimum and maximum range.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
readonly class Between
{
    public function __construct(private int $min, private int $max) {}

    /**
     * @throws \Avax\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property) : void
    {
        if ($value < $this->min || $value > $this->max) {
            throw new ValidationException(
                message: sprintf(
                             '%s must be between %d and %d.',
                             $property,
                             $this->min,
                             $this->max,
                         ),
            );
        }
    }
}
