<?php

declare(strict_types=1);

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\DataHandling\Validation\Contracts\RuleInterface;
use Avax\Exceptions\ValidationException;

/**
 * Validates that a property is an integer.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
final readonly class IntegerRule
{
    /**
     * @param string|null $message Custom error message
     */
    public function __construct(
        private string|null $message = null
    ) {}

    /**
     * Validates that the provided value is an integer.
     *
     * @param mixed  $value    The value to validate.
     * @param string $property The name of the property being validated.
     *
     * @throws ValidationException
     */
    public function validate(mixed $value, string $property) : void
    {
        if (! is_int(value: $value) && ! ctype_digit(text: (string) $value)) {
            throw new ValidationException(
                message : $this->message ?? "The {$property} field must be an integer.",
                metadata: [
                    'property' => $property,
                    'value'    => $value,
                    'expected' => 'integer'
                ]
            );
        }
    }
}
