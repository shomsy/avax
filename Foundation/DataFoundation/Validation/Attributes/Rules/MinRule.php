<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Validation\Attributes\Rules;

use Attribute;
use InvalidArgumentException;

#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final readonly class MinRule
{
    public function __construct(private int|float $minimum) {}

    public function validate(mixed $value, string $property) : void
    {
        if (! is_int(value: $value) && ! is_float(value: $value)) {
            throw new InvalidArgumentException(message: sprintf('Field "%s" must be numeric.', $property));
        }

        if ($value < $this->minimum) {
            throw new InvalidArgumentException(
                message: sprintf('Field "%s" must be at least %s.', $property, (string) $this->minimum),
            );
        }
    }
}
