<?php

declare(strict_types=1);

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;

/**
 * Attribute to enforce that a property value must be a float.
 *
 * This attribute validates that the value of a property is a float. It is
 * primarily used in DTOs to ensure data integrity and type safety.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
class FloatRule
{
    /**
     * Validation error message template.
     */
    private const string ERROR_MESSAGE = 'The "%s" field must be a valid float.';

    /**
     * Constructor for the FloatRule attribute.
     */
    public function __construct(private readonly string|null $message = null) {}

    /**
     * Validates that the provided value is a float or numeric.
     *
     * @param mixed  $value    The value to validate.
     * @param string $property The name of the property being validated.
     *
     * @throws ValidationException If the value is not a float or numeric.
     */
    public function validate(mixed $value, string $property) : void
    {
        if (! is_float($value) && ! is_numeric($value)) {
            throw new ValidationException(
                message : $this->message ?? sprintf(self::ERROR_MESSAGE, $property),
                metadata: [
                              'property' => $property,
                              'value'    => $value,
                              'expected' => 'float',
                              'actual'   => gettype($value),
                          ]
            );
        }
    }
}
