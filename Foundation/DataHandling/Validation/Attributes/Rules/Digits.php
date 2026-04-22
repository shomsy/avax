<?php

declare(strict_types=1);

/**
 * Attribute class to enforce a property to contain exactly a specified number of digits.
 *
 * - This class can only be used as a property attribute (TARGET_PROPERTY).
 * - The 'readonly' keyword denotes that the property values cannot be changed after instantiation.
 * - Instantiated with a single parameter 'digits' to determine the number of digits required.
 */

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;

/**
 * Readonly class to validate that a property consists of a specific number of digits.
 *
 * @Attribute aims to use this class as a property attribute in another class.
 * This is useful for validating property values against a specific constraint.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
readonly class Digits
{
    private int $digits;

    public function __construct(int $digits) { $this->digits = $digits; }

    /**
     * @throws ValidationException
     */
    public function validate(mixed $value, string $property) : void
    {
        if (sprintf('/^\d{%d}$/', $this->digits)
                |> (static fn ($x) => preg_match(pattern: $x, subject: (string) $value))
                |> (static fn ($x) => in_array(needle: $x, haystack: [0, false], strict: true))) {
            throw new ValidationException(message: sprintf('%s must be %d digits.', $property, $this->digits));
        }
    }
}
