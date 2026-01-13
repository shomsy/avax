<?php

declare(strict_types=1);

/**
 * Attribute to enforce a digit-based range constraint on a property.
 *
 * This Attribute is declared as read-only and targets properties.
 * It ensures that the value of the property contains only digits and
 * that the number of digits falls within the specified minimum and maximum range.
 */

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;

/**
 * Class DigitsBetween
 *
 * This class contains validation logic to ensure that a given value is a string
 * composed only of digits and that its length falls within a specified minimum
 * and maximum range.
 *
 * Marked as readonly to indicate immutability: once instantiated, the properties
 * $min and $max should not be altered.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
readonly class DigitsBetween
{
    public function __construct(private int $min, private int $max) {}

    /**
     * @throws \Avax\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property): void
    {
        if (in_array(needle: preg_match(pattern: '/^\d+$/', subject: (string) $value), haystack: [0, false], strict: true) || strlen(
            string: (string) $value,
        ) < $this->min || strlen(
            string: (string) $value,
        ) > $this->max) {
            throw new ValidationException(
                message: sprintf(
                    '%s must be between %d and %d digits.',
                    $property,
                    $this->min,
                    $this->max,
                ),
            );
        }
    }
}
