<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading;

use Attribute;
use InvalidArgumentException;

#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final readonly class AlphaNum
{
    public function validate(mixed $value, string $field) : void
    {
        if (! ctype_alnum((string) $value)) {
            throw new InvalidArgumentException(
                sprintf('Field "%s" must contain only alphanumeric characters.', $field),
            );
        }
    }
}
