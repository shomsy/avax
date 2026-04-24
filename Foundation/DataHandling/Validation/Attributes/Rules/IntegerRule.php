<?php

declare(strict_types=1);

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use InvalidArgumentException;

#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final readonly class IntegerRule
{
    public function validate(mixed $value, string $property) : void
    {
        if (! is_int(value: $value)) {
            throw new InvalidArgumentException(message: sprintf('Field "%s" must be an integer.', $property));
        }
    }
}
