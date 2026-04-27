<?php

declare(strict_types=1);

namespace components\DataFoundation\Validation\Attributes\Rules;

use Attribute;
use InvalidArgumentException;

#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final readonly class EmailRule
{
    public function validate(mixed $value, string $property) : void
    {
        if (! is_string(value: $value) || filter_var(value: $value, filter: FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException(message: sprintf('Field "%s" must be a valid email address.', $property));
        }
    }
}
