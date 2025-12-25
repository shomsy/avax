<?php

declare(strict_types=1);

/**
 * Class URL
 *
 * Defines a custom attribute for validating if a property is a valid URL.
 * Meant to be used on class properties to enforce URL format validation.
 */

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;

/**
 * Attribute class to represent URL validation.
 *
 * Applied at TARGET_PROPERTY level to ensure properties are valid URLs.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
class URL
{
    /**
     * @throws \Avax\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property) : void
    {
        if (! filter_var(value: $value, filter: FILTER_VALIDATE_URL)) {
            throw new ValidationException(message: $property . ' must be a valid URL.');
        }
    }
}
