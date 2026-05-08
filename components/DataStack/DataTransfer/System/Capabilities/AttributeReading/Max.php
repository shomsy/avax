<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading;

use Attribute;
use InvalidArgumentException;

#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final readonly class Max
{
    public function __construct(public int|float $max) {}

    public function validate(mixed $value, string $field) : void
    {
        if (is_string($value) && strlen($value) > $this->max) {
            throw new InvalidArgumentException(
                sprintf('Field "%s" must not exceed %d characters.', $field, $this->max),
            );
        }

        if (is_numeric($value) && $value > $this->max) {
            throw new InvalidArgumentException(
                sprintf('Field "%s" must not exceed %s.', $field, $this->max),
            );
        }

        if (is_array($value) && count($value) > $this->max) {
            throw new InvalidArgumentException(
                sprintf('Field "%s" must not have more than %d items.', $field, $this->max),
            );
        }
    }
}
