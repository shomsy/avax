<?php

declare(strict_types=1);

/**
 * Attribute class representing an "Accepted" validation rule.
 *
 * This class can be used as an attribute to ensure that a property
 * has an acceptable value such as 'yes', 'on', 1, or true.
 *
 * - Flags Attribute::TARGET_PROPERTY restricts usage to class properties.
 * - Throws ValidationException if the value does not meet acceptable criteria.
 */

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;

/**
 * This attribute class enforces that a property must be explicitly accepted.
 * It's used for properties where a confirmation or acknowledgment is required.
 * The acceptable values are 'yes', 'on', 1, or true.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
class Accepted
{
    /**
     * @throws \Avax\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property) : void
    {
        if (! in_array($value, ['yes', 'on', 1, true], true)) {
            throw new ValidationException(message: $property . ' must be accepted.');
        }
    }
}
