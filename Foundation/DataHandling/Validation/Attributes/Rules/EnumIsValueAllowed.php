<?php

declare(strict_types=1);

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use BackedEnum;
use Avax\Exceptions\ValidationException;

/**
 * Attribute: EnumIsValueAllowed
 *
 * A declarative validation rule that ensures a property adheres to a specified enum constraint.
 * This attribute enforces that the annotated property either:
 * - Is already an instance of the specified Enum class.
 * - Resolves from a scalar value (if the Enum class is backed).
 *
 * If the value cannot be resolved to a valid Enum instance or scalar value, a ValidationException
 * is thrown with an appropriate error describing the issue.
 *
 * **Key Usage:**
 * - This Attribute ensures cleaner and more maintainable DTO properties within DDD contexts.
 * - Limited to `#[Attribute::TARGET_PROPERTY]` for enforcement at the property level.
 *
 * This validator relies on the PHP 8.1+ `BackedEnum` interface for backed enums.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class EnumIsValueAllowed
{
    /**
     * Constructor for the EnumIsValueAllowed Attribute.
     *
     * Leverages Constructor Promotion for concise and expressive class definition.
     *
     * @param class-string<BackedEnum> $enumClass Fully qualified Enum class name.
     *                                            This must be a valid class implementing PHP's `BackedEnum` interface.
     */
    public function __construct(private string $enumClass) {}

    /**
     * Validates the value against the specified Enum class.
     *
     * This method ensures the following:
     * - If the value is already an instance of the predefined Enum class, no further action is required.
     * - For scalar values (e.g., strings, integers), the method attempts to resolve the value into a backed Enum case.
     * - Throws a `ValidationException` if:
     *   1. The resolution fails (e.g., the provided scalar does not map to any Enum case).
     *   2. The provided value is not an Enum instance or a valid scalar convertible to a case.
     *
     * @param mixed  $value    The value to validate. This can refer to any mixed-type data.
     *                         Passed as a reference (`&`) to apply inline transformations (e.g., scalar -> Enum
     *                         conversion).
     * @param string $property The name of the property being validated.
     *                         Used to provide meaningful error messages for exceptions.
     *
     * @throws ValidationException If the value cannot be validated or resolved to the specified Enum.
     */
    public function validate(mixed &$value, string $property) : void
    {
        // Retrieve the Enum class provided in the attribute.
        $enumClass = $this->enumClass;

        // Step 1: If the value is already an instance of the given Enum class, validation succeeds.
        if ($value instanceof $enumClass) {
            return;
        }

        // Step 2: If the value is a scalar (e.g., string, int), check for compatibility with backed Enums.
        if (is_scalar($value) && is_subclass_of($enumClass, BackedEnum::class)) {
            // Attempt to resolve the scalar value into a backed Enum case using 'tryFrom'.
            $resolved = $enumClass::tryFrom($value);

            // If the value was successfully resolved, update the reference and exit.
            if ($resolved !== null) {
                $value = $resolved;

                return;
            }

            // Step 3: If resolution failed, enumerate all possible backed values for error clarity.
            $allowed = implode(
                ', ',
                array_map(
                    static fn(BackedEnum $e) => $e->value, // Extract each Enum's value.
                    $enumClass::cases() // Retrieve all cases for the Enum.
                )
            );

            // Throw an exception with the allowed values for better debugging and usage feedback.
            throw new ValidationException(
                message: "{$property} must be one of enum {$enumClass}: {$allowed}"
            );
        }

        // Step 4: If the value is neither a valid Enum instance nor a valid scalar convertible to an Enum, throw an exception.
        throw new ValidationException(
            message: "{$property} must be an instance or value of {$enumClass}"
        );
    }
}