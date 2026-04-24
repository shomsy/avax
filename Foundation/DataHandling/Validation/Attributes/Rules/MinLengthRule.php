<?php

declare(strict_types=1);

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use InvalidArgumentException;

#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final readonly class MinLengthRule
{
    public function __construct(private int $length) {}

    public function validate(mixed $value, string $property) : void
    {
        if (! is_string(value: $value) || mb_strlen(string: $value) < $this->length) {
            throw new InvalidArgumentException(
                message: sprintf('Field "%s" must be at least %d characters.', $property, $this->length),
            );
        }
    }
}
