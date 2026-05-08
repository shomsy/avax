<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading;

use Attribute;
use InvalidArgumentException;

#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final readonly class Min
{
    public function __construct(public int|float $min) {}

    public function validate(mixed $value, string $field) : void
    {
        if (is_string($value) && strlen($value) < $this->min) {
            throw new InvalidArgumentException(
                sprintf('Field "%s" must be at least %d characters.', $field, $this->min),
            );
        }

        if (is_numeric($value) && $value < $this->min) {
            throw new InvalidArgumentException(
                sprintf('Field "%s" must be at least %s.', $field, $this->min),
            );
        }

        if (is_array($value) && count($value) < $this->min) {
            throw new InvalidArgumentException(
                sprintf('Field "%s" must have at least %d items.', $field, $this->min),
            );
        }
    }
}
