<?php

declare(strict_types=1);

/**
 * Attribute to enforce that a date property must be after a specified date.
 *
 * The After attribute can be applied to properties to ensure the date value
 * assigned to the property is after a predefined date. This is particularly
 * useful in scenarios where certain events must occur after a specific date.
 */

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;
use DateTime;

/**
 * Validates that the provided date is after the date specified during instantiation.
 *
 * @throws \Avax\Exceptions\ValidationException if the input date is not after the comparison date.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
readonly class After
{
    public function __construct(private string $date) {}

    /**
     * @throws \Avax\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property): void
    {
        $inputDate = DateTime::createFromFormat(format: 'Y-m-d', datetime: $value);
        $comparisonDate = DateTime::createFromFormat(format: 'Y-m-d', datetime: $this->date);

        if (! $inputDate || ! $comparisonDate || $inputDate <= $comparisonDate) {
            throw new ValidationException(message: sprintf('%s must be a date after %s.', $property, $this->date));
        }
    }
}
