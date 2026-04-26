<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Validation\Attributes\Rules;

use Attribute;
use InvalidArgumentException;

#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final readonly class PasswordComplexityRule
{
    public function validate(mixed $value, string $property) : void
    {
        if (! is_string(value: $value)) {
            throw new InvalidArgumentException(message: sprintf('Field "%s" must be a string.', $property));
        }

        if (preg_match(pattern: '/[A-Z]/', subject: $value) !== 1 || preg_match(pattern: '/[a-z]/', subject: $value) !== 1 || preg_match(pattern: '/\d/', subject: $value) !== 1) {
            throw new InvalidArgumentException(
                message: sprintf('Field "%s" must contain uppercase, lowercase, and numeric characters.', $property),
            );
        }
    }
}
