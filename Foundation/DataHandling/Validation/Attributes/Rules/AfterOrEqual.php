<?php

declare(strict_types=1);

/**
 * Attribute to enforce a property to be a date after or equal to a specified date.
 *
 * This attribute is applied at the property level within a Data Transfer Object (DTO).
 * It ensures that the validated date is not earlier than the provided comparison date.
 */

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;
use DateTime;

/**
 * Attribute class designed to enforce the rule that a given date must be
 * either after or equal to a specified date. This validation is used
 * in scenarios where certain business rules require dates to respect
 * a minimum threshold.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
readonly class AfterOrEqual
{
    public function __construct(private string $date) {}

    /**
     * @throws \Avax\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property) : void
    {
        $inputDate      = DateTime::createFromFormat(format: 'Y-m-d', datetime: $value);
        $comparisonDate = DateTime::createFromFormat(format: 'Y-m-d', datetime: $this->date);

        if (! $inputDate || ! $comparisonDate || $inputDate < $comparisonDate) {
            throw new ValidationException(
                message: sprintf(
                    '%s must be a date after or equal to %s.',
                    $property,
                    $this->date,
                ),
            );
        }
    }
}
