<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Validation\Attributes;

use Attribute;
use InvalidArgumentException;

#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final readonly class Required
{
    public function validate(mixed $value, string $property) : void
    {
        if ($value === null || $value === '') {
            throw new InvalidArgumentException(message: sprintf('Field "%s" is required.', $property));
        }
    }
}
