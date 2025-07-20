<?php

declare(strict_types=1);

/**
 * The NotRegex class is an attribute used to validate that a property does not match a given regex pattern.
 *
 * This attribute is applied at the property level (TARGET_PROPERTY) and enforces validation rules at runtime.
 * The primary use case is to ensure that certain input properties do not conform to particular patterns,
 * which is critical for enforcing business rules and avoiding invalid data submissions.
 */

namespace Gemini\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Gemini\Exceptions\ValidationException;

/**
 * This attribute class ensures that a value does not match a specific regex pattern.
 *
 * Using the #[Attribute(flags: Attribute::TARGET_PROPERTY)] directive to target properties only,
 * it integrates with validation mechanisms seamlessly.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
readonly class NotRegex
{
    public function __construct(private string $pattern) {}

    /**
     * @throws \Gemini\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property) : void
    {
        if (preg_match($this->pattern, (string) $value)) {
            throw new ValidationException(message: $property . ' format is invalid.');
        }
    }
}
