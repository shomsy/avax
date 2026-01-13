<?php

declare(strict_types=1);

/**
 * Attribute class to enforce date validation rules.
 *
 * This Attribute can only be applied to properties.
 * It checks if a given date value is before a specified date.
 */

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;
use DateTime;

/**
 * Attribute to validate if a given date is before a specified date.
 *
 * The "Before" class is a read-only attribute designed to enforce a date validation rule.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
readonly class Before
{
    public function __construct(private string $date) {}

    /**
     * @throws \Avax\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property): void
    {
        $inputDate = DateTime::createFromFormat(format: 'Y-m-d', datetime: $value);
        $comparisonDate = DateTime::createFromFormat(format: 'Y-m-d', datetime: $this->date);

        if (! $inputDate || ! $comparisonDate || $inputDate >= $comparisonDate) {
            throw new ValidationException(message: sprintf('%s must be a date before %s.', $property, $this->date));
        }
    }
}
