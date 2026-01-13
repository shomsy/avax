<?php

declare(strict_types=1);

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;

/**
 * Attribute that validates a property's value is one of a predefined set.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
readonly class In
{
    /**
     * @param  array<int|string>  $values  List of accepted values for the field.
     */
    public function __construct(private array $values) {}

    /**
     * Validates whether a given value is in the allowed set.
     *
     * @param  mixed  $value  Value to validate.
     * @param  string  $property  Property name (for exception context).
     *
     * @throws ValidationException If the value is not in the list of allowed values.
     */
    public function validate(mixed $value, string $property): void
    {
        // Unwrap enum to scalar value if needed
        if (is_object(value: $value) && method_exists(object_or_class: $value, method: 'value')) {
            $value = $value->value;
        }

        // Perform strict comparison against an allowed set
        if (! in_array(needle: $value, haystack: $this->values, strict: true)) {
            $allowed = implode(separator: ', ', array: array_map(callback: 'strval', array: $this->values));

            throw new ValidationException(
                message: "{$property} must be one of: {$allowed}"
            );
        }
    }
}
