<?php

declare(strict_types=1);

/**
 * Attribute class to enforce a date equality rule on a DTO property.
 *
 * This attribute ensures that a property's date value matches a specified date.
 * Useful for scenarios where a particular date needs to be strictly validated,
 * such as ensuring a creation date matches a record date.
 *
 * - Declared readonly to emphasize immutability once initialized.
 * - Can only be applied to class properties (TARGET_PROPERTY).
 */

namespace Gemini\DataHandling\Validation\Attributes\Rules;

use Attribute;
use DateTime;
use Gemini\Exceptions\ValidationException;

/**
 * Validates that the given value matches the specified date.
 *
 * The validation checks for strict equality between the input date
 * and the configured date. The date format used is 'Y-m-d'.
 *
 * @throws \Gemini\Exceptions\ValidationException if the dates do not match or if either date is invalid.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
readonly class DateEquals
{
    public function __construct(private string $date) {}

    /**
     * @throws \Gemini\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property) : void
    {
        $inputDate      = DateTime::createFromFormat(format: 'Y-m-d', datetime: $value);
        $comparisonDate = DateTime::createFromFormat(format: 'Y-m-d', datetime: $this->date);

        if (! $inputDate || ! $comparisonDate || $inputDate != $comparisonDate) {
            throw new ValidationException(message: sprintf('%s must be a date equal to %s.', $property, $this->date));
        }
    }
}
