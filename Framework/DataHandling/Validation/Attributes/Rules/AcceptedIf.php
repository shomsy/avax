<?php

declare(strict_types=1);

/**
 * Attribute class to validate if a property value is "accepted" conditionally based on another field's value.
 *
 * The 'AcceptedIf' attribute ensures the given property is validated as accepted (with specific valid values)
 * if a condition on a another field's value is met. This allows for conditional validation logic to be applied
 * on data transfer objects.
 *
 * The class is marked readonly to indicate that instances should have immutable properties. This ensures
 * consistency and reliability of the validation logic once an instance is created with a specific condition.
 */

namespace Gemini\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Gemini\Exceptions\ValidationException;

/**
 * Validates that a property is "accepted" if a certain condition in the data is met.
 *
 * The "acceptance" means that the value should be one of 'yes', 'on', 1, or true. If the condition specified
 * by conditionField and conditionValue is met and the value is not one of these acceptable values,
 * a ValidationException is thrown.
 *
 * @throws \Gemini\Exceptions\ValidationException
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
readonly class AcceptedIf
{
    public function __construct(private string $conditionField, private mixed $conditionValue) {}

    /**
     * @throws \Gemini\Exceptions\ValidationException
     */
    public function validate(mixed $value, array $data, string $property) : void
    {
        if (($data[$this->conditionField] ?? null) === $this->conditionValue
            && ! in_array(
                $value,
                ['yes', 'on', 1, true],
                true,
            )) {
            throw new ValidationException(message: $property . " must be accepted.");
        }
    }
}
