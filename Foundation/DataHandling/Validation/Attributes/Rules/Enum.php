<?php

declare(strict_types=1);

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;
use BackedEnum;
use UnitEnum;

/**
 * Enum Validator Attribute
 *
 * Ensures that the provided value matches the specified enum type.
 * Supports both:
 * - Standard enum instances.
 * - Scalar values mapping to `BackedEnum` values.
 * Throws a validation exception when the value violates the constraint.
 *
 * Use attribute declaration to enable declarative validations for Data Objects.
 * Complies with strict type safety and clean code principles.
 *
 * @template T of UnitEnum
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
readonly class Enum
{
    /**
     * Initializes the Enum Validator Attribute.
     *
     * @param  class-string<T>  $enumClass  The fully qualified class name of the target enum.
     */
    public function __construct(private string $enumClass) {}

    /**
     * Validates whether the provided value is a valid enum instance or a scalar value
     * that can be mapped to a `BackedEnum`.
     *
     * @param  mixed  $value  The value being validated; expected to be an enum instance or backed value.
     * @param  string  $property  The name of the property being validated (for meaningful exception messages).
     *
     * @throws ValidationException When the value does not match the expected enum or backed enum type.
     */
    public function validate(mixed $value, string $property): void
    {
        // Check if the provided class exists and is a valid enum.
        if (! enum_exists(enum: $this->enumClass)) {
            // Throws an exception when the class does not exist or is not declared as an enum.
            throw new ValidationException(
                message: "Enum class '{$this->enumClass}' does not exist."
            );
        }

        // If the value is already an instance of the specified enum, accept it as valid.
        if ($value instanceof $this->enumClass) {
            return; // Validation passes with no further checks needed.
        }

        // If the value is scalar, validate its compatibility with BackedEnum.
        if (is_scalar(value: $value) && is_subclass_of(object_or_class: $this->enumClass, class: BackedEnum::class)) {
            // Extract all scalar values (backed values) from the enum cases.
            $values = array_column(array: $this->enumClass::cases(), column_key: 'value');

            // If the scalar value matches one of the allowed enum backed values, validation passes.
            if (in_array(needle: $value, haystack: $values, strict: true)) {
                return; // Validation passes; exit early.
            }

            // Throw an exception if the scalar value does not match any of the allowed backed values.
            throw new ValidationException(
                message: "{$property} must be one of: ".implode(separator: ', ', array: $values)
            );
        }

        // Fallback: Reject any other types (e.g., arrays, objects without compatibility).
        throw new ValidationException(message: "{$property} must be a valid enum of type {$this->enumClass}");
    }
}
