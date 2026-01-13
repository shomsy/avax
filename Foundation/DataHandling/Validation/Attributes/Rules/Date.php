<?php

declare(strict_types=1);

/**
 * Includes a date validation attribute which targets properties.
 * Ensures that property values conform to date formats 'Y-m-d' or 'Y-m-d H:i:s'.
 *
 * This attribute can be instantiated and used to provide a clear validation rule
 * for date properties, simplifying validation logic in the broader application.
 */

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;
use DateTime;

/**
 * This class is an attribute used to validate date properties.
 *
 * It ensures that the value assigned to a property is a valid date
 * in the standard formats 'Y-m-d' or 'Y-m-d H:i:s'. If the value
 * does not conform to these formats, a ValidationException is thrown.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
class Date
{
    /**
     * @throws \Avax\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property): void
    {
        if (! DateTime::createFromFormat(format: 'Y-m-d', datetime: $value) && ! DateTime::createFromFormat(
            format  : 'Y-m-d H:i:s',
            datetime: $value,
        )) {
            throw new ValidationException(message: $property.' is not a valid date.');
        }
    }
}
