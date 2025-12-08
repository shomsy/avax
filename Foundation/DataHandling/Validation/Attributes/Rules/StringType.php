<?php

declare(strict_types=1);

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;

/**
 * Attribute to enforce that a property value must be a string.
 *
 * This attribute validates that the value of a property is a string. It is
 * primarily used in DTOs to ensure data integrity and type safety.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
class StringType
{
    /**
     * Validation error message template.
     */
    private const string ERROR_MESSAGE = 'The "%s" field must be a string.';

    /**
     * Constructor for the StringType attribute.
     */
    public function __construct(private readonly string|null $message = null) {}

    /**
     * Validates that the provided value is a string.
     *
     * @param mixed  $value    The value to validate.
     * @param string $property The name of the property being validated.
     *
     * @throws ValidationException If the value is not a string.
     */
    public function validate(mixed $value, string $property) : void
    {
        if (! is_string($value)) {
            throw new ValidationException(
                message : $this->message ?? sprintf(self::ERROR_MESSAGE, $property),
                metadata: [
                              'property' => $property,
                              'value'    => $value,
                              'expected' => 'string',
                              'actual'   => gettype($value),
                          ]
            );
        }
    }
}
