<?php

declare(strict_types=1);

/**
 * This class is an attribute that enforces a specific date format on a property.
 *
 * The 'readonly' keyword ensures immutability, making sure that once the attribute
 * is instantiated, its properties cannot be modified.
 *
 * Applied for property-level validation.
 */

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;
use DateTime;

/**
 * The DateFormat class is a read-only attribute used to enforce a specific date format on a property.
 *
 * It leverages PHP's native DateTime class to attempt parsing the string into a date
 * according to the specified format. If the parsing fails, or if the parsed date does not match
 * the original string, a ValidationException is thrown. This is crucial for ensuring date fields
 * consistently conform to expected formats, which can help prevent errors related to date handling.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
readonly class DateFormat
{
    public function __construct(private string $format) {}

    /**
     * @throws \Avax\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property) : void
    {
        $date = DateTime::createFromFormat(format: $this->format, datetime: $value);
        if (! $date || $date->format(format: $this->format) !== $value) {
            throw new ValidationException(
                message: sprintf('%s does not match the format %s.', $property, $this->format),
            );
        }
    }
}
