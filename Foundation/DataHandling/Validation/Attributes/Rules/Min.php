<?php

declare(strict_types=1);

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Countable;
use Avax\Exceptions\ValidationException;
use InvalidArgumentException;

/**
 * Attribute to enforce a minimum value or length constraint on a property.
 *
 * Supports numeric values, strings, and countable objects/arrays.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
class Min
{
    private const string ERROR_NUMERIC   = 'The value of "%s" must be at least %d.';

    private const string ERROR_STRING    = 'The length of "%s" must be at least %d characters.';

    private const string ERROR_COUNTABLE = 'The number of items in "%s" must be at least %d.';

    private const string ERROR_INVALID   = 'Invalid value type for "%s". Expected a numeric, string, or countable value, but got "%s".';


    /**
     * Constructor for the Min attribute.
     *
     * @param int         $min     The minimum value or size.
     * @param string|null $message Optional custom error message.
     */
    public function __construct(private readonly int $min, private readonly string|null $message = null)
    {
        if ($min < 0) {
            throw new InvalidArgumentException(message: 'The minimum value must be a non-negative integer.');
        }
    }

    /**
     * Validates the value against the minimum constraint.
     *
     * @param mixed  $value    The value to validate.
     * @param string $property The name of the property being validated.
     *
     * @throws ValidationException If validation fails.
     */
    public function validate(mixed $value, string $property) : void
    {
        if ($value === null) {
            return; // Allow nulls; other validators handle required constraints.
        }

        match (true) {
            is_numeric($value)                              => $this->validateNumeric(
                value   : (float) $value,
                property: $property
            ),
            is_string($value)                               => $this->validateString(
                value   : $value,
                property: $property
            ),
            is_countable($value) => $this->validateCountable(
                value   : $value,
                property: $property
            ),
            default                                         => $this->throwValidationException(
                errorKey: self::ERROR_INVALID,
                property: $property,
                value   : $value
            ),
        };
    }

    private function validateNumeric(float $value, string $property) : void
    {
        if ($value < $this->min) {
            $this->throwValidationException(errorKey: self::ERROR_NUMERIC, property: $property, value: $value);
        }
    }

    private function throwValidationException(string $errorKey, string $property, mixed $value) : void
    {
        throw new ValidationException(
            message : $this->message ?? sprintf('%s must be at least %d.', ucfirst($property), $this->min),
            metadata: [
                          'property' => $property,
                          'value'    => $value,
                          'min'      => $this->min,
                          'errorKey' => $errorKey,
                      ]
        );
    }

    private function validateString(string $value, string $property) : void
    {
        if (mb_strlen($value) < $this->min) {
            $this->throwValidationException(errorKey: self::ERROR_STRING, property: $property, value: $value);
        }
    }

    private function validateCountable(array|Countable $value, string $property) : void
    {
        $count = is_array($value) ? count($value) : iterator_count($value);

        if ($count < $this->min) {
            $this->throwValidationException(errorKey: self::ERROR_COUNTABLE, property: $property, value: $count);
        }
    }
}
