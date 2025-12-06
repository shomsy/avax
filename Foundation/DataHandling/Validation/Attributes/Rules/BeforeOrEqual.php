<?php

declare(strict_types=1);

/**
 * Attribute class that ensures a property is a date before or equal to a specified date.
 *
 * This attribute can be applied to properties and ensures that their value is a date
 * formatted as 'Y-m-d' that is before or equal to the date specified during instantiation.
 */

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use DateTime;
use Avax\Exceptions\ValidationException;

/**
 * This attribute class enforces that a given date must be before or equal to a specified date.
 *
 * The readonly modifier ensures immutability, providing a safeguard against accidental changes to the date property.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
readonly class BeforeOrEqual
{
    public function __construct(private string $date) {}

    /**
     * @throws \Avax\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property) : void
    {
        $inputDate      = DateTime::createFromFormat(format: 'Y-m-d', datetime: $value);
        $comparisonDate = DateTime::createFromFormat(format: 'Y-m-d', datetime: $this->date);

        if (! $inputDate || ! $comparisonDate || $inputDate > $comparisonDate) {
            throw new ValidationException(
                message: sprintf(
                             '%s must be a date before or equal to %s.',
                             $property,
                             $this->date,
                         ),
            );
        }
    }
}
