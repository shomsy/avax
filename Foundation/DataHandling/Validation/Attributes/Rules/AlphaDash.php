<?php

declare(strict_types=1);

/**
 * This class represents a validation rule to ensure that a given property
 * contains only letters, numbers, dashes, and underscores.
 *
 * The class is marked as an attribute and is intended to be used on class properties.
 * The Attribute::TARGET_PROPERTY flag restricts its use to properties.
 */

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;

/**
 * Validates that the given value adheres to the AlphaDash rule.
 *
 * Ensures the value only contains letters, numbers, dashes, and underscores.
 *
 * @throws \Avax\Exceptions\ValidationException if the value does not match the allowed pattern.
 *
 * Rationale: This validation is necessary for sanitizing inputs where only alphanumeric characters,
 * dashes, and underscores are allowed. It helps prevent potential security risks
 * and ensures consistency in the values stored or processed.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
class AlphaDash
{
    /**
     * @throws \Avax\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property) : void
    {
        if (in_array(preg_match('/^[\pL\pM\pN_-]+$/u', (string) $value), [0, false], true)) {
            throw new ValidationException(
                message: $property . ' may only contain letters, numbers, dashes, and underscores.',
            );
        }
    }
}
