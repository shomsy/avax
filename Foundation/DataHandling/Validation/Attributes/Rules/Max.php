<?php

declare(strict_types=1);

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;
use Countable;
use InvalidArgumentException;

/**
 * Attribute to enforce a maximum value or length constraint on a property.
 *
 * Supports validation for strings, numerics, arrays, and countable objects.
 **/
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
class Max
{
    /** Error messages for different validation types. */
    private const string ERROR_NUMERIC = 'The value of "%s" must not exceed %d.';

    private const string ERROR_STRING = 'The length of "%s" must not exceed %d characters.';

    private const string ERROR_COUNTABLE = 'The number of items in "%s" must not exceed %d.';

    private const string ERROR_INVALID = 'Invalid value type for "%s". Expected a numeric, string, or countable value, but got "%s".';

    /**
     * Constructor for the Max attribute.
     *
     * @param int         $max     The maximum value or size.
     * @param string|null $message Optional custom error message.
     *
     * @throws InvalidArgumentException If the max value provided is negative.
     */
    public function __construct(private readonly int $max, private readonly string|null $message = null)
    {
        // Ensure the maximum value is a non-negative integer.
        if ($max < 0) {
            throw new InvalidArgumentException(message: 'The maximum value must be a non-negative integer.');
        }
    }

    /**
     * Validates the value against the maximum constraint.
     *
     * @param mixed  $value    The value to validate.
     * @param string $property The name of the property being validated.
     *
     * @throws ValidationException If validation fails.
     **/
    public function validate(mixed $value, string $property) : void
    {
        // Allow null values (no validation required for null).
        if ($value === null) {
            return;
        }

        // Match the type of value to the appropriate validation method.
        match (true) {
            is_numeric(value: $value)   => $this->validateNumeric(
                value   : (float) $value,
                property: $property
            ),
            is_string(value: $value)    => $this->validateString(
                value   : $value,
                property: $property
            ),
            is_countable(value: $value) => $this->validateCountable(
                value   : $value,
                property: $property
            ),
            default                     => $this->throwValidationException(
                errorKey: self::ERROR_INVALID,
                property: $property,
                value   : $value
            ),
        };
    }

    /**
     * Validates numeric values against the maximum constraint.
     *
     * @param float  $value    The numeric value to validate.
     * @param string $property The name of the property being validated.
     *
     * @throws ValidationException If the value exceeds the maximum.
     */
    private function validateNumeric(float $value, string $property) : void
    {
        if ($value > $this->max) {
            // The Numeric value exceeds the maximum allowed.
            $this->throwValidationException(errorKey: self::ERROR_NUMERIC, property: $property, value: $value);
        }
    }

    /**
     * Throws a ValidationException with relevant error details.
     *
     * @param string $errorKey The error key identifying the type of validation error.
     * @param string $property The name of the property being validated.
     * @param mixed  $value    The value that caused the validation failure.
     *
     * @throws ValidationException Always triggered when called.
     */
    private function throwValidationException(string $errorKey, string $property, mixed $value) : void
    {
        // Constructs and throws a detailed ValidationException.
        throw new ValidationException(
            message : $this->message ?? sprintf('%s must be at most %d.', ucfirst(string: $property), $this->max),
            metadata: [
                'property' => $property,
                'value'    => $value,
                'max'      => $this->max,
                'errorKey' => $errorKey,
            ]
        );
    }

    /**
     * Validates string values against the maximum length constraint.
     *
     * @param string $value    The string value to validate.
     * @param string $property The name of the property being validated.
     *
     * @throws ValidationException If the string length exceeds the maximum.
     */
    private function validateString(string $value, string $property) : void
    {
        if (mb_strlen(string: $value) > $this->max) {
            // String length exceeds the maximum allowed.
            $this->throwValidationException(errorKey: self::ERROR_STRING, property: $property, value: $value);
        }
    }

    /**
     * Validates array or countable values against the maximum count constraint.
     *
     * @param array|Countable $value    The array or countable value to validate.
     * @param string          $property The name of the property being validated.
     *
     * @throws ValidationException If the count exceeds the maximum.
     */
    private function validateCountable(array|Countable $value, string $property) : void
    {
        // Determine the count of elements.
        $count = is_array(value: $value) ? count(value: $value) : iterator_count(iterator: $value);

        if ($count > $this->max) {
            // Element count exceeds the maximum allowed.
            $this->throwValidationException(errorKey: self::ERROR_COUNTABLE, property: $property, value: $count);
        }
    }
}
