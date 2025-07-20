<?php

declare(strict_types=1);

/**
 * An attribute class applied to a property to ensure the property value is not
 * within a specified set of values. This uses a 'NotIn' validation rule.
 *
 * This class is marked as 'readonly' to signify that it should not be modified
 * after instantiation, enhancing its immutability and ensuring integrity.
 */

namespace Gemini\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Gemini\Exceptions\ValidationException;

/**
 * Attribute class used to enforce that a property value is not within a specified array of values.
 *
 * The readonly modifier ensures that instances of this class are immutable once constructed.
 * This design choice prevents accidental changes to the array of invalid values after instantiation,
 * which is critical for maintaining consistent validation rules.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
readonly class NotIn
{
    public function __construct(private array $values) {}

    /**
     * @throws \Gemini\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property) : void
    {
        if (in_array($value, $this->values, true)) {
            throw new ValidationException(message: $property . ' must not be one of: ' . implode(', ', $this->values));
        }
    }
}
