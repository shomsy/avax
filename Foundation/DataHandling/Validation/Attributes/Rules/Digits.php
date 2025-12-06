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
    public function __construct(private int $digits) {}

    /**
     * @throws \Avax\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property) : void
    {
        if (in_array(preg_match(sprintf('/^\d{%d}$/', $this->digits), (string) $value), [0, false], true)) {
            throw new ValidationException(message: sprintf('%s must be %d digits.', $property, $this->digits));
        }
    }
}
