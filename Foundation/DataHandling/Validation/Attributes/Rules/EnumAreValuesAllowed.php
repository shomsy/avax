<?php

declare(strict_types=1);

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;

/**
 * EnumAreValuesAllowed Attribute
 *
 * This validation attribute ensures that a property is an array containing only
 * valid values of a specified Enum class. It applies validation rules during runtime and
 * supports declarative validation of Data Transfer Objects (DTOs) in a clean and DDD-friendly way.
 *
 * Example usage:
 * ```
 * #[EnumAreValuesAllowed(MyEnum::class)]
 * private array $myProperty;
 * ```
 *
 * - The `readonly` modifier ensures immutability after instantiation.
 * - The attribute works only on class properties (`TARGET_PROPERTY`).
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
readonly class EnumAreValuesAllowed
{
    /**
     * Fully qualified class name of the Enum to validate against.
     *
     * @var string $enumClass The expected Enum class, which must implement `BackedEnum` to support `tryFrom`.
     */
    public function __construct(
        private string $enumClass,
        private bool   $strict = false // disallow null if strict = true
    ) {}

    /**
     * Validates whether the given value is an array of valid Enum instances or Enum-backed values.
     *
     * @param mixed  $value    The property value to validate.
     * @param string $property The name of the property (used for exception messages).
     *
     * @throws ValidationException If the validation fails due to:
     *                              - The value not being an array.
     *                              - The array containing invalid Enum values.
     */
    public function validate(mixed $value, string $property) : void
    {
        if ($value === null) {
            return; // ⬅️ Null is valid — ignore further validation
        }

        // Ensure the value is an array
        if (! is_array($value)) {
            throw new ValidationException(
                message: "{$property} must be an array of {$this->enumClass}"
            );
        }

        // Iterate through the array to validate each element
        foreach ($value as $v) {
            if ($v === null) {
                throw new ValidationException(message: "{$property} must not contain null values");
            }

            $resolved = is_object($v) ? $v : ($this->enumClass)::tryFrom($v);

            if (! $resolved instanceof $this->enumClass) {
                throw new ValidationException(
                    message: "{$property} contains invalid enum value: " . var_export($v, true)
                );
            }
        }
    }

    /**
     * Applies the Enum resolution to each element of the value if valid.
     *
     * This method accepts an array of Enum-backed values or Enum instances and ensures
     * that all elements are converted into instances of the specified Enum class.
     *
     * @param mixed $value The property value to process.
     *
     * @return array|null Returns an array of Enum instances if the input is valid, null otherwise.
     */
    public function apply(mixed $value) : array|null
    {
        // Resolve each array element to its respective Enum instance, or return null if not an array
        return is_array($value)
            ? array_map(
                fn($v) => is_object($v) ? $v : ($this->enumClass)::tryFrom($v),
                $value
            )
            : null;
    }
}